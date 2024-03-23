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


$router->get('/', function() use ($router) {
  return $router->app->version();
});


$router->group(['prefix' => env('API_VERSION')], function() use ($router) {

  $router->group(['prefix' => 'auth'], function() use ($router) {
    $router->post('login', 'AuthController@Login');
  });

});

$router->group(['prefix' => env('API_VERSION'), 'middleware' => 'autenticacao'], function() use ($router) {

  $router->group(['prefix' => 'process'], function() use ($router) {
    $router->post('update', 'ProcessController@Update');
  });

});

$router->group(['prefix' => env('API_VERSION'), 'middleware' => 'auth:api'], function() use ($router) {

  $router->group(['prefix' => 'auth'], function() use ($router) {
    $router->post('refresh', 'AuthController@Refresh');
    $router->post('logout', 'AuthController@Logout');
  });

  $router->group(['prefix' => 'financeiro'], function() use ($router) {
    $router->get('list', 'FinanceiroController@List');
    $router->get('{id}', 'FinanceiroController@Get');
    $router->post('', 'FinanceiroController@Post');
    $router->put('', 'FinanceiroController@Put');
    $router->delete('', 'FinanceiroController@Delete');
  });


});
