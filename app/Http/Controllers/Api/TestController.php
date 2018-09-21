<?php

namespace App\Http\Controllers\Api;

use App\Model\Test;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DB;

class TestController extends BaseController
{
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