<?php

namespace App\Services;


use App\Helpers\Functions;
use App\Models\BancoExtrato;
use App\Models\Extrato;
use App\Models\Fechamento;
use Illuminate\Http\Request;


class ProcessService extends Service
{

  public function __construct()
  {
    parent::__construct();
  }

  public function ImportEstrato(Request $request)
  {

    $BancoExtrato = BancoExtrato::select()
      ->where('IdUsuario', '=', $this->idUsuario)
      ->where('BancoNome', '=', 'Nubank')
      ->first();

    if(!$BancoExtrato)
      throw new \Exception('configuração de banco não encontrada');

    if(!$BancoExtrato->GmailUser)
      throw new \Exception('GMAIL user não configurado');

    if(!$BancoExtrato->GmailPass)
      throw new \Exception('GMAIL IMAP pass não configurado');

    $idBancoExtrato = $BancoExtrato->Id;

    $user = utf8_encode(env($BancoExtrato->GmailUser));
    $pass = utf8_encode(str_replace('_', ' ', env($BancoExtrato->GmailPass)));

    $listados = 0;
    $incluidos = 0;
    $emails = 0;

    // Conectar ao servidor IMAP do Gmail
    $mailbox = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', $user, $pass);

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

      if($incluidos)
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
          (coalesce(sum(Extrato.Valor), 0)  +
          (
            -- fechamentos anteriores
            select
            coalesce(sum(Fechamento.ValorFechado), 0)
            from Fechamento
            where Fechamento.DataBase = ?
          )) SaldoFechamento", [$data_base])
        ->whereBetween('Extrato.Data', [$data_inicio_mes . ' 00:00:00', $data_fim_mes . ' 23:59:59'])
        ->first();

      if(!$fechamento_atual)
        throw new \Exception('Fechamento não canculado');


      $fechamento = Fechamento::select()->where('DataBase', '=', $data_inicio_mes)->first();

      if(!$fechamento) {
        $fechamento = new Fechamento();
        $fechamento->DataBase = $data_inicio_mes;
      }

      $fechamento->ValorFechado = $fechamento_atual->SaldoFechamento;
      $fechamento->AlteradoEm = $dataHorAtual;
      $fechamento->save();

      $data_atual = date('Y-m-01', strtotime('+1 month', strtotime($data_atual)));

    } // while

  }


//  private function categorizarExtratoPassado($Extrato)
//  {
//
//    foreach($Extrato as $extrato) {
//
//    }
//
//  }
//
//  private function tokenizarExtrato($Extrato)
//  {
//
//    foreach($Extrato as $extrato) {
//      $descricao = Functions::limparTexto($extrato->Descricao);
//      $descricao_tokens = json_encode(Functions::tokenizarTexto($descricao));
//      $extrato->DescricaoTokens = $descricao_tokens;
//      $extrato->save();
//    }
//
//  }
//
//  public function CategorizarExtrato(Request $request)
//  {
//
//    if(!$request->mes)
//      throw new \Exception('mes não passado');
//
//    if(!$request->ano)
//      throw new \Exception('ano não passado');
//
//    $mes = $request->mes;
//    $ano = $request->ano;
//
//    $Extrato = Extrato::select()
//      ->whereNull('Extrato.DescricaoTokens')
//      ->get();
//
//    $this->tokenizarExtrato($Extrato);
//
//    // Obtém a data do primeiro dia do mês
//    $data_inicio = date('Y-m-01', strtotime("first day of {$ano}-{$mes}"));
//
//    // Obtém a data do último dia do mês
//    $data_fim = date('Y-m-t', strtotime("last day of {$ano}-{$mes}"));
//
//    $Extrato = Extrato::select()
//      ->whereBetween('Extrato.Data', [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'])
//      ->get();
//
//    $this->categorizarExtratoPassado($Extrato);
//
//  }

}
