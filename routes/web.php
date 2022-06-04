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

$router->get('/', function () use ($router) {
    return response()->json([
        "data" => "Welcome to Tomcat API"
    ]);
});

$router->post('login', 'AuthenticationController@login');

$router->group(['middleware' => 'auth:moderator'], function () use ($router) {
    // $router->post('create-group', 'QuizController@createGroup');
    $router->post('create-game', 'QuizController@createGame');
    $router->post('add-user', 'AuthenticationController@addUserData');
    $router->post('close-game', 'QuizController@closeGame');
});

$router->group(['middleware' => 'auth:user'], function () use ($router) {
    $router->get('get-leaderboard/{gameName}', 'QuizController@getLeadaerBoard');
    $router->post('update-score', 'QuizController@updateGameScore');
});
