<?php

namespace App\Http\Controllers\Api;

use App\Model\Test;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use DB;

class TestController extends BaseController
{
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
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

}