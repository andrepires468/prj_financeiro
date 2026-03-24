<?php


namespace App\Services;

use App\Helpers\Bcrypt;
use App\Models\BancoExtrato;
use App\Models\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthService extends Service
{

  protected $usuario;

  public function __construct(
    Usuario $usuario
  )
  {
    parent::__construct();
    $this->usuario = $usuario;
  }


  private function createNewAccessToken($usuario, $token)
  {

    $date = new DateTime();

    return [
      'Usuario' => $usuario,
      'created_at' => $date->getTimestamp(),
      'accessToken' => $token,
      'token_type' => 'bearer',
      'expires_in' => Auth::factory()->getTTL() * 60
    ];

  }

  private function createCustomClains($usuario)
  {

    return [
      'Usuario' => [
        'IdUsuario' => $usuario->Id ?? $usuario->IdUsuario,
        'IdBancoExtrato' => $usuario->IdBancoExtrato,
        'Email' => $usuario->Email,
        'Nome' => $usuario->Nome,
      ],
    ];

  }

  private function getUserToken($usuario)
  {

    $customClains = $this->createCustomClains($usuario);

    if(!$token = auth()->claims($customClains)->login($usuario))
      throw new \Exception('Erro ao criar o token do usuário');

    return $this->createNewAccessToken($customClains, $token);

  }

  public function Logout()
  {

    auth()->logout();

  }

  public static function getUsuario()
  {

    try {

      $payload = auth()->payload();
      return (object)$payload->get('Usuario');

    } catch(\Exception $ex) {
      return null;
    }

  }

  public function Login(Request $request)
  {

    $email = $request->Email;
    $password = $request->Password;

    $validator = Validator::make($request->all(), [
      'Email' => 'email|required|string',
      'Password' => 'required|string|min:4',
      'IdBancoExtrato' => 'sometimes|nullable|integer',
    ]);

    if($validator->fails())
      throw new \Exception("Entre com o usuário e senha");

    $usuario = Usuario::where('Email', '=', $email)->first();

    if(empty($usuario))
      throw new \Exception('Usuário ou senha inválidos');

    if(!Bcrypt::check($password, $usuario->Password))
      throw new \Exception('Usuário ou senha inválidos');

    $idUsuario = $usuario->Id;

    if($request->filled('IdBancoExtrato')) {
      $banco = BancoExtrato::where('IdUsuario', '=', $idUsuario)
        ->where('Id', '=', (int)$request->IdBancoExtrato)
        ->first();
      if(empty($banco))
        throw new \Exception('Conta bancária inválida ou não pertence ao usuário');
    } else {
      $banco = BancoExtrato::where('IdUsuario', '=', $idUsuario)
        ->orderByDesc('Default')
        ->orderBy('Id')
        ->first();
      if(empty($banco))
        throw new \Exception('Banco do usuário não encontrado');
    }

    $usuario->IdBancoExtrato = $banco->Id;

    return $this->getUserToken($usuario);

  }

  /**
   * Cria usuário, conta de extrato (e opcionalmente Gmail / período do calendário).
   * Requer AUTH_REGISTER_ENABLED=true no .env.
   */
  public function Register(Request $request)
  {

    if(!filter_var(env('AUTH_REGISTER_ENABLED', false), FILTER_VALIDATE_BOOLEAN))
      throw new \Exception('Cadastro de novos usuários está desabilitado');

    $validator = Validator::make($request->all(), [
      'Nome' => 'required|string|max:200',
      'Email' => 'email|required|string|max:200',
      'Password' => 'required|string|min:4',
      'BancoNome' => 'required|string|max:200',
      'GmailUser' => 'nullable|string|max:500',
      'GmailPass' => 'nullable|string|max:500',
      'MesInicio' => 'nullable|integer|min:1|max:12',
      'AnoInicio' => 'nullable|integer|min:2000|max:2100',
    ]);

    if($validator->fails())
      throw new \Exception($validator->errors()->first());

    $hasMes = $request->filled('MesInicio');
    $hasAno = $request->filled('AnoInicio');
    if($hasMes !== $hasAno)
      throw new \Exception('Informe MesInicio e AnoInicio juntos, ou deixe ambos em branco');

    if(Usuario::where('Email', '=', $request->Email)->exists())
      throw new \Exception('E-mail já cadastrado');

    $usuario = DB::connection('home')->transaction(function() use ($request) {

      $u = new Usuario();
      $u->Nome = $request->Nome;
      $u->Email = $request->Email;
      $u->Password = Bcrypt::hash($request->Password);
      $u->save();

      $banco = new BancoExtrato();
      $banco->BancoNome = $request->BancoNome;
      $banco->IdUsuario = $u->Id;
      $banco->Default = true;
      $banco->GmailUser = $request->GmailUser ?: null;
      $banco->GmailPass = $request->GmailPass ?: null;
      if($request->filled('MesInicio') && $request->filled('AnoInicio')) {
        $banco->MesInicio = (int)$request->MesInicio;
        $banco->AnoInicio = (int)$request->AnoInicio;
      }
      $banco->save();

      $u->IdBancoExtrato = $banco->Id;

      return $u;

    });

    return $this->getUserToken($usuario);

  }

  public function Refresh()
  {

    $usuario = $this->getUsuario();

    $customClains = $this->createCustomClains($usuario);

    return $this->createNewAccessToken($customClains, auth()->refresh());

  }

}
