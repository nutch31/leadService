<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Channel;
use App\Model\Call;
use DB;

class CallController extends BaseController
{
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function getCalls(Request $request)
    {
        $response = $this->Reponse_getCalls($request); 
        return response($response, '200');
    }

    public function getCalls_DidPhone(Request $request)
    {
        $response = $this->Reponse_getCalls($request);     
        return response($response, '200');
    }

    public function getCalls_DidPhone_CallerPhone(Request $request)
    {        
        $response = $this->Reponse_getCalls($request);      
        return response($response, '200');
    }

    public function getCalls_DidPhone_getStartEndDate(Request $request)
    {           
        $response = array();

        $calls_minDate = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->where('channels.tracking_phone', '=', $request->DidPhone)
                    ->min('calls.date');

        $calls_maxDate = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->where('channels.tracking_phone', '=', $request->DidPhone)
                    ->max('calls.date');
        
        if(is_null($calls_minDate) || is_null($calls_maxDate))
        {   
            return response('{"response":"Not Have Leads"}', '200');
        }
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $calls_minDate);
        //$dt->setTimezone($this->timezone);
        $startDate = $dt->format(DateTime::ISO8601);   
                            
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $calls_maxDate);
        //$dt->setTimezone($this->timezone);
        $endDate = $dt->format(DateTime::ISO8601);  

        $response['startDate'] = "$startDate";
        $response['endDate'] = "$endDate";
                
        //$response = json_encode($response);       
        return response($response, '200');
    }

    public function getCalls_DidPhone_CallerPhone_SubmitDateTime(Request $request)
    {   
        $request->SubmitDateTime = Carbon::parse($request->SubmitDateTime);
        $request->SubmitDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getCalls($request);    
        return response($response, '200');
    }

    public function getCalls_DidPhone_StartDate_EndDate(Request $request)
    {
        $request->StartDateTime = Carbon::parse($request->StartDateTime);
        $request->StartDateTime->setTimezone($this->timezone);

        $request->EndDateTime = Carbon::parse($request->EndDateTime);
        $request->EndDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getCalls($request);       
        return response($response, '200');
    }

    public function getCalls_DidPhone_StartDate_EndDate_Count(Request $request)
    {
        $request->StartDateTime = Carbon::parse($request->StartDateTime);
        $request->StartDateTime->setTimezone($this->timezone);

        $request->EndDateTime = Carbon::parse($request->EndDateTime);
        $request->EndDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getCalls_Count($request);    
        return response($response, '200');
    }

    public function Reponse_getCalls(Request $request)
    {        
        $response = array();

        $calls = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->select(
                        'channels.tracking_phone', 
                        'calls.id', 'calls.duration', 'calls.status', 'calls.recording_url', 'calls.phone', 'calls.date'
                    );
        
        if(isset($request->CallerPhone))
        {
            $calls = $calls->where('calls.phone', '=', $request->CallerPhone);
        }
        
        if(isset($request->DidPhone))
        {
            $calls = $calls->where('channels.tracking_phone', '=', $request->DidPhone);
        }
        else
        {
            $calls = $calls->where('channels.tracking_phone', '!=', '');
        }

        if(isset($request->SubmitDateTime))
        {
            $calls = $calls->where('calls.date', '=', $request->SubmitDateTime);
        }

        if(isset($request->StartDateTime) && isset($request->EndDateTime))
        {
            $calls = $calls->whereBetween('calls.date', [$request->StartDateTime, $request->EndDateTime]);
        }

        $calls = $calls->orderBy('calls.date', 'asc')
                        ->get();

        foreach($calls as $callKey => $call)
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

            $response['links'] = array();
            $response['content'][$callKey]['pbxcallEvent']['rowId'] = "$call->id";
            $response['content'][$callKey]['pbxcallEvent']['duration'] = "$call->duration";
            $response['content'][$callKey]['pbxcallEvent']['status'] = "$status";
            $response['content'][$callKey]['pbxcallEvent']['recordingUrl'] = "$call->recording_url";
            $response['content'][$callKey]['pbxcallEvent']['heroNumber'] = "$call->tracking_phone";
            $response['content'][$callKey]['pbxcallEvent']['callerId'] = "$call->phone";
            $response['content'][$callKey]['pbxcallEvent']['submitDateTime'] = "$submitDateTime";

            $response['content'][$callKey]['links'][0]['rel'] = "self";
            $response['content'][$callKey]['links'][0]['href'] = "http://128.199.186.53/leadService/public/index.php/getcalls/".$call->tracking_phone."/".$call->phone."/".$submitDateTime;
            $response['content'][$callKey]['links'][0]['hreflang'] = null;
            $response['content'][$callKey]['links'][0]['media'] = null;
            $response['content'][$callKey]['links'][0]['title'] = null;
            $response['content'][$callKey]['links'][0]['type'] = null;
            $response['content'][$callKey]['links'][0]['deprecation'] = null;
        }        

        //$response = json_encode($response);
        return $response;
    }

    public function Reponse_getCalls_Count(Request $request)
    {
        $response = array();

        $count = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->where('channels.tracking_phone', '=', $request->DidPhone)
                    ->whereBetween('calls.date', [$request->StartDateTime, $request->EndDateTime])
                    ->count();    
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $request->StartDateTime);
        //$dt->setTimezone($this->timezone);
        $StartDateTime = $dt->format(DateTime::ISO8601);   
            
        $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->EndDateTime);
        //$dt2->setTimezone($this->timezone);
        $EndDateTime = $dt2->format(DateTime::ISO8601);   

        $response['fromDateTime'] = "$StartDateTime";
        $response['totalCalls'] = "$count";
        $response['heroNumber'] = "$request->DidPhone";
        $response['toDateTime'] = "$EndDateTime";
        
        //$response = json_encode($response);
        return $response;
    }

    public function postCall(Request $request)
    {
        $this->validate($request, [
            'call_id' => 'required',
            'channel_id' => 'required'
        ]);

        $count = Call::where('date', '=', $request->date)
                    ->where('channel_id', '=', $request->channel_id)
                    ->where('phone', '=', $request->phone)
                    ->count();

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