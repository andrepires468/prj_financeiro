<?php

namespace App\Services;


use App\Helpers\Functions;
use App\Models\BancoExtrato;
use App\Models\Categoria;
use App\Models\Extrato;
use App\Models\Fechamento;
use App\Models\FechamentoCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;


class ProcessService extends Service
{

  protected $geminiService;

  public function __construct(GeminiService $geminiService)
  {
    parent::__construct();
    $this->geminiService = $geminiService;
  }

  public function ImportExtrato(Request $request)
  {

    $BancoExtrato = BancoExtrato::select()
      ->where('Id', '=', $this->idBancoExtrato)
      ->first();

    if(!$BancoExtrato)
      throw new \Exception('configuração de banco não encontrada');

    if(!$BancoExtrato->GmailUser)
      throw new \Exception('GMAIL user não configurado');

    if(!$BancoExtrato->GmailPass)
      throw new \Exception('GMAIL IMAP pass não configurado');

    $idBancoExtrato = $BancoExtrato->Id;

//    $user_ = Crypt::encrypt($BancoExtrato->GmailUser);
//    $pass_ = Crypt::encrypt($BancoExtrato->GmailPass);

    try {
      $user = Crypt::decrypt($BancoExtrato->GmailUser);
    } catch(\Exception $e) {
      $user = $BancoExtrato->GmailUser;
    }

    try {
      $pass = Crypt::decrypt($BancoExtrato->GmailPass);
    } catch(\Exception $e) {
      $pass = $BancoExtrato->GmailPass;
    }

    // Remove espaços da App Password do Google (formato: "xxxx xxxx xxxx xxxx" -> "xxxxxxxxxxxxxxxx")
    $pass = str_replace(' ', '', $pass);

    $listados = 0;
    $incluidos = 0;
    $emails = 0;

    // Conectar ao servidor IMAP do Gmail
    imap_timeout(IMAP_OPENTIMEOUT, 15);
    imap_timeout(IMAP_READTIMEOUT, 15);
    imap_timeout(IMAP_WRITETIMEOUT, 15);

//    \Log::debug('IMAP auth', [
//      'user'      => $user,
//      'user_len'  => strlen($user),
//      'pass_len'  => strlen($pass),
//      'pass_last4'=> substr($pass, -4),
//    ]);

    $mailbox = @imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', $user, $pass, 0, 1);

    if(!$mailbox) {
      $errors = imap_errors();
      $errorMsg = $errors ? implode(' | ', $errors) : 'Falha ao conectar ao servidor IMAP';
      throw new \Exception('Erro IMAP: ' . $errorMsg);
    }

    try {
      $resultadoDebito = $this->importarDebito($mailbox, $idBancoExtrato);
      $emails += $resultadoDebito['emails'];
      $listados += $resultadoDebito['listados'];
      $incluidos += $resultadoDebito['incluidos'];

      $resultadoCredito = $this->importarCredito($mailbox, $idBancoExtrato);
      $emails += $resultadoCredito['emails'];
      $listados += $resultadoCredito['listados'];
      $incluidos += $resultadoCredito['incluidos'];

      $resultadoEmpresas = $this->importarExtratoEmpresas($mailbox, $idBancoExtrato);
      $emails += $resultadoEmpresas['emails'];
      $listados += $resultadoEmpresas['listados'];
      $incluidos += $resultadoEmpresas['incluidos'];

      if($incluidos > 0)
        $this->Fechamento();
    } finally {
      imap_close($mailbox);
    }

    return [
      'emails_lidos' => $emails,
      'itens_listados' => $listados,
      'itens_incluidos' => $incluidos
    ];

  }

