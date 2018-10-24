<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Comment_call;
use App\Model\Comment_form;

class PushLeadsDataFromAlphaController extends BaseController
{    
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function PushLeadsData(Request $request)
    { 
        $type = $request->get('type');
        
        $data = $request->get('data');

        if(isset($data["id"]))
        {
            $id = $data["id"];
        }
        else
        {
            $id = "";
        }
        if(isset($data["firstName"]))
        {
            $firstName = $data["firstName"];
        }
        else
        {
            $firstName = "";
        }
        if(isset($data["lastName"]))
        {
            $lastName = $data["lastName"];
        }
        else
        {
            $lastName = "";
        }
        
        if(isset($data["remark"]["id"]))
        {
            $remarkId = $data["remark"]["id"];
        }
        else
        {
            $remarkId = "";
        }
        if(isset($data["remark"]["value"]))
        {
            $remarkValue = $data["remark"]["value"];
        }
        else
        {
            $remarkValue = "";
        }
        if(isset($data["remark"]["reporterId"]))
        {
            $reporterId = $data["remark"]["reporterId"];
        }
        else
        {
            $reporterId = "";
        }
        if(isset($data["remark"]["typeOfAction"]))
        {
            $typeOfAction = $data["remark"]["typeOfAction"];
        }
        else
        {
            $typeOfAction = "";
        }
        if(isset($data["sourceId"]))
        {
            $sourceId = $data["sourceId"];
        }
        else
        {
            $sourceId = "";
        }
        if(isset($data["statusId"]))
        {
            $statusId = $data["statusId"];
        }
        else
        {
            $statusId = "";
        }        
        
        if($type == "phone")
        {                                        
            $comment_call = Comment_call::create([
                'call_id' => $id, 
                'firstName' => $firstName, 
                'lastName' => $lastName, 
                'remarkId' => $remarkId,
                'remarkValue' => $remarkValue, 
                'reporterId' => $reporterId,
                'typeOfAction' => $typeOfAction, 
                'sourceId' => $sourceId, 
                'statusId' => $statusId, 
                'data' => $request
            ]);

            return response($comment_call, '200');  
        }
        else if($type == "submitted")
        {                        
            $comment_form = Comment_form::create([
                'form_id' => $id, 
                'firstName' => $firstName, 
                'lastName' => $lastName, 
                'remarkId' => $remarkId,
                'remarkValue' => $remarkValue, 
                'reporterId' => $reporterId,
                'typeOfAction' => $typeOfAction, 
                'sourceId' => $sourceId, 
                'statusId' => $statusId, 
                'data' => $request
            ]);

            return response($comment_form, '200'); 
        }

        return response("{'message' : 'parameter type : '.$type.' not found.'}", '400'); 
    }
}
