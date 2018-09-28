<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Pbxcallservice;
use App\Model\Call;

class PbxCallServiceController extends BaseController
{
    public function PbxCallService(Request $request)
    { 
        $Pbxcallservice = new Pbxcallservice;
        $Pbxcallservice->response = $request;
        $Pbxcallservice->status = 0;
        $Pbxcallservice->call_id_leadservice = 0;
        $Pbxcallservice->save();
                     
        //command for receive message PBXCall
        $content = file_get_contents('php://input');
        
        //conver json to array
        $events = json_decode($content, true);
        
        $date           = $events['date'];
        $duration       = $events['duration'];
        $recording_url  = $events['recording_url'];
        $status         = $events['status'];  
        $phone          = $events['phone'];
        $channel_id     = $events['channel_id'];
        $location       = $events['location'];
        $client_number  = $events['client_number'];
        $call_uuid      = $events['call_uuid'];
        $call_mapped    = $events['call_mapped'];
        
        $count = Call::where('channel_id', '=', $channel_id)->where('phone', '=', $phone)->count();

        if($count == 0)
        {
            $is_duplicated = false;
        }
        else
        {
            $is_duplicated = true;
        }
        
        /*
        $call = Call::create([
            'call_id' => 0, 
            'date' => $date, 
            'duration' => $duration, 
            'recording_url' => $recording_url,
            'status' => $status, 
            'phone' => $phone, 
            'channel_id' => $channel_id, 
            'is_duplicated' => $is_duplicated,
            'location' => $location, 
            'created_at_calls' => date("Y-m-d H:i:s"), 
            'updated_at_calls' => date("Y-m-d H:i:s"), 
            'client_number' => $client_number, 
            'call_uuid' => $call_uuid, 
            'call_mapped' => $call_mapped
        ]);
        */
        
        if(isset($call->id))
        {
            $Pbxcallservice = Pbxcallservice::find($Pbxcallservice->id);
            $Pbxcallservice->status = 1;
            $Pbxcallservice->call_id_leadservice = $form->id;
            $Pbxcallservice->save();
        }        
    }
}
