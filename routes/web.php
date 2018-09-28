<?php

date_default_timezone_set('GMT');

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
$router->get('/getcalls/{DidPhone:[0-9]+}', 'Api\CallController@getCalls_DidPhone');
$router->get('/getcalls/{DidPhone:[0-9]+}/{CallerPhone:[0-9]+}', 'Api\CallController@getCalls_DidPhone_CallerPhone');
$router->get('/getcalls/{DidPhone:[0-9]+}/getStartEndDate', 'Api\CallController@getCalls_DidPhone_getStartEndDate');
$router->get('/getcalls/{DidPhone:[0-9]+}/{CallerPhone:[0-9]+}/{SubmitDateTime}', 'Api\CallController@getCalls_DidPhone_CallerPhone_SubmitDateTime');
$router->get('/getcalls/byPeriod/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate');
$router->get('/getcalls/byPeriod/count/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate_Count');
$router->post('/postcall', 'Api\CallController@postCall');

$router->get('/getforms', 'Api\FormController@getForms');
$router->get('/getforms/{analyticCampaignId:[0-9]+}', 'Api\FormController@getForms_AnalyticCampaignId');
$router->get('/getforms/{analyticCampaignId:[0-9]+}/getStartEndDate', 'Api\FormController@getForms_AnalyticCampaignId_getStartEndDate');
$router->get('/getforms/{analyticCampaignId:[0-9]+}/{CallerPhone:[0-9]+}/{SubmitDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_CallerPhone_SubmitDateTime');
$router->get('/getforms/byPeriod/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime');
$router->get('/getforms/byPeriod/count/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Count');
$router->post('/postform', 'Api\FormController@postForm');

$router->post('/PbxCallService', 'Api\PbxCallServiceController@PbxCallService');
$router->post('/LandingPageCallService', 'Api\LandingPageCallServiceController@LandingPageCallService');

