<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


use Illuminate\Http\Response;

$router->get('/', function() use ($router) {
  return $router->app->version();
});


$router->group(['prefix' => env('API_VERSION'), 'middleware' => 'autenticacao'], function() use ($router) {

  $router->group(['prefix' => 'auth'], function() use ($router) {

    $router->get('test', function() {
      return response(['OK'], Response::HTTP_OK);
    });

    $router->post('login', 'AuthController@Login');

  });


  $router->group(['prefix' => 'process'], function() use ($router) {
    $router->post('update', 'ProcessController@Update');
  });

});