  /**
   * Importa lançamentos de débito a partir de emails (ex.: extrato Nubank conta).
   * Retorna contadores para emails processados, linhas listadas e itens incluídos.
   */
  private function importarDebito($mailbox, $idBancoExtrato)
  {
    $emails = 0;
    $listados = 0;
    $incluidos = 0;

    $search_criteria = 'UNSEEN FROM "@nubank.com.br" SUBJECT "Extrato da sua conta do Nubank"';
    $mails = imap_search($mailbox, $search_criteria);

    if(!$mails)
      return ['emails' => 0, 'listados' => 0, 'incluidos' => 0];

    foreach($mails as $mail_id) {

      $structure = imap_fetchstructure($mailbox, $mail_id);

      foreach($structure->parts as $part_number => $part) {

        if($part->ifdparameters && $part->dparameters[0]->attribute == 'FILENAME' && pathinfo($part->dparameters[0]->value)['extension'] == 'csv') {

          $emails++;

          $attachment = imap_fetchbody($mailbox, $mail_id, $part_number + 1);
          $decodedCsv = base64_decode($attachment);
          $rows = array_map('str_getcsv', explode("\n", $decodedCsv));
          array_shift($rows);

          $listados = count($rows);

          foreach($rows as $row) {

            if(count($row) < 4)
              continue;

            $extrato = Extrato::where("Identificador", '=', $row[2])->first();
            if($extrato) {
              if($extrato->Excluido) {
                $extrato->Data = Functions::formatDate($row[0]);
                $extrato->Valor = $row[1];
                $extrato->Descricao = $row[3];
                $extrato->Excluido = false;
                $extrato->RestauradoEm = $this->date->format('Y-m-d H:i:s');
                $extrato->Origem = 'integracao_proc';
                $extrato->save();
                $incluidos++;
              }
              continue;
            }

            $extrato = new Extrato();
            $extrato->Data = Functions::formatDate($row[0]);
            $extrato->Valor = $row[1];
            $extrato->Identificador = $row[2];
            $extrato->Descricao = $row[3];
            $extrato->IdTipoExtrato = 1; // débito
            $extrato->IdBancoExtrato = $idBancoExtrato;
            $extrato->CriadoEm = $this->date->format('Y-m-d H:i:s');
            $extrato->Excluido = false;
            $extrato->Origem = 'integracao_proc';
            $extrato->save();

            $incluidos++;
          }

          imap_setflag_full($mailbox, $mail_id, "\\Seen");
        }
      }
    }

    return ['emails' => $emails, 'listados' => $listados, 'incluidos' => $incluidos];
  }

  /**
   * Importa lançamentos de crédito (fatura do cartão) a partir de emails (ex.: extrato fatura Nubank).
   * CSV: date, title, amount. Retorna contadores para emails processados, linhas listadas e itens incluídos.
   */
  private function importarCredito($mailbox, $idBancoExtrato)
  {
    $emails = 0;
    $listados = 0;
    $incluidos = 0;

    $search_criteria = 'UNSEEN FROM "@nubank.com.br" SUBJECT "Extrato da fatura"';
    $mails = imap_search($mailbox, $search_criteria);

    if(!$mails)
      return ['emails' => 0, 'listados' => 0, 'incluidos' => 0];

    foreach($mails as $mail_id) {

      $structure = imap_fetchstructure($mailbox, $mail_id);

      foreach($structure->parts as $part_number => $part) {

        if($part->ifdparameters && $part->dparameters[0]->attribute == 'FILENAME' && pathinfo($part->dparameters[0]->value)['extension'] == 'csv') {

          $emails++;

          $attachment = imap_fetchbody($mailbox, $mail_id, $part_number + 1);
          $decodedCsv = base64_decode($attachment);
          $decodedCsv = preg_replace('/^\xEF\xBB\xBF/u', '', $decodedCsv);
          $rows = array_map('str_getcsv', preg_split('/\R/u', $decodedCsv));
          array_shift($rows);
          $rows = array_values(array_filter($rows, function($row) {
            if(!is_array($row))
              return false;

            foreach($row as $coluna) {
              if(trim((string)$coluna) !== '')
                return true;
            }

            return false;
          }));

          $listados += count($rows);

          foreach($rows as $row) {

            if(count($row) < 3)
              continue;

            $titulo = trim((string)$row[1]);
            if($titulo === '' || strtolower($titulo) === 'pagamento recebido')
              continue;

            $dataExtrato = $this->parseDataCreditoCsv($row[0]);
            if(!$dataExtrato)
              continue;

            $valorExtrato = $this->valorFaturaCsv($row[2]);
            if($valorExtrato === null)
              continue;

            // Gerar identificador único para evitar duplicados: hash dos campos relevantes (ex.: date, title, amount)
            $identificador = 'credito-' . md5($row[0] . '|' . $row[1] . '|' . $row[2]);
            $extrato = Extrato::where('Identificador', '=', $identificador)->first();
            if($extrato) {
              if($extrato->Excluido) {
                $extrato->Data = $dataExtrato;
                $extrato->Valor = $valorExtrato;
                $extrato->Descricao = mb_substr($titulo, 0, 420);
                $extrato->Excluido = false;
                $extrato->RestauradoEm = $this->date->format('Y-m-d H:i:s');
                $extrato->Origem = 'integracao_proc';
                $extrato->save();
                $incluidos++;
              }
              continue;
            }

            $extrato = new Extrato();
            $extrato->Data = $dataExtrato;
            $extrato->Valor = $valorExtrato;
            $extrato->Identificador = $identificador;
            $extrato->Descricao = mb_substr($titulo, 0, 420); // title
            $extrato->IdTipoExtrato = 2; // crédito
            $extrato->IdBancoExtrato = $idBancoExtrato;
            $extrato->Excluido = false;
            $extrato->Origem = 'integracao_proc';
            $extrato->save();

            $incluidos++;
          }

          imap_setflag_full($mailbox, $mail_id, "\\Seen");
        }
      }
    }

    return ['emails' => $emails, 'listados' => $listados, 'incluidos' => $incluidos];
  }

