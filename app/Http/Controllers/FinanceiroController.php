<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\FinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class FinanceiroController extends Controller
{

  protected $financeiroService;

  public function __construct(FinanceiroService $financeiroService)
  {
    $this->financeiroService = $financeiroService;
  }

  public function List(Request $request)
  {

    try {

      $result = $this->financeiroService->List($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Get(Request $request)
  {
    try {

      $result = $this->financeiroService->Get($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Post(Request $request)
  {
    try {

      $result = $this->financeiroService->Post($request);
      return ApiResponse::GetSuccessResponse($result, 'item criado');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Put(Request $request)
  {
    try {

      $this->financeiroService->Put($request);
      return ApiResponse::GetSuccessResponse([], 'item atualizado');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Delete(Request $request)
  {
    try {

      $this->financeiroService->Delete($request);
      return ApiResponse::GetSuccessResponse([], 'item removido');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }



}
