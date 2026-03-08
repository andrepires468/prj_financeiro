<?php

namespace App\Helpers;

use Illuminate\Http\Response;

class ApiResponse
{

  public function __construct()
  {
  }

  public static function GetSuccessResponse(
    $data = [],
    $message = "OK"
  )
  {

    $httpSuccess = Response::HTTP_OK;

    $result = [
      'HttpCode' => $httpSuccess,
      'Message' => $message,
      'Success' => true,
      'Data' => $data
    ];

    return response($result, $httpSuccess);

  }


  public static function GetErrorResponse(
    \Exception $ex,
    string $message = null,
    int $httpError = Response::HTTP_INTERNAL_SERVER_ERROR,
  )
  {

    $result = [
      'HttpCode' => $httpError,
      'Type' => ($ex->getCode() == 1)? 'warning' : 'error',
      'Message' => $message ?? $ex->getMessage(),
      'Success' => false,
      'Data' => [],
      'Exception' => $ex->__toString(),
    ];

    return response($result, $httpError);

  }
}
