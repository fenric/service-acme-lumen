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

$router->post('/api/user/register', 'UserController@register');
$router->post('/api/user/sign-in', 'UserController@signIn');
$router->post('/api/user/recover-password', 'UserController@recoverPassword');
$router->post('/api/user/change-password', 'UserController@changePassword');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/api/user/companies', 'UserController@createCompany');
    $router->get('/api/user/companies', 'UserController@listCompanies');
});
