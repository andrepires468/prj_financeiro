<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AuthController extends Controller
{

  protected $authService;

  public function __construct(AuthService $authService)
  {
    $this->authService = $authService;
  }

  public function Login(Request $request)
  {

    try {

      $result = $this->authService->Login($request);

      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
        return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

  public function Register(Request $request)
  {

    try {

      $result = $this->authService->Register($request);

      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

  public function Logout()
  {
    try {

      $this->authService->Logout();

      return ApiResponse::GetSuccessResponse([], 'Desconectado com sucesso');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

  public function Refresh()
  {

    try {

      $result = $this->authService->Refresh();

      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

}
