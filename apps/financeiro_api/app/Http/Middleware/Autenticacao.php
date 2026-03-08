<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Response;

class Autenticacao
{

  public function handle($request, Closure $next)
  {

    try {

      if(!$tokenReq = $request->header('Authorization'))
        throw new \Exception('Token não passado');

      if(!$tokenEnv = env('TOKEN_AUTH'))
        throw new \Exception('Token não configurado na API');

      if($tokenReq != $tokenEnv)
        throw new \Exception('Token inválido');

      return $next($request);

    } catch(\Exception $ex) {

      return ApiResponse::GetErrorResponse($ex, $ex->getMessage(),Response::HTTP_UNAUTHORIZED);

    }

  }
}
