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
$router->get('/test', 'Api\TestController@test');
$router->get('/pagination/{DidPhone:[0-9]+}', 'Api\TestController@pagination');
*/

//Herobase accounts table to Leadservice accounts table
$router->post('/postAccount', 'Api\AccountController@postAccount');

//Herobase campaigns table to Leadservice campaigns table
$router->post('/postCampaign', 'Api\CampaignController@postCampaign');

//Herobase channels table to Leadservice channels table
$router->post('/postChannel', 'Api\ChannelController@postChannel');

//Get all call leads Leadservice && Herobase calls table to Leadservice calls table && Delete call lead
$router->get('/getCalls', 'Api\CallController@getCalls');
$router->get('/getCalls/{DidPhone:[0-9]+}', 'Api\CallController@getCalls_DidPhone');
$router->get('/getCalls/{DidPhone:[0-9]+}/{CallerPhone:[0-9]+}', 'Api\CallController@getCalls_DidPhone_CallerPhone');
$router->get('/getCalls/{DidPhone:[0-9]+}/getStartEndDate', 'Api\CallController@getCalls_DidPhone_getStartEndDate');
$router->get('/getCalls/{DidPhone:[0-9]+}/{CallerPhone:[0-9]+}/{SubmitDateTime}', 'Api\CallController@getCalls_DidPhone_CallerPhone_SubmitDateTime');
$router->get('/getCalls/byPeriod/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate');
$router->get('/getCalls/byPeriod/count/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate_Count');
$router->get('/getCalls/byPeriod/count/daybyday/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate_Count_Daybyday');
$router->get('/getCalls/byMonthYear/count/daybyday/{DidPhone:[0-9]+}/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\CallController@getCalls_DidPhone_MonthYear_Count_Daybyday');
$router->get('/getCalls/byPeriod/unique/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate_Unique');
$router->get('/getCalls/byPeriod/unique/daybyday/{DidPhone:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\CallController@getCalls_DidPhone_StartDate_EndDate_Unique_Daybyday');
$router->get('/getCalls/byMonthYear/unique/daybyday/{DidPhone:[0-9]+}/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\CallController@getCalls_DidPhone_MonthYear_Unique_Daybyday');
$router->post('/postCall', 'Api\CallController@postCall');
$router->delete('/deleteCall/{id:[0-9]+}/{userId}', 'Api\CallController@deleteCall');

//Get all call LeadService by DidPhone[]
$router->get('/getCalls2', 'Api\CallController@getCalls_DidPhoneArray');
$router->get('/getCalls2/byPeriod/', 'Api\CallController@getCalls_DidPhoneArray_StartDate_EndDate');
$router->get('/getCalls2/byPeriod/count', 'Api\CallController@getCalls_DidPhoneArray_StartDate_EndDate_Count');
$router->get('/getCalls2/byPeriod/count/daybyday', 'Api\CallController@getCalls_DidPhoneArray_StartDate_EndDate_Count_Daybyday');
$router->get('/getCalls2/byMonthYear/count/daybyday/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\CallController@getCalls_DidPhoneArray_MonthYear_Count_Daybyday');
$router->get('/getCalls2/byPeriod/unique', 'Api\CallController@getCalls_DidPhoneArray_StartDate_EndDate_Unique');
$router->get('/getCalls2/byPeriod/unique/daybyday', 'Api\CallController@getCalls_DidPhoneArray_StartDate_EndDate_Unique_Daybyday');
$router->get('/getCalls2/byMonthYear/unique/daybyday/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\CallController@getCalls_DidPhoneArray_MonthYear_Unique_Daybyday');

//Get all submit form Leadservice && Herobase forms table to Leadservice forms table && Delete submit form lead
$router->get('/getForms', 'Api\FormController@getForms');
$router->get('/getForms/{analyticCampaignId:[0-9]+}', 'Api\FormController@getForms_AnalyticCampaignId');
$router->get('/getForms/{analyticCampaignId:[0-9]+}/getStartEndDate', 'Api\FormController@getForms_AnalyticCampaignId_getStartEndDate');
$router->get('/getForms/{analyticCampaignId:[0-9]+}/{CallerPhone:[0-9]+}/{SubmitDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_CallerPhone_SubmitDateTime');
$router->get('/getForms/byPeriod/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime');
$router->get('/getForms/byPeriod/count/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Count');
$router->get('/getForms/byPeriod/count/daybyday/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Count_Daybyday');
$router->get('/getForms/byMonthYear/count/daybyday/{analyticCampaignId:[0-9]+}/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\FormController@getForms_AnalyticCampaignId_MonthYear_Count_Daybyday');
$router->get('/getForms/byPeriod/unique/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Unique');
$router->get('/getForms/byPeriod/unique/daybyday/{analyticCampaignId:[0-9]+}/{StartDateTime}/{EndDateTime}', 'Api\FormController@getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Unique_Daybyday');
$router->get('/getForms/byMonthYear/unique/daybyday/{analyticCampaignId:[0-9]+}/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\FormController@getForms_AnalyticCampaignId_MonthYear_Unique_Daybyday');
$router->post('/postForm', 'Api\FormController@postForm');
$router->delete('/deleteForm/{id:[0-9]+}/{userId}', 'Api\FormController@deleteForm');

