<?php

date_default_timezone_set('Asia/Bangkok');

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

$router->get('/key', function () {
    return str_random(32);
});

/*
$router->get('/index', 'Api\TestController@index');
$router->get('/get', 'Api\TestController@getItem');
$router->post('/post', 'Api\TestController@postItem');
$router->put('/put/{id}', 'Api\TestController@putItem');
$router->delete('/delete/{id}', 'Api\TestController@deleteItem');
*/

$router->post('/postaccount', 'Api\AccountController@postAccount');

$router->post('/postcampaign', 'Api\CampaignController@postCampaign');

$router->post('/postchannel', 'Api\ChannelController@postChannel');

$router->get('/getcalls', 'Api\CallController@getCalls');
$router->post('/postcall', 'Api\CallController@postCall');

$router->get('/getforms', 'Api\FormController@getForms');
$router->post('/postform', 'Api\FormController@postForm');
