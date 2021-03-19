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

use App\Http\Middlewares\BearerTokenAuthMiddleware;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/api/v1/user/register', 'UserController@register');
$router->post('/api/v1/user/sign-in', 'UserController@signIn');
$router->post('/api/v1/user/recover-password', 'UserController@generatePasswordRecoveryToken');
$router->post('/api/v1/user/change-password', 'UserController@changePassword');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/api/v1/user/companies', 'UserController@createCompany');
    $router->get('/api/v1/user/companies', 'UserController@readCompanies');
});
