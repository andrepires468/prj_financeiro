<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\BancoExtratoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class BancoExtratoController extends Controller
{

  protected $bancoExtratoService;

  public function __construct(BancoExtratoService $bancoExtratoService)
  {
    $this->bancoExtratoService = $bancoExtratoService;
  }

  public function Get(Request $request)
  {

    try {

      $result = $this->bancoExtratoService->Get($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

  public function Put(Request $request)
  {

    try {

      $this->bancoExtratoService->Put($request);
      return ApiResponse::GetSuccessResponse();

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_BAD_REQUEST);
    }

  }

}
