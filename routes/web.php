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
    $router->post('add-user', 'AuthenticationController@addUserData');
    $router->post('create-game', 'QuizController@createGame');
    $router->post('create-game-session', 'QuizController@createGameSession');
    $router->post('close-game-session', 'QuizController@closeGameSession');
});

$router->group(['middleware' => 'auth:user'], function () use ($router) {
    $router->get('get-leaderboard/{sessionUuid}', 'QuizController@getLeadaerBoard');
    $router->post('update-score', 'QuizController@updateGameSessionScore');
});
