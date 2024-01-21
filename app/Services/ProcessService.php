<?php

namespace App\Services;


use App\Helpers\Functions;
use App\Models\Extrato;
use Illuminate\Http\Request;


class ProcessService extends Service
{

  public function __construct()
  {
    parent::__construct();
  }


  public function Update(Request $request)
  {

    $user = utf8_encode(env('GMAIL_IMAP_USER'));
    $pass = utf8_encode(str_replace('_', ' ', env('GMAIL_IMAP_PASS')));

    $listados = 0;
    $incluidos = 0;
    $emails = 0;

    // Conectar ao servidor IMAP do Gmail
    $mailbox = imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $user, $pass);

    // Definir critérios de pesquisa (por exemplo, o endereço do remetente)
//    $search_criteria = 'FROM "todomundo@nubank.com.br"';
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
              $extrato->IdBancoExtrato = 1; // nubank
              $extrato->save();

              $incluidos++;

            }

            // Marcar o email como lido para evitar que seja baixado novamente
            imap_setflag_full($mailbox, $mail_id, "\\Seen");

          }// if csv

        }// foreach attachments

      } // foreach emails

    } // if emails

    return [
      'emails_lidos' => $emails,
      'itens_listados' => $listados,
      'itens_incluidos' => $incluidos
    ];

  }

}
