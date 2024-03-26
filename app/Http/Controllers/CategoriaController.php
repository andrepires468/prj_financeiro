<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\CategoriaService;
use App\Services\FinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class CategoriaController extends Controller
{

  protected $categoriaService;

  public function __construct(CategoriaService $categoriaService)
  {
    $this->categoriaService = $categoriaService;
  }

  public function List(Request $request)
  {

    try {

      $result = $this->categoriaService->List($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Get(Request $request)
  {

    try {

      $result = $this->categoriaService->Get($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Post(Request $request)
  {

    try {

      $result = $this->categoriaService->Post($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Put(Request $request)
  {

    try {

      $result = $this->categoriaService->Put($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function Delete(Request $request)
  {

    try {

      $result = $this->categoriaService->Delete($request);
      return ApiResponse::GetSuccessResponse($result);

    } catch(\Exception $ex) {
      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

}
