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

        if(isset($data["_id"]))
        {
            $_id = $data["_id"];
        }
        else
        {
            $_id = "";
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
        
        if(isset($data["remark"]["_id"]))
        {
            $remark_id = $data["remark"]["_id"];
        }
        else
        {
            $remark_id = "";
        }
        if(isset($data["remark"]["value"]))
        {
            $remark_value = $data["remark"]["value"];
        }
        else
        {
            $remark_value = "";
        }
        if(isset($data["remark"]["type_of_action"]))
        {
            $remark_type_of_action = $data["remark"]["type_of_action"];
        }
        else
        {
            $remark_type_of_action = "";
        }
        if(isset($data["source"]["_id"]))
        {
            $source_id = $data["source"]["_id"];
        }
        else
        {
            $source_id = "";
        }
        if(isset($data["source"]["value"]))
        {
            $source_value = $data["source"]["value"];
        }
        else
        {
            $source_value = "";
        }
        if(isset($data["source"]["type_of_action"]))
        {
            $source_type_of_action = $data["source"]["type_of_action"];
        }
        else
        {
            $source_type_of_action = "";
        }
        if(isset($data["status"]["_id"]))
        {
            $status_id = $data["status"]["_id"];
        }
        else
        {
            $status_id = "";
        }
        if(isset($data["status"]["value"]))
        {
            $status_value = $data["status"]["value"];
        }
        else
        {
            $status_value = "";
        }
        if(isset($data["status"]["type_of_action"]))
        {
            $status_type_of_action = $data["status"]["type_of_action"];
        }
        else
        {
            $status_type_of_action = "";
        }
        
        if($type == "phone")
        {                                        
            $comment_call = Comment_call::create([
                'call_id' => $_id, 
                'first_name' => $firstName, 
                'last_name' => $lastName, 
                'remark_id' => $remark_id,
                'remark_value' => $remark_value, 
                'remark_type_of_action' => $remark_type_of_action, 
                'source_id' => $source_id, 
                'source_value' => $source_value,
                'source_type_of_action' => $source_type_of_action,  
                'status_id' => $status_id, 
                'status_value' => $status_value, 
                'status_type_of_action' => $status_type_of_action,
                'data' => $request
            ]);

            return response($comment_call, '200');  
        }
        else if($type == "submitted")
        {                        
            $comment_form = Comment_form::create([
                'form_id' => $_id, 
                'remark_id' => $remark_id,
                'remark_value' => $remark_value, 
                'remark_type_of_action' => $remark_type_of_action, 
                'source_id' => $source_id, 
                'source_value' => $source_value,
                'source_type_of_action' => $source_type_of_action,  
                'status_id' => $status_id, 
                'status_value' => $status_value, 
                'status_type_of_action' => $status_type_of_action,
                'data' => $request
            ]);

            return response($comment_form, '200'); 
        }

        return response("{'message' : 'parameter type : '.$type.' not found.'}", '400'); 
    }
}
