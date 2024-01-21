<?php


namespace App\Services;

use App\Helpers\ApiResponse;
use App\Helpers\Bcrypt;
use App\Helpers\Functions;
use App\Mail\GenericMail;
use App\Models\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthService extends Service
{

  protected $usuario;

  public function __construct(
    Usuario $usuario,
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

  public function Login(Request $request)
  {

    $email = $request->Email;
    $password = $request->Password;

    $validator = Validator::make($request->all(), [
      'Email' => 'email|required|string',
      'Password' => 'required|string|min:4',
    ]);

    if($validator->fails())
      throw new \Exception("Entre com o usuário e senha");

    $usuario = Usuario::where("Usuario.Email", "=", $email)->first();

    if(empty($usuario))
      throw new \Exception('Usuário ou senha inválidos');

    if(!Bcrypt::check($password, $usuario->Password))
      throw new \Exception('Usuário ou senha inválidos');

    return $this->getUserToken($usuario);

  }

  public function ValidateLogin(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'Email' => 'email|required|string',
      'Codigo' => 'required|string',
    ]);

    if($validator->fails())
      throw new \Exception("Falta de parametros");

    $usuario = Usuario::where("Usuario.Email", "=", $request->Email)->first();

    if(!$usuario)
      throw new \Exception("Email não encontrado");

    return $this->getUserToken($usuario);

  }

  public function ForgotPassword(Request $request)
  {

    throw new \Exception('método não implementado');

//    $usuario = Usuario::where('Usuario.Email', '=', $request->Email)->first();
//
//    if(!$usuario)
//      throw new \Exception('Usuário não encontrado');
//
//    $pass = Functions::gerarSenha(8, true, true, true, false);
//    $newPassword = Bcrypt::hash($pass);
//    $tokenAlterarSenha = Functions::gerarGuid();
//    $link = env('APP_URL') . "/alterar-senha/" . $usuario->Id . '/' . $tokenAlterarSenha;
//
//    $this->externoService->alterarSenhaUsuario($newPassword, $usuario);
//
//    $usuario->Password = $newPassword;
//    $usuario->TokenAlterarSenha = $tokenAlterarSenha;
//    $usuario->save();
//
//    Mail::to($usuario->Email)->send(new GenericMail(
//      "Recuperar Senha",
//      ["Link" => $link],
//      'email.recuperar-senha',
//      null
//    ));

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

  public static function getPermissoes()
  {

    try {

      $payload = auth()->payload();
      return (object)$payload->get('Permissoes');

    } catch(\Exception $ex) {
      return null;
    }

  }

  public function Refresh()
  {

    $usuario = $this->getUsuario();

    $customClains = $this->createCustomClains($usuario);

    return $this->createNewAccessToken($customClains, auth()->refresh());

  }

  public function ChangePassword(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'Id' => 'required|int',
      'Password' => 'required|string|min:4',
      'PasswordConfirmacao' => 'required|string|min:4'
    ]);

    if($validator->fails())
      throw new \Exception("Erro na validação dos campos");

    $usuario = Usuario::where('Id', '=', $request->Id)->first();

    if(!$usuario)
      throw new \Exception('registro não encontrado');

    if($request->Password != $request->PasswordConfirmacao)
      throw new \Exception('As senhas não conferem');

    $newPassword = Bcrypt::hash($request->Password);

    $usuario->Password = $newPassword;
    $usuario->save();

  }

}
