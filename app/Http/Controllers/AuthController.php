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

      $result = $this->authService->Login($request);

      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
        return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

}
