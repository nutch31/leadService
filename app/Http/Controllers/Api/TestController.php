<?php

namespace App\Http\Controllers\Api;

use App\Model\Test;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use DB;
use App\Model\Channel;
use App\Model\Call;

class TestController extends BaseController
{
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->limit = 99999999;
    }

    public function test()
    {
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', Date("Y-m-d H:i:s"));
        $dt->setTimezone($this->timezone);
        $submitted_date_time = $dt->format(DateTime::ISO8601); 

        $arr = array(
            'type' => 'submitting',
            'data' => [
                'submitted_date_time' => $submitted_date_time
            ]
           );
        $val = json_encode($arr);

        print_r($val);
    }

    public function index()
    {
        return 'Index TestController';
    }

    public function getItem()
    {
        $item = Test::all();
        
        return response($item, '200');
    }

    public function postItem(Request $request)
    {
        $this->validate($request, [
            'product' => 'required',
            'age' => 'required'
        ]);

        $item = Test::create($request->all());

        return response($item, '201');
    }

    public function putItem(Request $request, $id)
    {
        $item = Test::find($id);
        $item->update($request->all());

        return response($item, '200');
    }

    public function deleteItem($id)
    {
        try {
            $item = Test::find($id);
            $item->delete();
            return response()->json('success', '200');
        } catch (\Exception $e) {
            return "false";
        }
    }

    public function pagination(Request $request)
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

        $response = array();

        $calls = DB::table('calls')
                    ->join('channels', 'channels.channel_id', '=', 'calls.channel_id')
                    ->select(
                        'channels.tracking_phone', 
                        'calls.id', 'calls.duration', 'calls.status', 'calls.recording_url', 'calls.phone', 'calls.date'
                    );
        
        
        $calls = $calls->where('channels.tracking_phone', '=', $request->DidPhone);
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

}