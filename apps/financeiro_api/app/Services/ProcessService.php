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

    // Definir critérios de pesquisa (por exemplo, o endereço do remetente)
    $search_criteria = 'UNSEEN FROM "@nubank.com.br" SUBJECT "Extrato da sua conta do Nubank"';
    $mails = imap_search($mailbox, $search_criteria);

    if($mails) {

      // Iterar sobre os emails encontrados
      foreach($mails as $mail_id) {

        // Baixar todas as partes do email
        $structure = imap_fetchstructure($mailbox, $mail_id);

        // Iterar sobre as partes do email
        foreach($structure->parts as $part_number => $part) {

          // Verificar se é um anexo e se é do tipo CSV
          if($part->ifdparameters && $part->dparameters[0]->attribute == 'FILENAME' && pathinfo($part->dparameters[0]->value)['extension'] == 'csv') {

            $emails++;

            // Baixar o anexo
            $attachment = imap_fetchbody($mailbox, $mail_id, $part_number + 1);

            $decodedCsv = base64_decode($attachment);

            // Decodificar o CSV para uma matriz
            $rows = array_map('str_getcsv', explode("\n", $decodedCsv));

            // Remove o cabeçalho (assumindo que a primeira linha do CSV é o cabeçalho)
            array_shift($rows);

            $listados = count($rows);

            foreach($rows as $row) {

              if(count($row) < 4)
                continue;

              $extrato = Extrato::where("Identificador", '=', $row[2])->first();
              if($extrato)
                continue;

              $extrato = new Extrato();
              $extrato->Data = functions::formatDate($row[0]);
              $extrato->Valor = $row[1];
              $extrato->Identificador = $row[2];
              $extrato->Descricao = $row[3];
              $extrato->IdTipoExtrato = 1; // debito
              $extrato->IdBancoExtrato = $idBancoExtrato; // nubank
              $extrato->save();

              $incluidos++;

            }

            // Marcar o email como lido para evitar que seja baixado novamente
            imap_setflag_full($mailbox, $mail_id, "\\Seen");

          }// if csv

        }// foreach attachments

      } // foreach emails

//      if($incluidos > 0)
      $this->Fechamento();

    } // if emails

    return [
      'emails_lidos' => $emails,
      'itens_listados' => $listados,
      'itens_incluidos' => $incluidos
    ];

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
          -- Fechamentos anteriores
          select
              coalesce(sum(Fechamento.ValorFechado), 0)
          from Fechamento
          inner join BancoExtrato on BancoExtrato.Id = Fechamento.IdBancoExtrato
          where
              BancoExtrato.IdUsuario = ?
              and Fechamento.DataBase = ?
        ) as SaldoFechamento", [$idUsuario, $data_base])
        ->where('Extrato.IdBancoExtrato', '=', $idBancoExtrato)
        ->whereBetween('Extrato.Data', [$data_inicio_mes . ' 00:00:00', $data_fim_mes . ' 23:59:59'])
        ->first();

      if(!$fechamento_atual)
        throw new \Exception('Fechamento não canculado');


      $fechamento = Fechamento::select()->where('DataBase', '=', $data_inicio_mes)->first();

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
      ->first();

    $totalCategoria = 0;

    if(isset($fechamento_categoria->TotalCategoria))
      $totalCategoria = (float)$fechamento_categoria->TotalCategoria;

