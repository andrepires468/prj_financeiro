<?php


namespace App\Services;


use App\Helpers\Bcrypt as Bcrypt;
use App\Models\Usuario;
use Illuminate\Http\Request;
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

    return [
      'Usuario' => [
        'IdUsuario' => $usuario->Id ?? $usuario->IdUsuario,
        'Email' => $usuario->Email,
        'Nome' => $usuario->Nome,
      ],
    ];


  }

}
