<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Usuario;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AuthController extends Controller
{

  protected $authService;

  public function __construct(AuthService $authService)
  {
    $this->middleware('auth:api', ['except' => ['Login', 'ValidateLogin', 'ForgotPassword', 'ChangePassword']]);
    $this->authService = $authService;
  }

  public function Login(Request $request)
  {

    try {

//      $result = $this->authService->Login($request);
//
//      return ApiResponse::GetSuccessResponse($result);

      return ApiResponse::GetSuccessResponse(['OK']);

    } catch(\Exception $ex) {
        return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

  public function ValidateLogin(Request $request)
  {

    try {

      $result = $this->authService->ValidateLogin($request);

      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, 'bad request', Response::HTTP_BAD_REQUEST);
    }

  }

  public function ForgotPassword(Request $request)
  {

    try {

      $this->authService->ForgotPassword($request);
      return ApiResponse::GetSuccessResponse([], "Email de alteração de senha enviado para {$request->Email}");

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, 'bad request', Response::HTTP_BAD_REQUEST);
    }

  }

  public function Logout()
  {
    try {

      $this->authService->Logout();

      return ApiResponse::GetSuccessResponse([], 'Desconectado com sucesso');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, 'bad request', Response::HTTP_BAD_REQUEST);
    }

  }

  public function Refresh()
  {

    try {

      $result = $this->authService->Refresh();

      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, 'bad request', Response::HTTP_BAD_REQUEST);
    }

  }


  public function ChangePassword(Request $request)
  {

    try {

      $result = $this->authService->ChangePassword($request);

      return ApiResponse::GetSuccessResponse($result, 'Senha alterada com sucesso');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, 'bad request', Response::HTTP_BAD_REQUEST);
    }

  }

}
