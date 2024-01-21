<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ProcessService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProcessController extends Controller
{
    protected $processService;

    public function __construct(
        ProcessService $processService
    )
    {
        $this->processService = $processService;
    }


    public function Update(Request $request)
    {

        try {

          $result = $this->processService->Update($request);

          return ApiResponse::GetSuccessResponse($result, 'Processo Concluído');

        } catch(\Exception $ex) {
          return ApiResponse::GetErrorResponse($ex, 'bad request', Response::HTTP_BAD_REQUEST);
        }

    }

}
