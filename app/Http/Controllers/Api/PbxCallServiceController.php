<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Pbxcallservice;
use App\Model\Call;
use App\Model\Channel;

class PbxCallServiceController extends BaseController
{    
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function PbxCallService(Request $request)
    { 
        $Pbxcallservice = new Pbxcallservice;
        $Pbxcallservice->response = $request;
        $Pbxcallservice->status = 0;
        $Pbxcallservice->status_pbx = 0;
        $Pbxcallservice->status_alpha = 0;
        $Pbxcallservice->call_id_leadservice = 0;
        $Pbxcallservice->save();
                            
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
        
        //$this->call_herobase($date, $heronumber, $client_number, $phone, $status_text, $duration, $recording_url, $Pbxcallservice->id);

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

        $call = Call::create([
            'call_id' => 0, 
            'date' => $date_leadservice, 
            'duration' => $seconds, 
            'recording_url' => $recording_url,
            'status' => $status, 
            'phone' => $phone, 
            'channel_id' => $campaign_id, 
            'is_duplicated' => $is_duplicated,
            'parent_id_duplicated' => $parent_id_duplicated,
            'location' => $location, 
            'created_at_calls' => date("Y-m-d H:i:s"), 
            'updated_at_calls' => date("Y-m-d H:i:s"), 
            'client_number' => $client_number, 
            'call_uuid' => $call_uuid, 
            'call_mapped' => $call_mapped
        ]);
        
        if(isset($call->id))
        {
            $Pbxcallservice = Pbxcallservice::find($Pbxcallservice->id);
            $Pbxcallservice->status = 1;
            $Pbxcallservice->call_id_leadservice = $call->id;
            $Pbxcallservice->save();
            
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $date_leadservice);
            $dt->setTimezone($this->timezone);
            $submitted_date_time = $dt->format(DateTime::ISO8601);   
            
            $this->call_alpha($campaign_id, $submitted_date_time, $phone, $status, "Incoming", $recording_url, $Pbxcallservice->id, $call->id, $is_duplicated, $parent_id_duplicated, $seconds, $heronumber);
        }    
    }

    public function call_herobase($date, $heronumber, $client_number, $phone, $status_text, $duration, $recording_url, $Pbxcallservice_id)
    {          
        $arr = array(
                     'timestamp' => $date, 
                     'heronumber' => $heronumber, 
                     'client_number' => $client_number, 
                     'caller_id' => $phone, 
                     'status' => $status_text, 
                     'duration' => $duration, 
                     'recording_url' => $recording_url 
                    );
        $val = json_encode($arr);

        $url = 'https://clients.heroleads.com/api/calls?token=thioch4eimovoiDu6ahd';   // comment for using the url from database
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
            
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $val);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($val))
        );     
        $response = curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);

        if($info==200 || $info==201)
        {
            $Pbxcallservice = Pbxcallservice::find($Pbxcallservice_id);
            $Pbxcallservice->status_pbx = 1;
            $Pbxcallservice->save();
        }
    }

    
    public function call_alpha($channel_id, $submitted_date_time, $caller_phone_number, $status, $call_direction, $recording_url, $Pbxcallservice_id, $call_id, $is_duplicated, $parent_id_duplicated, $duration, $tracking_phone)
    {   
        if($status == 1)
        {
            $text = "ANSWER";
        }
        else 
        {
            $text = "MISSED CALL";
        }       

        $arr = array(
                        'type' => 'phone',
                        'data' => [
                            '_id' => $call_id,
                            'channel_id' => $channel_id,
                            'submitted_date_time' => $submitted_date_time,
                            'caller_phone_number' => $caller_phone_number,
                            'status' => $text,
                            'call_direction' => $call_direction,
                            'recording_url' => $recording_url,
                            'is_duplicated' => $is_duplicated,
                            'parent_id_duplicated' => $parent_id_duplicated,
                            'duration' => $duration,
                            'did_phone' => $tracking_phone
                        ]
                    );
        $val = json_encode($arr);
        
        $url = env("ALPHA_API");
        $url .= "push-leads-data";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
            
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $val);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($val))
        );     
        $response = curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);

        if(!is_null($Pbxcallservice_id))
        {
            if($info == "200" || $info == "201")
            {
                $Pbxcallservice = Pbxcallservice::find($Pbxcallservice_id);
                $Pbxcallservice->status_alpha = 1;
                $Pbxcallservice->save();
            }
        }
    }
    

    
    public function PullLeadsCalls(Request $request)
    {   
        $channel = Channel::where('tracking_phone', '=', $request->DidPhone)->first();
        
        $calls = Call::where('channel_id', '=', $channel->channel_id);
        if(isset($request->StartDateTime) && isset($request->EndDateTime))
        {                
            $request->StartDateTime = Carbon::parse($request->StartDateTime);
            $request->StartDateTime->setTimezone($this->timezone);

            $request->EndDateTime = Carbon::parse($request->EndDateTime);
            $request->EndDateTime->setTimezone($this->timezone);
            
            $calls = $calls->whereBetween('calls.date', [$request->StartDateTime, $request->EndDateTime]);
        }
        $calls = $calls->orderBy('calls.date', 'asc')->get();

        foreach($calls as $call)
        {
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
            $dt->setTimezone($this->timezone);
            $submitted_date_time = $dt->format(DateTime::ISO8601);

            $this->call_alpha($call->channel_id, $submitted_date_time, $call->phone, $call->status, "Incoming", $call->recording_url, Null, $call->id, $call->is_duplicated, $call->parent_id_duplicated, $call->duration, $request->DidPhone);
        }
        
        return response(array(
            'Status' => 'Success'
        ), '200');
    }
    
}