  /**
   * Importa lançamentos de débito a partir de emails do Nu Empresas (extrato da conta Nu Empresas).
   * CSV: date, amount, identifier(UUID), title (mesmo layout do importarDebito). IdTipoExtrato = 1 (débito).
   * Retorna contadores para emails processados, linhas listadas e itens incluídos.
   */
  private function importarExtratoEmpresas($mailbox, $idBancoExtrato)
  {
    $emails = 0;
    $listados = 0;
    $incluidos = 0;

    $search_criteria = 'UNSEEN FROM "@nubank.com.br" SUBJECT "conta Nu Empresas"';
    $mails = imap_search($mailbox, $search_criteria);

    if(!$mails)
      return ['emails' => 0, 'listados' => 0, 'incluidos' => 0];

    foreach($mails as $mail_id) {

      $structure = imap_fetchstructure($mailbox, $mail_id);

      foreach($structure->parts as $part_number => $part) {

        if($part->ifdparameters && $part->dparameters[0]->attribute == 'FILENAME' && pathinfo($part->dparameters[0]->value)['extension'] == 'csv') {

          $emails++;

          $attachment = imap_fetchbody($mailbox, $mail_id, $part_number + 1);
          $decodedCsv = base64_decode($attachment);
          $rows = array_map('str_getcsv', explode("\n", $decodedCsv));
          array_shift($rows);

          $listados = count($rows);

          foreach($rows as $row) {

            if(count($row) < 4)
              continue;

            // Prefixo 'empresas-' para não colidir com outros bancos que também usam UUID
            $identificador = 'empresas-' . $row[2];
            $extrato = Extrato::where('Identificador', '=', $identificador)->first();
            if($extrato)
              continue;

            $extrato = new Extrato();
            $extrato->Data = Functions::formatDate($row[0]);
            $extrato->Valor = $row[1];
            $extrato->Identificador = $identificador;
            $extrato->Descricao = mb_substr($row[3], 0, 420);
            $extrato->IdTipoExtrato = 1; // débito
            $extrato->IdBancoExtrato = $idBancoExtrato;
            $extrato->CriadoEm = $this->date->format('Y-m-d H:i:s');
            $extrato->save();

            $incluidos++;
          }

          imap_setflag_full($mailbox, $mail_id, "\\Seen");
        }
      }
    }

    return ['emails' => $emails, 'listados' => $listados, 'incluidos' => $incluidos];
  }

