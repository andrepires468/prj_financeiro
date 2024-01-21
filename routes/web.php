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

if(!env('API_AAAA'))
  echo "aaaaaaaaa";


$router->group(['prefix' => env('API_VERSION')], function() use ($router) {

  $router->group(['prefix' => 'auth'], function() use ($router) {
    $router->post('login', 'AuthController@Login');
    $router->post('validate-login', 'AuthController@ValidateLogin');
//    $router->post('forgot-password', 'AuthController@ForgotPassword');
//    $router->post('change-password', 'AuthController@ChangePassword');
  });

});

$router->group(['prefix' => env('API_VERSION'), 'middleware' => 'auth:api'], function() use ($router) {

  $router->group(['prefix' => 'auth'], function() use ($router) {
    $router->get('refresh', 'AuthController@Refresh');
    $router->post('logout', 'AuthController@Logout');
  });

  $router->group(['prefix' => 'process'], function() use ($router) {
    $router->post('update', 'ProcessController@Update');
  });


});
