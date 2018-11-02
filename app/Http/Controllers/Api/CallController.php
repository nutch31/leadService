<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $this->limit = 99999999;
    }

    public function getCalls(Request $request)
    {
        if(isset($request->page) && !isset($request->limit) || !isset($request->page) && isset($request->limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }

        if(!isset($request->limit))
        {
            $request->limit = $this->limit;
        }
        
        $response = $this->Reponse_getCalls($request); 
        return response($response, '200');
    }

    public function getCalls_DidPhone(Request $request)
    {
        if(isset($request->page) && !isset($request->limit) || !isset($request->page) && isset($request->limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }

        if(!isset($request->limit))
        {
            $request->limit = $this->limit;
        }

        $response = $this->Reponse_getCalls($request);     
        return response($response, '200');
    }

    public function getCalls_DidPhone_CallerPhone(Request $request)
    {        
        if(isset($request->page) && !isset($request->limit) || !isset($request->page) && isset($request->limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }

        if(!isset($request->limit))
        {
            $request->limit = $this->limit;
        }

        $response = $this->Reponse_getCalls($request);      
        return response($response, '200');
    }

    public function getCalls_DidPhone_getStartEndDate(Request $request)
    {           
        $response = array();

        $calls_minDate = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->where('channels.tracking_phone', '=', $request->DidPhone);
        if(isset($request->status))
        {
            $calls_minDate = $calls_minDate->where('calls.status', '=', $request->status);
        }
        $calls_minDate = $calls_minDate->min('calls.date');

        $calls_maxDate = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->where('channels.tracking_phone', '=', $request->DidPhone);
        if(isset($request->status))
        {
            $calls_maxDate = $calls_maxDate->where('calls.status', '=', $request->status);
         }
        $calls_maxDate = $calls_maxDate->max('calls.date');
        
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
        $request->limit = $this->limit;

        $request->SubmitDateTime = Carbon::parse($request->SubmitDateTime);
        $request->SubmitDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getCalls($request);    
        return response($response, '200');
    }

    public function getCalls_DidPhone_StartDate_EndDate(Request $request)
    {
        if(isset($request->page) && !isset($request->limit) || !isset($request->page) && isset($request->limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }

        if(!isset($request->limit))
        {
            $request->limit = $this->limit;
        }

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

    public function getCalls_DidPhone_StartDate_EndDate_Count_Daybyday(Request $request)
    {
        $array_StartDate = explode("+", $request->StartDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->EndDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->StartDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $request->count = CarbonPeriod::create($request->StartDateTime, $request->EndDateTime);
        $count = $request->count->count();

        $response = $this->Reponse_getCalls_Count_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }
    
    public function getCalls_DidPhone_MonthYear_Count_Daybyday(Request $request)
    {
        $timezone_StartDate = $request->TimeZone;
        $timezone_EndDate = $request->TimeZone;

        $StartDateTime = $request->Year."-".$request->Month."-01T00:00:00+".$timezone_StartDate;
        $request->StartDateTime = $StartDateTime;

        $dt = Carbon::parse($StartDateTime);
        $count = $dt->daysInMonth;
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $EndDateTime = $request->Year."-".$request->Month."-".$count."T23:59:59+".$timezone_StartDate;
        $request->EndDateTime = $EndDateTime;

        $dt = Carbon::parse($EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $response = $this->Reponse_getCalls_Count_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getCalls_DidPhone_StartDate_EndDate_Unique(Request $request)
    {
        $request->StartDateTime = Carbon::parse($request->StartDateTime);
        $request->StartDateTime->setTimezone($this->timezone);

        $request->EndDateTime = Carbon::parse($request->EndDateTime);
        $request->EndDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getCalls_Unique($request);    
        return response($response, '200');
    }

    public function getCalls_DidPhone_StartDate_EndDate_Unique_Daybyday(Request $request)
    {
        $array_StartDate = explode("+", $request->StartDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->EndDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->StartDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $request->count = CarbonPeriod::create($request->StartDateTime, $request->EndDateTime);
        $count = $request->count->count();
        
        $response = $this->Reponse_getCalls_Unique_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getCalls_DidPhone_MonthYear_Unique_Daybyday(Request $request)
    {
        $timezone_StartDate = $request->TimeZone;
        $timezone_EndDate = $request->TimeZone;

        $StartDateTime = $request->Year."-".$request->Month."-01T00:00:00+".$timezone_StartDate;
        $request->StartDateTime = $StartDateTime;

        $dt = Carbon::parse($StartDateTime);
        $count = $dt->daysInMonth;
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $EndDateTime = $request->Year."-".$request->Month."-".$count."T23:59:59+".$timezone_StartDate;
        $request->EndDateTime = $EndDateTime;

        $dt = Carbon::parse($EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $response = $this->Reponse_getCalls_Unique_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
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
        
        if(isset($request->status))
        {
            $calls = $calls->where('calls.status', '=', $request->status);
        }

        $calls = $calls->orderBy('calls.date', 'asc')
                        ->paginate($request->limit);
                        
        $response['paging']['count'] = $calls->count();
        $response['paging']['currentPage'] = $calls->currentPage();
        $response['paging']['firstItem'] = $calls->firstItem();
        $response['paging']['hasMorePages'] = $calls->hasMorePages();
        $response['paging']['lastItem'] = $calls->lastItem();
        $response['paging']['lastPage'] = $calls->lastPage();
                
        if(!is_null($calls->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $calls->nextPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $calls->nextPageUrl();
        }
                
        $response['paging']['onFirstPage'] = $calls->onFirstPage();
        //$response['paging']['perPage'] = $calls->perPage();
                
        if(!is_null($calls->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $calls->previousPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $calls->previousPageUrl();
        }
                
        $response['paging']['total'] = $calls->total();

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
            $response['content'][$callKey]['links'][0]['href'] = "http://leadservice.heroleads.co.th/leadService/public/index.php/getCalls/".$call->tracking_phone."/".$call->phone."/".$submitDateTime;
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
                    ->whereBetween('calls.date', [$request->StartDateTime, $request->EndDateTime]);
        if(isset($request->status))
        {
            $count = $count->where('calls.status', '=', $request->status);
        }
        $count = $count->count();    
                    
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

    public function Reponse_getCalls_Count_Daybyday(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {
        $response = array();

        $last_day = $count-1;
        
        for($day=0;$day<$count;$day++)
        {            
            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;

                $array = explode("T",$request->StartDateTime);
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
            
            $count_calls = DB::table('calls')
            ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
            ->where('channels.tracking_phone', '=', $request->DidPhone)
            ->whereBetween('calls.date', [$StartDateTime, $EndDateTime]);
            if(isset($request->status))
            {
                $count_calls = $count_calls->where('calls.status', '=', $request->status);
            }
            $count_calls = $count_calls->count();
                    
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
    
            $response[$day]['fromDateTime'] = "$Start_DateTime";
            $response[$day]['totalCalls'] = "$count_calls";
            $response[$day]['heroNumber'] = "$request->DidPhone";
            $response[$day]['toDateTime'] = "$End_DateTime";
        }

        //$response = json_encode($response);
        return $response;
    }

    public function Reponse_getCalls_Unique(Request $request)
    {
        $response = array();

        $count = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->where('channels.tracking_phone', '=', $request->DidPhone)
                    ->where('is_duplicated', '=', '0')
                    ->whereBetween('calls.date', [$request->StartDateTime, $request->EndDateTime]);
        if(isset($request->status))
        {
            $count = $count->where('calls.status', '=', $request->status);
        }
        $count = $count->count();    
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $request->StartDateTime);
        //$dt->setTimezone($this->timezone);
        $StartDateTime = $dt->format(DateTime::ISO8601);   
            
        $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->EndDateTime);
        //$dt2->setTimezone($this->timezone);
        $EndDateTime = $dt2->format(DateTime::ISO8601);   

        $response['fromDateTime'] = "$StartDateTime";
        $response['totalUniqueCalls'] = "$count";
        $response['heroNumber'] = "$request->DidPhone";
        $response['toDateTime'] = "$EndDateTime";
        
        //$response = json_encode($response);
        return $response;
    }

    public function Reponse_getCalls_Unique_Daybyday(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {
        $response = array();

        $last_day = $count-1;
        
        for($day=0;$day<$count;$day++)
        {            
            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;

                $array = explode("T",$request->StartDateTime);
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
            
            $count_calls = DB::table('calls')
            ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
            ->where('channels.tracking_phone', '=', $request->DidPhone)
            ->where('is_duplicated', '=', '0')
            ->whereBetween('calls.date', [$StartDateTime, $EndDateTime]);
            if(isset($request->status))
            {
                $count_calls = $count_calls->where('calls.status', '=', $request->status);
            }
            $count_calls = $count_calls->count();
                    
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
    
            $response[$day]['fromDateTime'] = "$Start_DateTime";
            $response[$day]['totalUniqueCalls'] = "$count_calls";
            $response[$day]['heroNumber'] = "$request->DidPhone";
            $response[$day]['toDateTime'] = "$End_DateTime";
        }

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
    
    public function UpdateParentIdDuplicatedCalls(Request $request)
    {
        $calls = DB::table('calls')
                    ->select('id','channel_id','phone')
                    ->orderBy('calls.id', 'asc')
                    ->paginate($request->limit);
                        
        $response['paging']['count'] = $calls->count();
        $response['paging']['currentPage'] = $calls->currentPage();
        $response['paging']['firstItem'] = $calls->firstItem();
        $response['paging']['hasMorePages'] = $calls->hasMorePages();
        $response['paging']['lastItem'] = $calls->lastItem();
        $response['paging']['lastPage'] = $calls->lastPage();
                
        if(!is_null($calls->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $calls->nextPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $calls->nextPageUrl();
        }
                
        $response['paging']['onFirstPage'] = $calls->onFirstPage();
        //$response['paging']['perPage'] = $calls->perPage();
                
        if(!is_null($calls->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $calls->previousPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $calls->previousPageUrl();
        }
                
        $response['paging']['total'] = $calls->total();

        foreach($calls as $callKey => $call)
        {   

            $CampaignId = Channel::where('channel_id', '=', $call->channel_id)->select('campaign_id')->first();

            $ChannelIds = Channel::where('campaign_id', '=', $CampaignId->campaign_id)->select('channel_id')->get(); 
            $array_channels = [];
            foreach($ChannelIds as $ChannelIdKey => $ChannelId)
            {
                $array_channels[$ChannelIdKey] = $ChannelId->channel_id;
            }
                    
            $count = Call::whereIn('channel_id', $array_channels)->where('id', '!=', $call->id)->where('id', '<', $call->id)->where('phone', '=', $call->phone)->count();

            if($count > 0)
            {
                $parent_id_duplicated = Call::whereIn('channel_id', $array_channels)->where('id', '!=', $call->id)->where('id', '<', $call->id)->where('phone', '=', $call->phone)->orderBy('id', 'asc')->select('id')->first();
                                     
                //$call_update = Call::find($call->id);
                //$call_update->is_duplicated = 1;
                //$call_update->parent_id_duplicated = $parent_id_duplicated->id;
                //$call_update->save();                
            }
            else
            {
                //$call_update = Call::find($call->id);
                //$call_update->is_duplicated = 0;
                //$call_update->parent_id_duplicated = '';
                //$call_update->save();
            } 
                                        
            $response['content'][$callKey]['PbxpageCallEvent']['rowId'] = "$call->id";
            $response['content'][$callKey]['PbxpageCallEvent']['is_duplicated'] = "$call_update->is_duplicated";
            $response['content'][$callKey]['PbxpageCallEvent']['parent_id_duplicated'] = "$call_update->parent_id_duplicated";
        }        
        return $response;
    }
}