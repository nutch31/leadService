<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Channel;
use App\Model\Call;

class CallController extends BaseController
{
    public function __construct()
    {
        $this->timezone = 'Asia/Bangkok';
    }

    public function getCalls()
    {
        $response = array();

        $channels = Channel::with('calls')->where('tracking_phone', '!=', '')->orderBy('id', 'asc')->get();

        foreach($channels as $channelKey => $channel)
        {
            foreach($channel->calls as $callKey => $call)
            {                                
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
                $dt->setTimezone('GMT');
                $heroNusubmitDateTime = $dt->format(DateTime::ISO8601);   

                $response['content'][$callKey]['pbxcallEvent']['rowId'] = "$call->id";
                $response['content'][$callKey]['pbxcallEvent']['duration'] = "$call->duration";
                $response['content'][$callKey]['pbxcallEvent']['status'] = "$call->status";
                $response['content'][$callKey]['pbxcallEvent']['recordingUrl'] = "$call->recording_url";
                $response['content'][$callKey]['pbxcallEvent']['heroNumber'] = "$channel->tracking_phone";
                $response['content'][$callKey]['pbxcallEvent']['callerId'] = "$call->phone";
                $response['content'][$callKey]['pbxcallEvent']['heroNusubmitDateTime'] = "$heroNusubmitDateTime";

                
                $response['content'][$callKey]['links'][0]['rel'] = "Null";
                $response['content'][$callKey]['links'][0]['href'] = "";
                $response['content'][$callKey]['links'][0]['hreflang'] = "";
                $response['content'][$callKey]['links'][0]['media'] = "";
                $response['content'][$callKey]['links'][0]['title'] = "";
                $response['content'][$callKey]['links'][0]['type'] = "";
                $response['content'][$callKey]['links'][0]['deprecation'] = "";
            }
        }

        $response = json_encode($response, true);

        return response($response, '200');
    }

    public function postCall(Request $request)
    {
        $this->validate($request, [
            'call_id' => 'required',
            'channel_id' => 'required'
        ]);

        $count = Call::where('call_id', '=', $request->call_id)->count();

        if($count == 0)
        {
            $call = Call::create([
                'call_id' => $request->call_id, 
                'date' => $request->date, 
                'duration' => $request->duration, 
                'recording_url' => $request->recording_url,
                'status' => $request->status, 
                'phone' => $request->phone, 
                'channel_id' => $request->channel_id, 
                'is_duplicated' => $request->is_duplicated,
                'location' => $request->location, 
                'created_at_calls' => $request->created_at, 
                'updated_at_calls' => $request->updated_at, 
                'client_number' => $request->client_number, 
                'call_uuid' => $request->call_uuid, 
                'call_mapped' => $request->call_mapped
            ]);

            return response($call, '201');
        }
        else
        {
            return response()->json('Call ID : '.$request->call_id. ' Duplicated', '409');
        }
    }
}