//Get all submit form LeadService by AnalyticCampaignId[], ChannelId[]
$router->get('/getForms2', 'Api\FormController@getForms_AnalyticCampaignId_ChannelId');
$router->get('/getForms2/{channelId:[0-9]+}/getStartEndDate', 'Api\FormController@getForms_channelId_getStartEndDate');
$router->get('/getForms2/{channelId:[0-9]+}/{CallerPhone:[0-9]+}/{SubmitDateTime}', 'Api\FormController@getForms_channelId_CallerPhone_SubmitDateTime');
$router->get('/getForms2/byPeriod', 'Api\FormController@getForms_channelId_StartDateTime_EndDateTime');
$router->get('/getForms2/byPeriod/count', 'Api\FormController@getForms_channelId_StartDateTime_EndDateTime_Count');
$router->get('/getForms2/byPeriod/count/daybyday', 'Api\FormController@getForms_channelId_StartDateTime_EndDateTime_Count_Daybyday');
$router->get('/getForms2/byMonthYear/count/daybyday/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\FormController@getForms_channelId_MonthYear_Count_Daybyday');
$router->get('/getForms2/byPeriod/unique', 'Api\FormController@getForms_channelId_StartDateTime_EndDateTime_Unique');
$router->get('/getForms2/byPeriod/unique/daybyday', 'Api\FormController@getForms_channelId_StartDateTime_EndDateTime_Unique_Daybyday');
$router->get('/getForms2/byMonthYear/unique/daybyday/{Month:[0-9]+}/{Year:[0-9]+}/{TimeZone:[0-9]+}', 'Api\FormController@getForms_channelId_MonthYear_Unique_Daybyday');

//Get Calls & Submit Form & Direct (New Type) LeadService by Didphone[], AnalyticCampaignId[], ChannelId[]
$router->get('/getCallsForms/byDidPhoneAnalyticCampaignId', 'Api\CallsFormsController@getCallsForms_byDidPhoneAnalyticCampaignId');
$router->get('/getCallsForms/byPeriodDidPhoneAnalyticCampaignId', 'Api\CallsFormsController@getCallsForms_byPeriodDidPhoneAnalyticCampaignId');

//Get Calls & Submit Form & Direct (New Type) status LeadService by Didphone[], AnalyticCampaignId[], ChannelId[]
$router->get('/getCallsForms/summaryStatus/byPeriodDidPhoneAnalyticCampaignId', 'Api\CallsFormsController@getCallsForms_summaryStatus_byPeriodDidPhoneAnalyticCampaignId');

//Webhook PBX System, Unbounce System
$router->post('/PbxCallService', 'Api\PbxCallServiceController@PbxCallService');
$router->post('/LandingPageCallService', 'Api\LandingPageCallServiceController@LandingPageCallService');

//Pull Calls & Submit From & Direct LeadService to Alpha 
$router->get('/pullLeadsCalls/{DidPhone:[0-9]+}[/{StartDateTime}[/{EndDateTime}]]', 'Api\PbxCallServiceController@PullLeadsCalls');
$router->get('/pullLeadsForms/{analyticCampaignId:[0-9]+}[/{StartDateTime}[/{EndDateTime}]]', 'Api\LandingPageCallServiceController@PullLeadsForms');
$router->get('/pullLeadsForms2/{channelId:[0-9]+}[/{StartDateTime}[/{EndDateTime}]]', 'Api\LandingPageCallServiceController@PullLeadsForms_ChannelId');
$router->get('/pullAllLeadsCalls', 'Api\PbxCallServiceController@PullAllLeadsCalls');
$router->get('/pullAllLeadsForms', 'Api\LandingPageCallServiceController@PullAllLeadsForms');

//Check Data before PBX System, Unbounce System (For Double Check Data)
$router->post('/CheckPbxCallService', 'Api\CheckPbxCallServiceController@CheckPbxCallService');
$router->get('/CheckLandingPageCallService', 'Api\CheckLandingPageCallServiceController@CheckLandingPageCallService');

//Update Data Calls Forms Table Field parent_id_duplicated
$router->put('/updateParentIdDuplicatedCalls', 'Api\CallController@UpdateParentIdDuplicatedCalls');
$router->put('/updateParentIdDuplicatedForms', 'Api\FormController@UpdateParentIdDuplicatedForms');

//Push Lead Data From Alpha to LeadService
$router->post('/push-leads-data', 'Api\PushLeadsDataFromAlphaController@PushLeadsData');

//Integrate LeadService with MessageService
$router->get('/sendMail', 'Api\SendMailController@SendMail');




