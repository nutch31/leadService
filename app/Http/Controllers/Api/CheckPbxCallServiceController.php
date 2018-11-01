<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Call;
use App\Model\Channel;

class CheckPbxCallServiceController extends BaseController
{    
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function CheckPbxCallService(Request $request)
    {
        $date           = $request->get('timestamp');
        if(!isset($date))
        {
            $date = "";
        }
        $heronumber     = $request->get('heronumber'); 
        if(!isset($heronumber))
        {
            $heronumber = "";
            $campaign_id = $request->get('campaign_id');
            $campaign_name = $request->get('campaign_name');
        }
        else
        {            
            $channel = Channel::where('tracking_phone', '=', $heronumber)->select('channel_id', 'name')->orderBy('id', 'desc')->first();
            $campaign_id = $channel->channel_id;
            $campaign_name = $channel->name;
        }
        
        $client_number  = $request->get('client_number');
        if(!isset($client_number))
        {
            $client_number = "";
        }      
        $phone          = $request->get('caller_id');  
        if(!isset($phone))
        {
            $phone = "";
        }      
        $status_text    = $request->get('status');  
        if(!isset($status_text))
        {
            $status_text = "";
        }      
        if($status_text == "ANSWER" || $status_text == "ANSWERED")
        {
            $status = 1;
        }
        else
        {
            $status = 2;
        }
        $duration       = $request->get('duration');  
        if(!isset($duration))
        {
            $duration = "";
        }      
        $recording_url  = $request->get('recording_url');
        if(!isset($recording_url))
        {
            $recording_url = "";
        }      
        $location       = $request->get('location');  
        if(!isset($location))
        {
            $location = "";
        }      
        $call_uuid      = $request->get('call_uuid');  
        if(!isset($call_uuid))
        {
            $call_uuid = "";
        }      
        $call_mapped    = $request->get('call_mapped');     
        if(!isset($call_mapped))
        {
            $call_mapped = "";
        }    

        $CampaignId = Channel::where('channel_id', '=', $campaign_id)->select('campaign_id')->first();

        $ChannelIds = Channel::where('campaign_id', '=', $CampaignId->campaign_id)->select('channel_id')->get(); 
        $array_channels = [];
        foreach($ChannelIds as $ChannelIdKey => $ChannelId)
        {
            $array_channels[$ChannelIdKey] = $ChannelId->channel_id;
        }
        
        $parent_id_duplicated = "";
        
        $count = Call::whereIn('channel_id', $array_channels)->where('phone', '=', $phone)->where('is_duplicated', '=', '0')->count();
        if($count == 0)
        {
            $is_duplicated = false;
        }
        else
        {
            $is_duplicated = true;

            $parent_id_duplicated = Call::whereIn('channel_id', $array_channels)->where('phone', '=', $phone)->where('is_duplicated', '=', '0')->select('id')->first();

            if($parent_id_duplicated)            
            {
                $parent_id_duplicated = $parent_id_duplicated->id;
            }
        }        
                
        $timeArr = array_reverse(explode(":", $duration));
        $seconds = 0;
        foreach ($timeArr as $key => $value)
        {
            if ($key > 2) break;
            $seconds += pow(60, $key) * $value;
        }                               
        
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $date);
        $date_leadservice = $dt->format(DateTime::ISO8601);   
        $date_leadservice = str_replace("+0000", "+0700", $date_leadservice);
        
        $date_leadservice = Carbon::parse($date_leadservice);
        $date_leadservice->setTimezone($this->timezone);

        $response = array(
            'date_leadservice' => $date_leadservice,
            'seconds' => $seconds,
            'recording_url' => $recording_url,
            'status' => $status,
            'phone' => $phone,
            'heronumber' => $heronumber,
            'campaign_id' => $campaign_id,
            'is_duplicated' => $is_duplicated,
            'parent_id_duplicated' => $parent_id_duplicated,
            'location' => $location,
            'created_at_calls' => date("Y-m-d H:i:s"),
            'updated_at_calls' => date("Y-m-d H:i:s"),
            'client_number' => $client_number,
            'call_uuid' => $call_uuid,
            'call_mapped' => $call_mapped,
        );

        return response($response, '200');
    }   
}