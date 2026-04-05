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
    $router->post('register', 'AuthController@Register');
  });

});

$router->group(['prefix' => env('API_VERSION'), 'middleware' => 'auth:api'], function() use ($router) {

  $router->group(['prefix' => 'auth'], function() use ($router) {
    $router->get('refresh', 'AuthController@Refresh');
    $router->post('logout', 'AuthController@Logout');
    $router->post('change-password', 'AuthController@ChangePassword');
  });

  $router->group(['prefix' => 'financeiro'], function() use ($router) {
    $router->get('calendario-extrato', 'FinanceiroController@CalendarioExtrato');
    $router->get('lista-extrato', 'FinanceiroController@ListaExtrato');
    $router->put('update-extrato', 'FinanceiroController@UpdateExtrato');
    $router->get('chart-categoria-ultimos-meses', 'FinanceiroController@ChartCategoriaUltimosMeses');
    $router->get('chart-ultimos-meses', 'FinanceiroController@ChartUltimosMeses');
    $router->get('chart-categorias-mes', 'FinanceiroController@ChartCategoriasMes');
  });

  $router->group(['prefix' => 'categoria'], function() use ($router) {
    $router->get('list', 'CategoriaController@List');
    $router->get('{id}', 'CategoriaController@Get');
    $router->post('', 'CategoriaController@Post');
    $router->put('', 'CategoriaController@Put');
    $router->delete('', 'CategoriaController@Delete');
  });

  $router->group(['prefix' => 'process'], function() use ($router) {
    $router->post('import-extrato', 'ProcessController@ImportExtrato');
    $router->post('categorizar-extrato', 'ProcessController@CategorizarExtrato');
    $router->post('fechamento', 'ProcessController@Fechamento');
    $router->post('fechamento-categoria', 'ProcessController@FechamentoCategoria');
    $router->post('fechamento-todas-categorias', 'ProcessController@FechamentoTodasCategorias');
  });

});

$router->group(['prefix' => env('API_VERSION'), 'middleware' => 'autenticacao'], function() use ($router) {

//  $router->group(['prefix' => 'process'], function() use ($router) {
//    $router->post('update', 'ProcessController@Update');
//  });

});

