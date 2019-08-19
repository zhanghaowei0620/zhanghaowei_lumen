<?php

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//$router->get('/test', function () use ($router) {
//
////    return $router->app->version();
//});
$router->post('/login','User\UserController@logindo');
$router->options('/login',function() use ($router){
    return [];
});
$router->group(['middleware' => 'checkLogin'], function () use ($router) {
    $router->get('/weather','Test\TestController@weather');
});


























$router->post('/test','Test\TestController@test');
$router->post('/reg','User\UserController@register');
$router->options('/reg',function() use ($router){
    return [];
});
$router->post('/logindo','User\UserController@logindo');
$router->options('/logindo',function() use ($router){
    return [];
});

$router->group(['middleware' => 'checkLogin'], function () use ($router) {
    $router->post('/center','User\UserController@center');
});
$router->options('/center',function() use ($router){
    return [];
});

$router->get('/ajax','Test\TestController@ajaxTest');