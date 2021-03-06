<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use App\Model\Channel;
use App\Model\Call;
use App\Model\Form;
use App\Model\Comment_call;
use App\Model\Comment_form;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;

class CallsFormsController extends BaseController
{
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function getCallsForms_byDidPhoneAnalyticCampaignId(Request $request)
    {        
        if(isset($request->page) && !isset($request->limit) || !isset($request->page) && isset($request->limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }      

        if(isset($request->status) && $request->status != 1 && $request->status != 2)
        {
            return response(array(
                'Message' => 'Please send parameter status = 1(answered) or status = 2(miss call) only'
            ), '400');
        }
            
        $response = $this->Response_getCallsForms($request); 
        return response($response, '200');
    }

    public function getCallsForms_byPeriodDidPhoneAnalyticCampaignId(Request $request)
    {   
        if(isset($request->page) && !isset($request->limit) || !isset($request->page) && isset($request->limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }    

        if(isset($request->status) && $request->status != 1 && $request->status != 2)
        {
            return response(array(
                'Message' => 'Please send parameter status = 1(answered) or status = 2(miss call) only'
            ), '400');
        }

        $request->startDateTime = Carbon::parse($request->startDateTime);
        $request->startDateTime->setTimezone($this->timezone);

        $request->endDateTime = Carbon::parse($request->endDateTime);
        $request->endDateTime->setTimezone($this->timezone);
            
        $response = $this->Response_getCallsForms($request); 
        return response($response, '200');
    }

    public function Response_getCallsForms(Request $request)
    {   
        $response = array();
        $response['links'] = array();
        $array_channel = array(); 
        $array_type = array();
        $key = 0;
        $keys = 0;

        if(isset($request->didPhone))
        {
            $count_didPhone = count($request->didPhone);
            if($count_didPhone > 0)
            {
                $channels_didPhones = Channel::whereIn('tracking_phone', $request->didPhone)->get();
    
                foreach($channels_didPhones as $channels_didPhone)
                {
                    $array_channel[$key] = $channels_didPhone->channel_id;
                    $array_type[$key] = "calls";
                    $key++;
                }
            }
        }

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $array_type[$key] = "forms";
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('channel_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $array_type[$key] = "forms";  
                    $key++;
                }
            }
        }

        $count_channel = count($array_channel);

        for($x=0;$x<$count_channel;$x++)
        {
            if($array_type[$x] == "calls")
            { 
                $calls = DB::table('calls')
                ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                ->where('channels.channel_id', '=', $array_channel[$x])
                ->select(
                    'channels.tracking_phone', 
                    'calls.id', 'calls.channel_id', 'calls.duration', 'calls.status', 'calls.recording_url', 'calls.phone', 'calls.date'
                );
                if(isset($request->startDateTime) && isset($request->endDateTime))
                {
                    $calls = $calls->whereBetween('calls.date', [$request->startDateTime, $request->endDateTime]);
                }
                if(isset($request->status))
                {
                    $calls = $calls->where('calls.status', '=', $request->status);
                }
                if(isset($request->is_duplicated))
                {
                    $calls = $calls->where('calls.is_duplicated', '=', $request->is_duplicated);
                }

                $calls = $calls->orderBy('calls.date', 'asc')
                                ->get();                                        

                foreach($calls as $call)
                {                                
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
                    //$dt->setTimezone($this->timezone);
                    $submitDateTime = $dt->format(DateTime::ISO8601);   

                    if($call->status == 1)
                    {
                        $status = "ANSWER";
                    }
                    else
                    {
                        $status = "MISSED CALL";
                    }

                    $response['content'][$keys]['type'] = "phone";
                    $response['content'][$keys]['channelId'] = "$call->channel_id";
                    $response['content'][$keys]['rowId'] = "$call->id";
                    $response['content'][$keys]['duration'] = "$call->duration";
                    $response['content'][$keys]['status'] = "$status";
                    $response['content'][$keys]['recordingUrl'] = "$call->recording_url";
                    $response['content'][$keys]['heroNumber'] = "$call->tracking_phone";
                    $response['content'][$keys]['callerId'] = "$call->phone";
                    $response['content'][$keys]['submitDateTime'] = "$submitDateTime";

                    $count_comment_call = Comment_call::where('call_id', '=', $call->id)->count();

                    if($count_comment_call > 0)
                    {
                        $firstName = $this->comment_call($call->id, 'firstName');
                        $response['content'][$keys]['firstName'] = "$firstName";

                        $lastName = $this->comment_call($call->id, 'lastName');
                        $response['content'][$keys]['lastName'] = "$lastName";

                        $email = $this->comment_call($call->id, 'email');
                        $response['content'][$keys]['email'] = "$email";

                        $sourceId = $this->comment_call($call->id, 'sourceId');
                        $response['content'][$keys]['sourceId'] = "$sourceId";

                        $statusId = $this->comment_call($call->id, 'statusId');
                        $response['content'][$keys]['statusId'] = "$statusId";

                        $comment_calls = Comment_call::where([
                            ['call_id', '=', $call->id],
                            ['typeOfAction', '!=', '']
                        ])->orderBy('id', 'asc')->get();

                        foreach($comment_calls as $Keycomment_calls => $comment_call)
                        {
                            if($comment_call->typeOfAction == "add")
                            {
                                $response['content'][$keys]['comment'][$Keycomment_calls]['remarkId'] = "$comment_call->id"; 
                            }                       
                            else
                            {
                                $response['content'][$keys]['comment'][$Keycomment_calls]['remarkId'] = "$comment_call->remarkId"; 
                            }
                            $response['content'][$keys]['comment'][$Keycomment_calls]['remarkValue'] = "$comment_call->remarkValue";
                            $response['content'][$keys]['comment'][$Keycomment_calls]['reporterId'] = "$comment_call->reporterId";
                            $response['content'][$keys]['comment'][$Keycomment_calls]['typeOfAction'] = "$comment_call->typeOfAction";
                            $response['content'][$keys]['comment'][$Keycomment_calls]['dateTimeCreated'] = "$comment_call->created_at";
                        }
                    }
                    else
                    {
                        $response['content'][$keys]['firstName'] = "";
                        $response['content'][$keys]['lastName'] = "";
                        $response['content'][$keys]['email'] = "";
                        $response['content'][$keys]['sourceId'] = "";
                        $response['content'][$keys]['statusId'] = "";
                        $response['content'][$keys]['comment'] = array();
                    }

                    $response['content'][$keys]['links'][0]['rel'] = "self";
                    $response['content'][$keys]['links'][0]['href'] = "http://leadservice.heroleads.co.th/leadService/public/index.php/getCalls/".$call->tracking_phone."/".$call->phone."/".$submitDateTime;
                    $response['content'][$keys]['links'][0]['hreflang'] = null;
                    $response['content'][$keys]['links'][0]['media'] = null;
                    $response['content'][$keys]['links'][0]['title'] = null;
                    $response['content'][$keys]['links'][0]['type'] = null;
                    $response['content'][$keys]['links'][0]['deprecation'] = null;

                    $keys++;
                }
            }
            else if(($array_type[$x] == "forms"))
            {
                $forms = DB::table('forms')
                ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                ->where('channels.channel_id', '=', $array_channel[$x])
                ->select(
                    'channels.adwords_campaign_id', 'channels.facebook_campaign_id',
                    'forms.channel_id', 'forms.id', 'forms.name', 'forms.email', 'forms.phone', 'forms.custom_attributes', 'forms.created_at_forms'
                );
                if(isset($request->startDateTime) && isset($request->endDateTime))
                {
                    $forms = $forms->whereBetween('forms.created_at_forms', [$request->startDateTime, $request->endDateTime]);
                }
                if(isset($request->is_duplicated))
                {
                    $forms = $forms->where('forms.is_duplicated', '=', $request->is_duplicated);
                }

                $forms = $forms->orderBy('forms.created_at_forms', 'asc')
                                ->get();

                foreach($forms as $form)
                {                               
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
                    //$dt->setTimezone('GMT');
                    $submitDateTime = $dt->format(DateTime::ISO8601);   

                    if(trim($form->adwords_campaign_id) != "")
                    {
                        $analyticCampaignId = $form->adwords_campaign_id;
                    }
                    else
                    {
                        $analyticCampaignId = $form->facebook_campaign_id;
                    }

                    if($analyticCampaignId == "")
                    {
                        $type = "direct";
                    }
                    else
                    {
                        $type = "submitted";
                    }

                    $array_name = explode(" ", $form->name);
                    $firstName = $array_name[0];
                    $lastName = "";

                    for($z=1;$z<count($array_name);$z++)
                    {
                        $lastName.= $array_name[$z].' ';
                    }

                    $lastName = substr($lastName, 0, -1);
                    
                    $response['links'] = array();
                    $response['content'][$keys]['type'] = $type;
                    $response['content'][$keys]['channelId'] = $form->channel_id;
                    $response['content'][$keys]['rowId'] = "$form->id";
                    $response['content'][$keys]['analyticCampaignId'] = "$analyticCampaignId";
                    $response['content'][$keys]['phone'] = "$form->phone";
                    $response['content'][$keys]['custom_attributes'] = "$form->custom_attributes";
                    $response['content'][$keys]['submitDateTime'] = "$submitDateTime";

                    $count_comment_form = Comment_form::where('form_id', '=', $form->id)->count();

                    if($count_comment_form > 0)
                    {                        
                        $firstName = $this->comment_form($form->id, 'firstName');
                        $response['content'][$keys]['firstName'] = "$firstName";

                        $lastName = $this->comment_form($form->id, 'lastName');
                        $response['content'][$keys]['lastName'] = "$lastName";

                        $email = $this->comment_form($form->id, 'email');
                        $response['content'][$keys]['email'] = "$email";

                        $sourceId = $this->comment_form($form->id, 'sourceId');
                        $response['content'][$keys]['sourceId'] = "$sourceId";

                        $statusId = $this->comment_form($form->id, 'statusId');
                        $response['content'][$keys]['statusId'] = "$statusId";
                
                        $comment_forms = Comment_form::where([
                            ['form_id', '=', $form->id],
                            ['typeOfAction', '!=', '']
                        ])->orderBy('id', 'asc')->get();

                        foreach($comment_forms as $Keycomment_forms => $comment_form)
                        {
                            if($comment_form->typeOfAction == "add")
                            {
                                $response['content'][$keys]['comment'][$Keycomment_forms]['remarkId'] = "$comment_form->id"; 
                            }                       
                            else
                            {
                                $response['content'][$keys]['comment'][$Keycomment_forms]['remarkId'] = "$comment_form->remarkId"; 
                            }                     
                            $response['content'][$keys]['comment'][$Keycomment_forms]['remarkValue'] = "$comment_form->remarkValue";
                            $response['content'][$keys]['comment'][$Keycomment_forms]['reporterId'] = "$comment_form->reporterId";
                            $response['content'][$keys]['comment'][$Keycomment_forms]['typeOfAction'] = "$comment_form->typeOfAction";
                            $response['content'][$keys]['comment'][$Keycomment_forms]['dateTimeCreated'] = "$comment_form->created_at";
                        }                        
                    }
                    else
                    {
                        $response['content'][$keys]['firstName'] = $firstName;
                        $response['content'][$keys]['lastName'] = $lastName;
                        $response['content'][$keys]['email'] = "$form->email";
                        $response['content'][$keys]['sourceId'] = "";
                        $response['content'][$keys]['statusId'] = "";                        
                        $response['content'][$keys]['comment'] = array();
                    }

                    $response['content'][$keys]['links'][0]['rel'] = "self";
                    $response['content'][$keys]['links'][0]['href'] = "http://leadservice.heroleads.co.th/leadService/public/index.php/getForms/".$analyticCampaignId."/".$form->phone."/".$submitDateTime;
                    $response['content'][$keys]['links'][0]['hreflang'] = null;
                    $response['content'][$keys]['links'][0]['media'] = null;
                    $response['content'][$keys]['links'][0]['title'] = null;
                    $response['content'][$keys]['links'][0]['type'] = null;
                    $response['content'][$keys]['links'][0]['deprecation'] = null;

                    $keys++;
                }
            }
        }
                        
        $page = ($request->has('page')) ? intval($request->page) : 1;
        $size = ($request->has('limit')) ? intval($request->limit) : 25;
        
        $collection = collect($response['content']);
        $collection = $collection->sortByDesc('submitDateTime');
        $total = $collection->count();

        return new LengthAwarePaginator(
            array_values($collection->forPage($page, $size)->toArray()),
            $collection->count(),
            $size,
            $page
        );              
    }
    
