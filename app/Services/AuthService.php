<?php


namespace App\Services;

use App\Helpers\Bcrypt;
use App\Models\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

  public function Refresh()
  {

    $usuario = $this->getUsuario();

    $customClains = $this->createCustomClains($usuario);

    return $this->createNewAccessToken($customClains, auth()->refresh());

  }

}