//    if($totalCategoria < 0)
//      $totalCategoria = $totalCategoria *-1;

    $fechamentoCategoria = FechamentoCategoria::select()
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
    if (!$request->mes || $request->mes === '')
      throw new \Exception('mes não passado');

    if (!$request->ano || $request->ano === '')
      throw new \Exception('ano não passado');

    $mes = (int) $request->mes;
    $ano = (int) $request->ano;

    $idBancoExtrato = $this->idBancoExtrato;

    // 1. Categorias
    $categorias = Categoria::select('Id', 'CategoriaNome')
      ->orderBy('CategoriaNome')
      ->get();

    $idsCategoria = $categorias->pluck('Id')->map(function ($id) { return (int) $id; })->all();

    // 2. Histórico: últimos 3 meses, já categorizados (Descricao, CategoriaNome), limit 300
    $datasBase = Functions::ultimasDatadaBase(3);
    $data_inicio_hist = $datasBase[count($datasBase) - 1]['data_inicio_mes'] . ' 00:00:00';
    $data_fim_hist = $datasBase[0]['data_fim_mes'] . ' 23:59:59';

    $historico = Extrato::selectRaw('Extrato.Descricao, Categoria.CategoriaNome')
      ->join('Categoria', 'Categoria.Id', '=', 'Extrato.IdCategoria')
      ->where('Extrato.IdBancoExtrato', '=', $idBancoExtrato)
      ->whereBetween('Extrato.Data', [$data_inicio_hist, $data_fim_hist])
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
      ->get();

    if ($itensACategorizar->isEmpty()) {
      return ['atualizados' => 0];
    }

    $idsEnviados = $itensACategorizar->pluck('Id')->map(function ($id) { return (int) $id; })->all();

    // 4. Montar prompt
    $linhasCategorias = $categorias->map(function ($c) {
      return $c->Id . ' - ' . $c->CategoriaNome;
    })->implode("\n");

    $linhasHistorico = $historico->map(function ($r) {
      return 'Descricao: ' . $r->Descricao . ' -> Categoria: ' . $r->CategoriaNome;
    })->implode("\n");

    $linhasItens = $itensACategorizar->map(function ($r) {
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
    } catch (\Exception $e) {
      $msg = $e->getMessage();
      if ($msg === '') {
        $msg = 'erro desconhecido (verifique GEMINI_API_KEY e logs)';
      }
      throw new \Exception('Falha ao categorizar com IA: ' . $msg);
    }

    // 6. Parsear e validar resposta (extrair JSON mesmo com markdown ou texto em volta)
    $sugestoes = $this->parseGeminiCategorizacaoResponse($jsonText);
    if ($sugestoes === null) {
      $preview = strlen($jsonText) > 200 ? substr($jsonText, 0, 200) . '...' : $jsonText;
      throw new \Exception('Resposta da IA inválida. Tente novamente. Resposta recebida: ' . $preview);
    }

    $idsCategoriaSet = array_flip($idsCategoria);
    $atualizados = 0;

    foreach ($sugestoes as $item) {
      $id = isset($item['Id']) ? (int) $item['Id'] : (isset($item['id']) ? (int) $item['id'] : null);
      $idCategoria = isset($item['IdCategoria']) ? (int) $item['IdCategoria'] : (isset($item['idCategoria']) ? (int) $item['idCategoria'] : null);
      if ($id === null || $idCategoria === null) continue;
      if (!in_array($id, $idsEnviados, true)) continue;
      if (!isset($idsCategoriaSet[$idCategoria])) continue;

      $extrato = Extrato::where('Id', '=', $id)
        ->where('IdBancoExtrato', '=', $idBancoExtrato)
        ->first();
      if (!$extrato) continue;

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
   * Retorna array de sugestões ou null se inválido.
   */
  private function parseGeminiCategorizacaoResponse(string $jsonText): ?array
  {
    $jsonText = trim($jsonText);
    // Remove blocos markdown ```json ... ``` ou ``` ... ```
    $jsonText = preg_replace('/^```(?:json)?\s*\n?/i', '', $jsonText);
    $jsonText = preg_replace('/\n?```\s*$/i', '', $jsonText);
    $jsonText = trim($jsonText);

    $start = strpos($jsonText, '[');
    if ($start === false) {
      return null;
    }

    $end = strrpos($jsonText, ']');
    if ($end !== false && $end > $start) {
      $jsonText = substr($jsonText, $start, $end - $start + 1);
    } else {
      // Resposta truncada (sem ] no final): usa do [ até o fim e tenta fechar o array
      $jsonText = substr($jsonText, $start);
      $jsonText = rtrim($jsonText);
      if (substr($jsonText, -1) === ',') {
        $jsonText = substr($jsonText, 0, -1);
      }
      $jsonText .= ']';
    }

    $sugestoes = json_decode($jsonText, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($sugestoes)) {
      return null;
    }
    return $sugestoes;
  }

}
