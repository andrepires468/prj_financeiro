<?php


namespace App\Services;

use App\Models\BancoExtrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class BancoExtratoService extends Service
{

  public function __construct()
  {
    parent::__construct();
  }

  public function Get(Request $request)
  {

    if(!$this->idBancoExtrato)
      throw new \Exception('Banco de extrato do usuário não encontrado');

    $banco = BancoExtrato::where('Id', '=', $this->idBancoExtrato)
      ->where('IdUsuario', '=', $this->idUsuario)
      ->first();

    if(!$banco)
      throw new \Exception('Banco de extrato não encontrado');

    $gmailUser = null;
    if($banco->GmailUser) {
      try {
        $gmailUser = Crypt::decrypt($banco->GmailUser);
      } catch(\Exception $e) {
        $gmailUser = $banco->GmailUser;
      }
    }

    return [
      'Id' => $banco->Id,
      'BancoNome' => $banco->BancoNome,
      'GmailUser' => $gmailUser,
      'GmailPassConfigurado' => !empty($banco->GmailPass),
      'MesInicio' => $banco->MesInicio ? (int)$banco->MesInicio : null,
      'AnoInicio' => $banco->AnoInicio ? (int)$banco->AnoInicio : null,
    ];

  }

  public function Put(Request $request)
  {

    if(!$this->idBancoExtrato)
      throw new \Exception('Banco de extrato do usuário não encontrado');

    $validator = Validator::make($request->all(), [
      'GmailUser' => 'nullable|string|max:500',
      'GmailPass' => 'nullable|string|max:500',
      'MesInicio' => 'nullable|integer|min:1|max:12',
      'AnoInicio' => 'nullable|integer|min:2000|max:2100',
    ]);

    if($validator->fails())
      throw new \Exception($validator->errors()->first());

    $banco = BancoExtrato::where('Id', '=', $this->idBancoExtrato)
      ->where('IdUsuario', '=', $this->idUsuario)
      ->first();

    if(!$banco)
      throw new \Exception('Banco de extrato não encontrado');

    // GmailUser: atualiza se enviado no request (permite limpar enviando string vazia)
    if($request->has('GmailUser')) {
      $user = trim((string)$request->GmailUser);
      $banco->GmailUser = $user !== '' ? Crypt::encrypt($user) : null;
    }

    // GmailPass: só altera se vier preenchido (em branco = manter atual)
    if($request->filled('GmailPass')) {
      $pass = str_replace(' ', '', (string)$request->GmailPass);
      if($pass !== '')
        $banco->GmailPass = Crypt::encrypt($pass);
    }

    $hasMes = $request->filled('MesInicio');
    $hasAno = $request->filled('AnoInicio');

    if($hasMes !== $hasAno)
      throw new \Exception('Informe MesInicio e AnoInicio juntos, ou deixe ambos em branco');

    if($hasMes && $hasAno) {
      $banco->MesInicio = (int)$request->MesInicio;
      $banco->AnoInicio = (int)$request->AnoInicio;
    } elseif($request->has('MesInicio') && $request->has('AnoInicio')) {
      $banco->MesInicio = null;
      $banco->AnoInicio = null;
    }

    $banco->save();

  }

}