    public function comment_call($call_id, $field)
    {
        $comment_call = Comment_call::where([
            ['call_id', '=', $call_id], 
            [$field, '!=', '']
        ])->orderBy('id', 'Desc')->first();

        if(isset($comment_call->$field))
        {
            return $comment_call->$field;
        }else{
            return "";
        }    
    }

    public function comment_form($form_id, $field)
    {
        $comment_form = Comment_form::where([
            ['form_id', '=', $form_id], 
            [$field, '!=', '']
        ])->orderBy('id', 'Desc')->first();
        
        if(isset($comment_form->$field))
        {
            return $comment_form->$field;
        }else{
            return "";
        }    
    }

    public function getCallsForms_summaryStatus_byPeriodDidPhoneAnalyticCampaignId(Request $request)
    {        
        $array_StartDate = explode("+", $request->startDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->endDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->startDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->endDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $period = CarbonPeriod::create($request->startDateTime, $request->endDateTime);
        $count = $period->count();    
            
        $response = $this->Response_getCallsForms_summaryStatus_byPeriodDidPhoneAnalyticCampaignId($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count); 
        return response($response, '200');
    }

    public function Response_getCallsForms_summaryStatus_byPeriodDidPhoneAnalyticCampaignId(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {
        $response = array();
        $array_channel = array(); 
        $array_type = array();
        
        $key = 0;
        $uniqueLeadsTotal = 0;

        if(isset($request->didPhone))
        {
            $count_didPhone = count($request->didPhone);
            if($count_didPhone > 0)
            {
                $channels_didPhones = Channel::whereIn('tracking_phone', $request->didPhone)->get();
    
                foreach($channels_didPhones as $channels_didPhone)
                {
                    $array_channel[$key] = $channels_didPhone->channel_id;
                    $array_type[$key] = "calls";
                    $key++;
                }
            }
        }

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $array_type[$key] = "forms";
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('adwords_campaign_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $array_type[$key] = "forms";
                    $key++;
                }
            }
        }

        $last_day = $count-1;

        for($day=0;$day<$count;$day++)
        {        
            $uniqueLeadsPerDay = 0;

            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;
                $StartDateTime_true = $StartDateTime_Convert;

                $array = explode("T",$request->startDateTime);
                $EndDateTime = $array[0]." 23:59:59"."+".$timezone_StartDate;

                $dt = Carbon::parse($EndDateTime);
                $EndDateTime = $dt->setTimezone($this->timezone);
            }
            else if($day == $last_day)
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();
                                                            
                $EndDateTime = $EndDateTime_Convert;
            }
            else
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();

                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $EndDateTime = $dt->addDay();
            }
                                        
            for($x=0;$x<$key;$x++)
            {
                if($array_type[$x] == "calls")
                {
                    $count_calls = DB::table('calls')
                                        ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')                            
                                        ->where('channels.channel_id', '=', $array_channel[$x])
                                        ->where('is_duplicated', '=', '0')
                                        ->whereBetween('calls.date', [$StartDateTime, $EndDateTime])
                                        ->count();

                    $uniqueLeadsPerDay += $count_calls;
                }
                else if($array_type[$x] == "forms")
                {
                    $count_forms = DB::table('forms')
                                        ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                                        ->where('channels.channel_id', '=', $array_channel[$x])                    
                                        ->where('is_duplicated', '=', '0')
                                        ->whereBetween('forms.created_at_forms', [$StartDateTime, $EndDateTime])
                                        ->count();

                    $uniqueLeadsPerDay += $count_forms;
                }
            }
            
            $uniqueLeadsTotal += $uniqueLeadsPerDay;
            
            $response[$day]['uniqueLeadsPerDay'] = "$uniqueLeadsPerDay";
            $response[$day]['uniqueLeadsTotal'] = "$uniqueLeadsTotal";
            
            for($x=0;$x<$key;$x++)
            {
                $calls_perdays = DB::table('calls')
                                        ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')                            
                                        ->where('channels.channel_id', '=', $array_channel[$x])
                                        ->where('is_duplicated', '=', '0')
                                        ->whereBetween('calls.date', [$StartDateTime, $EndDateTime])
                                        ->select('calls.id')
                                        ->get();

                foreach($calls_perdays as $calls_perday)
                {
                    $comment_calls_per_day = DB::table('comment_calls')
                                                    ->where('call_id', '=', $calls_perday->id)
                                                    ->whereBetween('created_at', [$StartDateTime, $EndDateTime])
                                                    ->select('statusId')
                                                    ->orderBy('id', 'Desc')
                                                    ->first();
                    
                    if($comment_calls_per_day == ""){                        
                        if(!isset($response[$day]['statusPerDay']["default"]))
                        {
                            $response[$day]['statusPerDay']["default"] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusPerDay']["default"]++;
                        }
                    }
                    else
                    {
                        if(!isset($response[$day]['statusPerDay']["$comment_calls_per_day->statusId"]))
                        {
                            $response[$day]['statusPerDay']["$comment_calls_per_day->statusId"] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusPerDay']["$comment_calls_per_day->statusId"]++;
                        }
                    }
                }
            }

            for($x=0;$x<$key;$x++)
            {
                $calls_totals = DB::table('calls')
                                        ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')                            
                                        ->where('channels.channel_id', '=', $array_channel[$x])
                                        ->where('is_duplicated', '=', '0')
                                        ->whereBetween('calls.date', [$StartDateTime_true, $EndDateTime])
                                        ->select('calls.id')
                                        ->get();

                foreach($calls_totals as $calls_total)
                {
                    $comment_calls_total = DB::table('comment_calls')
                                                    ->where('call_id', '=', $calls_total->id)
                                                    ->whereBetween('created_at', [$StartDateTime_true, $EndDateTime])
                                                    ->select('statusId')
                                                    ->orderBy('id', 'Desc')
                                                    ->first();
                                                    
                    if($comment_calls_total == ""){                        
                        if(!isset($response[$day]['statusTotal']["default"]))
                        {
                            $response[$day]['statusTotal']["default"] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusTotal']["default"]++;
                        }
                    }
                    else
                    {
                        if(!isset($response[$day]['statusTotal'][$comment_calls_total->statusId]))
                        {
                            $response[$day]['statusTotal'][$comment_calls_total->statusId] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusTotal'][$comment_calls_total->statusId]++;
                        }
                    }
                }
            }

            for($x=0;$x<$key;$x++)
            {                                        
                $forms_perdays = DB::table('forms')
                                        ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                                        ->where('channels.channel_id', '=', $array_channel[$x])                    
                                        ->where('is_duplicated', '=', '0')
                                        ->whereBetween('forms.created_at_forms', [$StartDateTime, $EndDateTime])                            
                                        ->select('forms.id')
                                        ->get();
                                        
                foreach($forms_perdays as $forms_perday)
                {
                    $comment_forms_per_day = DB::table('comment_forms')
                                                    ->where('form_id', '=', $forms_perday->id)
                                                    ->whereBetween('created_at', [$StartDateTime, $EndDateTime])
                                                    ->select('statusId')
                                                    ->orderBy('id', 'Desc')
                                                    ->first();
                                                    
                    if($comment_forms_per_day == ""){                        
                        if(!isset($response[$day]['statusPerDay']["default"]))
                        {
                            $response[$day]['statusPerDay']["default"] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusPerDay']["default"]++;
                        }
                    }        
                    else
                    {
                        if(!isset($response[$day]['statusPerDay'][$comment_forms_per_day->statusId]))
                        {
                            $response[$day]['statusPerDay'][$comment_forms_per_day->statusId] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusPerDay'][$comment_forms_per_day->statusId]++;
                        }
                    }                                            
                }
            }

            for($x=0;$x<$key;$x++)
            {
                $forms_totals = DB::table('forms')
                                        ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                                        ->where('channels.channel_id', '=', $array_channel[$x])                    
                                        ->where('is_duplicated', '=', '0')
                                        ->whereBetween('forms.created_at_forms', [$StartDateTime_true, $EndDateTime])                                        
                                        ->select('forms.id')
                                        ->get();
                                            
                foreach($forms_totals as $forms_total)
                {
                    $comment_forms_total = DB::table('comment_forms')
                                                    ->where('form_id', '=', $forms_total->id)
                                                    ->whereBetween('created_at', [$StartDateTime_true, $EndDateTime])
                                                    ->select('statusId')
                                                    ->orderBy('id', 'Desc')
                                                    ->first();
                                
                    if($comment_forms_total == ""){                        
                        if(!isset($response[$day]['statusTotal']["default"]))
                        {
                            $response[$day]['statusTotal']["default"] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusTotal']["default"]++;
                        }
                    }        
                    else
                    {
                        if(!isset($response[$day]['statusTotal'][$comment_forms_total->statusId]))
                        {
                            $response[$day]['statusTotal'][$comment_forms_total->statusId] = 1;
                        }                                 
                        else
                        {
                            $response[$day]['statusTotal'][$comment_forms_total->statusId]++;
                        }
                    }                                                
                }
            }

            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                                        
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
                            
            $response[$day]['startDateTime'] = "$Start_DateTime";
            $response[$day]['EndDateTime'] = "$End_DateTime";
        }          
        return $response;
    }
}