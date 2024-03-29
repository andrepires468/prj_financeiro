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

  public function CalendarioExtrato(Request $request)
  {

    try {

      $result = $this->financeiroService->CalendarioExtrato($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }


  public function ListExtrato(Request $request)
  {

    try {

      $result = $this->financeiroService->ListExtrato($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }


  public function UpdateExtrato(Request $request)
  {

    try {

      $this->financeiroService->UpdateExtrato($request);
      return ApiResponse::GetSuccessResponse([], 'item extrato atualizado');

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }



}