  /**
   * Converte data do CSV de fatura (Y-m-d ou d/m/Y) para Y-m-d.
   */
  private function parseDataCreditoCsv($rawDate): ?string
  {
    $dataCredito = trim((string)$rawDate);
    $dataCredito = preg_replace('/^\xEF\xBB\xBF/u', '', $dataCredito);

    if($dataCredito === '')
      return null;

    if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataCredito))
      return $dataCredito;

    $dt = \DateTime::createFromFormat('d/m/Y', $dataCredito);
    if($dt instanceof \DateTime)
      return $dt->format('Y-m-d');

    $timestamp = strtotime($dataCredito);
    if($timestamp === false)
      return null;

    return date('Y-m-d', $timestamp);
  }

  /**
   * Normaliza valor da fatura: despesas ficam negativas no extrato.
   */
  private function valorFaturaCsv($rawAmount): ?float
  {
    $valor = Functions::parseValorMonetario($rawAmount);
    if($valor === null)
      return null;

    return $valor > 0 ? $valor * -1 : $valor;
  }

  public function Fechamento()
  {

    $dataHorAtual = $this->date->format('Y-m-d H:i:s');
    $idUsuario = $this->idUsuario;
    $idBancoExtrato = $this->idBancoExtrato;

    // Definindo o mês e ano de início
    $mes_inicio = env('MES_INICIO');
    $ano_inicio = env('ANO_INICIO');

    $mes_atual = $this->date->format("m");
    $ano_atual = $this->date->format("Y");

    $data_atual = date('Y-m-01', strtotime("first day of {$ano_inicio}-{$mes_inicio}"));
    $data_final = date('Y-m-01', strtotime("first day of {$ano_atual}-{$mes_atual}"));

    while($data_atual < $data_final) {

      $data_base = date('Y-m-01', strtotime('-1 month', strtotime($data_atual)));
      $data_inicio_mes = date('Y-m-01', strtotime("first day of", strtotime($data_atual)));
      $data_fim_mes = date('Y-m-t', strtotime("last day of", strtotime($data_atual)));

      $fechamento_atual = Extrato::selectRaw("
        coalesce(sum(Extrato.Valor), 0) + (
          -- Fechamento anterior do MESMO banco
          select
              coalesce(sum(Fechamento.ValorFechado), 0)
          from Fechamento
          where
              Fechamento.IdBancoExtrato = ?
              and Fechamento.DataBase = ?
        ) as SaldoFechamento", [$idBancoExtrato, $data_base])
        ->where('Extrato.IdBancoExtrato', '=', $idBancoExtrato)
        ->whereBetween('Extrato.Data', [$data_inicio_mes . ' 00:00:00', $data_fim_mes . ' 23:59:59'])
        ->ativos()
        ->first();

      if(!$fechamento_atual)
        throw new \Exception('Fechamento não canculado');


      $fechamento = Fechamento::select()
        ->where('IdBancoExtrato', '=', $idBancoExtrato)
        ->where('DataBase', '=', $data_inicio_mes)
        ->first();

      if(!$fechamento) {
        $fechamento = new Fechamento();
        $fechamento->IdBancoExtrato = $idBancoExtrato;
        $fechamento->DataBase = $data_inicio_mes;
      }

      $fechamento->ValorFechado = $fechamento_atual->SaldoFechamento;
      $fechamento->AlteradoEm = $dataHorAtual;
      $fechamento->save();

      $data_atual = date('Y-m-01', strtotime('+1 month', strtotime($data_atual)));

    } // while

  }

  public function FechamentoCategoria(Request $request)
  {

    if(!$request->idCategoria)
      throw new \Exception('idCategoria não passado');

    if(!$request->dataBase)
      throw new \Exception('dataBase não passado');

    $this->procFechamentoCategoria($request->idCategoria, $request->dataBase);

  }


  public function FechamentoTodasCategorias(Request $request)
  {

    if(!$request->dataBase)
      throw new \Exception('dataBase não passado');

    $dataBase = $request->dataBase;

    $categorias = Categoria::select()->get();

    foreach($categorias as $categoria) {
      $this->procFechamentoCategoria($categoria->Id, $dataBase);
    }


  }


  public function procFechamentoCategoria($idCategoria, $dataBase)
  {

    $idUsuario = $this->idUsuario;
    $idBancoExtrato = $this->idBancoExtrato;
    $dataHorAtual = $this->date->format('Y-m-d H:i:s');

    $date = new \DateTime($dataBase);
    $data_inicio_mes = $date->modify('first day of this month')->format('Y-m-d');

    $date = new \DateTime($dataBase); // Reinicializando a data
    $data_fim_mes = $date->modify('last day of this month')->format('Y-m-d');

    $fechamento_categoria = Extrato::selectRaw("coalesce(sum(Extrato.Valor), 0) TotalCategoria")
      ->where('Extrato.IdBancoExtrato', '=', $idBancoExtrato)
      ->where('Extrato.IdCategoria', '=', $idCategoria)
      ->whereBetween('Extrato.Data', [$data_inicio_mes . ' 00:00:00', $data_fim_mes . ' 23:59:59'])
      ->ativos()
      ->first();

    $totalCategoria = 0;

    if(isset($fechamento_categoria->TotalCategoria))
      $totalCategoria = (float)$fechamento_categoria->TotalCategoria;

//    if($totalCategoria < 0)
//      $totalCategoria = $totalCategoria *-1;

    $fechamentoCategoria = FechamentoCategoria::select()
      ->where('IdBancoExtrato', '=', $idBancoExtrato)
      ->where('IdCategoria', '=', $idCategoria)
      ->where('DataBase', '=', $dataBase)
      ->first();

    if(!$fechamentoCategoria) {
      $fechamentoCategoria = new FechamentoCategoria();
      $fechamentoCategoria->IdBancoExtrato = $idBancoExtrato;
      $fechamentoCategoria->IdCategoria = $idCategoria;
      $fechamentoCategoria->DataBase = $dataBase;
    }

    $fechamentoCategoria->ValorFechado = $totalCategoria;
    $fechamentoCategoria->AlteradoEm = $dataHorAtual;
    $fechamentoCategoria->save();

  }


  /**
   * Categoriza itens do extrato do mês selecionado usando Gemini API.
   * Usa histórico dos últimos 3 meses (apenas Descricao + Categoria) e atualiza somente IdCategoria.
   */
  public function CategorizarExtrato(Request $request)
  {
    if(!$request->mes || $request->mes === '')
      throw new \Exception('mes não passado');

    if(!$request->ano || $request->ano === '')
      throw new \Exception('ano não passado');

    $mes = (int)$request->mes;
    $ano = (int)$request->ano;

    $idBancoExtrato = $this->idBancoExtrato;

    // 1. Categorias
    $categorias = Categoria::select('Id', 'CategoriaNome')
      ->orderBy('CategoriaNome')
      ->get();

    $idsCategoria = $categorias->pluck('Id')->map(function($id) {
      return (int)$id;
    })->all();

    // 2. Histórico: últimos 3 meses, já categorizados (Descricao, CategoriaNome), limit 300
    $datasBase = Functions::ultimasDatadaBase(3);
    $data_inicio_hist = $datasBase[count($datasBase) - 1]['data_inicio_mes'] . ' 00:00:00';
    $data_fim_hist = $datasBase[0]['data_fim_mes'] . ' 23:59:59';

    $historico = Extrato::selectRaw('Extrato.Descricao, Categoria.CategoriaNome')
      ->join('Categoria', 'Categoria.Id', '=', 'Extrato.IdCategoria')
      ->where('Extrato.IdBancoExtrato', '=', $idBancoExtrato)
      ->whereBetween('Extrato.Data', [$data_inicio_hist, $data_fim_hist])
      ->ativos()
      ->orderByDesc('Extrato.Data')
      ->limit(300)
      ->get();

    // 3. Itens a categorizar: mês selecionado, sem categoria (Id, Descricao)
    $data_inicio_mes = date('Y-m-01', strtotime("first day of {$ano}-{$mes}"));
    $data_fim_mes = date('Y-m-t', strtotime("last day of {$ano}-{$mes}"));

    $itensACategorizar = Extrato::select('Extrato.Id', 'Extrato.Descricao')
      ->where('Extrato.IdBancoExtrato', '=', $idBancoExtrato)
      ->whereNull('Extrato.IdCategoria')
      ->whereBetween('Extrato.Data', [$data_inicio_mes . ' 00:00:00', $data_fim_mes . ' 23:59:59'])
      ->ativos()
      ->get();

    if($itensACategorizar->isEmpty()) {
      return ['atualizados' => 0];
    }

    $idsEnviados = $itensACategorizar->pluck('Id')->map(function($id) {
      return (int)$id;
    })->all();

    // 4. Montar prompt
    $linhasCategorias = $categorias->map(function($c) {
      return $c->Id . ' - ' . $c->CategoriaNome;
    })->implode("\n");

    $linhasHistorico = $historico->map(function($r) {
      return 'Descricao: ' . $r->Descricao . ' -> Categoria: ' . $r->CategoriaNome;
    })->implode("\n");

    $linhasItens = $itensACategorizar->map(function($r) {
      return 'Id: ' . $r->Id . ', Descricao: ' . $r->Descricao;
    })->implode("\n");

    $prompt = <<<PROMPT
Você classifica transações bancárias em categorias. Use apenas as categorias fornecidas abaixo.

Categorias disponíveis (Id - Nome):
{$linhasCategorias}

Exemplos de transações já categorizadas (Descricao -> Categoria):
{$linhasHistorico}

Itens a categorizar (para cada um responda com o Id e o IdCategoria da categoria correta):
{$linhasItens}

Responda SOMENTE com um JSON: array de objetos, cada um com as chaves "Id" (número) e "IdCategoria" (número). Nada mais. Exemplo: [{"Id":123,"IdCategoria":1},{"Id":124,"IdCategoria":2}]
PROMPT;

    // 5. Chamar Gemini
    try {
      $jsonText = $this->geminiService->generateContent($prompt);
    } catch(\Exception $e) {
      $msg = $e->getMessage();
      if($msg === '') {
        $msg = 'erro desconhecido (verifique GEMINI_API_KEY e logs)';
      }
      throw new \Exception('Falha ao categorizar com IA: ' . $msg);
    }

    // 6. Parsear e validar resposta (extrair JSON mesmo com markdown ou texto em volta)
    $parseResult = $this->parseGeminiCategorizacaoResponse($jsonText);
    $sugestoes = $parseResult['data'] ?? null;
    $parseError = $parseResult['error'] ?? null;
    if($sugestoes === null) {
      $preview = strlen($jsonText) > 200 ? substr($jsonText, 0, 200) . '...' : $jsonText;
      $msg = 'Resposta da IA inválida. Tente novamente. Resposta recebida: ' . $preview;
      if($parseError !== null && $parseError !== '') {
        $msg .= ' (erro JSON: ' . $parseError . ')';
      }
      throw new \Exception($msg);
    }

    $idsCategoriaSet = array_flip($idsCategoria);
    $atualizados = 0;

    foreach($sugestoes as $item) {
      $id = isset($item['Id']) ? (int)$item['Id'] : (isset($item['id']) ? (int)$item['id'] : null);
      $idCategoria = isset($item['IdCategoria']) ? (int)$item['IdCategoria'] : (isset($item['idCategoria']) ? (int)$item['idCategoria'] : null);
      if($id === null || $idCategoria === null) continue;
      if(!in_array($id, $idsEnviados, true)) continue;
      if(!isset($idsCategoriaSet[$idCategoria])) continue;

      $extrato = Extrato::where('Id', '=', $id)
        ->where('IdBancoExtrato', '=', $idBancoExtrato)
        ->ativos()
        ->first();
      if(!$extrato) continue;

      $extrato->IdCategoria = $idCategoria;
      $extrato->save();

      $dataExtrato = new \DateTime($extrato->Data);
      $dataBase = $dataExtrato->modify('first day of this month')->format('Y-m-d');
      $this->procFechamentoCategoria($idCategoria, $dataBase);

      $atualizados++;
    }

    return ['atualizados' => $atualizados];
  }

  /**
   * Extrai e decodifica o array JSON da resposta da Gemini (tolera markdown, texto em volta e JSON truncado).
   * Retorna ['data' => array de sugestões, 'error' => null] ou ['data' => null, 'error' => mensagem].
   */
  private function parseGeminiCategorizacaoResponse(string $jsonText): array
  {
    $jsonText = trim($jsonText);
    // Remove blocos markdown ```json ... ``` ou ``` ... ```
    $jsonText = preg_replace('/^```(?:json)?\s*\n?/i', '', $jsonText);
    $jsonText = preg_replace('/\n?```\s*$/i', '', $jsonText);
    $jsonText = trim($jsonText);

    $start = strpos($jsonText, '[');
    if($start === false) {
      return ['data' => null, 'error' => 'nenhum array encontrado na resposta'];
    }

    // Extrai apenas o primeiro array completo (contando colchetes), evita [ {...} ],[ {...} ] inválido
    $slice = $this->extractFirstJsonArray($jsonText, $start);
    if($slice === null) {
      // Resposta truncada: do [ até o fim, tenta fechar e corrigir último objeto incompleto
      $slice = substr($jsonText, $start);
      $slice = rtrim($slice);
      if(substr($slice, -1) === ',') {
        $slice = substr($slice, 0, -1);
      }
      // Remove último objeto incompleto (ex: ,{"Id":182,"IdCategoria":)
      $slice = $this->trimTrailingIncompleteObject($slice);
      $slice .= ']';
    }

    $sugestoes = json_decode($slice, true);
    $err = json_last_error();
    if($err !== JSON_ERROR_NONE) {
      return ['data' => null, 'error' => json_last_error_msg()];
    }
    if(!is_array($sugestoes)) {
      return ['data' => null, 'error' => 'resposta não é um array'];
    }
    return ['data' => $sugestoes, 'error' => null];
  }

  /**
   * Encontra o trecho do primeiro array JSON completo a partir de $start ([ já encontrado).
   * Retorna a substring incluindo [ e ] ou null se não houver ] de fechamento.
   */
  private function extractFirstJsonArray(string $jsonText, int $start): ?string
  {
    $len = strlen($jsonText);
    $depth = 1;
    $i = $start + 1;
    $inString = false;
    $escape = false;
    $quote = '';

    while($i < $len) {
      $c = $jsonText[$i];
      if($escape) {
        $escape = false;
        $i++;
        continue;
      }
      if($c === '\\' && $inString) {
        $escape = true;
        $i++;
        continue;
      }
      if(($c === '"' || $c === "'") && !$inString) {
        $inString = true;
        $quote = $c;
        $i++;
        continue;
      }
      if($c === $quote && $inString) {
        $inString = false;
        $i++;
        continue;
      }
      if($inString) {
        $i++;
        continue;
      }
      if($c === '[') {
        $depth++;
      } elseif($c === ']') {
        $depth--;
        if($depth === 0) {
          return substr($jsonText, $start, $i - $start + 1);
        }
      }
      $i++;
    }
    return null;
  }

  /**
   * Remove objeto incompleto no final (ex: ,{"Id":123,"IdCategoria": sem fechar }).
   */
  private function trimTrailingIncompleteObject(string $json): string
  {
    $json = rtrim($json);
    if(substr($json, -1) === ',') {
      $json = substr($json, 0, -1);
    }
    // Se termina com }, o último objeto está completo
    if(substr($json, -2) === '},') {
      return substr($json, 0, -1);
    }
    if(substr($json, -1) === '}') {
      return $json;
    }
    // Último objeto incompleto: cortar no último "}," e manter só o }
    $lastComplete = strrpos($json, '},');
    if($lastComplete !== false) {
      return substr($json, 0, $lastComplete + 1);
    }
    return $json;
  }

}
