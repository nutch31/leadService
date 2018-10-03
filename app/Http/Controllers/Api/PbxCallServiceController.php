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

        $count = Call::where([
            ['channel_id', '=', $campaign_id],
            ['phone', '=', $phone]
        ])->count();

        if($count == 0)
        {
            $is_duplicated = false;
        }
        else
        {
            $is_duplicated = true;
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
            
            //$this->call_alpha($date, $heronumber, $client_number, $phone, $status_text, $duration, $recording_url, $Pbxcallservice->id, $call->id);
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

    public function call_alpha($date, $heronumber, $client_number, $phone, $status_text, $duration, $recording_url, $Pbxcallservice_id, $call_id)
    {          
        $arr = array(
                     'timestamp' => $date, 
                     'heronumber' => $heronumber, 
                     'client_number' => $client_number, 
                     'caller_id' => $phone, 
                     'status' => $status_text, 
                     'duration' => $duration, 
                     'recording_url' => $recording_url,
                     'call_id' => $call_id 
                    );
        $val = json_encode($arr);

        //$url = '';   // comment for using the url from database
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
            $Pbxcallservice->status_alpha = 1;
            $Pbxcallservice->save();
        }
    }
}
