<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Landingpagecallservice;
use App\Model\Form;

class LandingPageCallServiceController extends BaseController
{
    public function LandingPageCallService(Request $request)
    {                        
        $Landingpagecallservice = new Landingpagecallservice;
        $Landingpagecallservice->response = $request;
        $Landingpagecallservice->status = 0;
        $Landingpagecallservice->form_id_leadservice = 0;
        $Landingpagecallservice->save();
       
        $data_json = $request->get('data_json');

        if(isset($data_json) && !empty($data_json))
        {
            $data_array = json_decode($data_json, true);
                
            $channel_id     = $data_array["channel_id"][0];
            $name           = $data_array["name"][0];
            $email          = $data_array["email"][0];
            $phone_number   = $data_array["phone_number"][0];
            $ip_address     = $data_array["ip_address"][0];
            
            unset($data_array["channel_id"]);
            unset($data_array["name"]);
            unset($data_array["email"]);
            unset($data_array["phone_number"]);
            
            $data_json = json_encode($data_array);            
        }
        else
        {
            $data_array = array();

            $channel_id     = 0;
            $name           = "";
            $email          = "";
            $phone_number   = "";
            $ip_address     = "";
            
            $data_json = json_encode($data_array);  
        }

        $page_url = $request->get('page_url');            
        
        //$count = Form::where('channel_id', '=', $channel_id)->where('email', '=', $email)->where('phone', '=', $phone_number)->count();
        
        $count = Form::where('channel_id', '=', $channel_id)->where(function($query) use ($email, $phone_number)
        {
            $query->where('email', '=', $email)->orWhere('phone', '=', $phone_number);
        })->count();

        if($count == 0)
        {
            $is_duplicated = false;
        }
        else
        {
            $is_duplicated = true;
        }
        
        $form = Form::create([
            'form_id' => 0, 
            'channel_id' => $channel_id, 
            'name' => $name, 
            'email' => $email,
            'phone' => $phone_number, 
            'custom_attributes' => $data_json, 
            'is_duplicated' => $is_duplicated, 
            'ip' => $ip_address,
            'location' => '', 
            'created_at_forms' => date("Y-m-d H:i:s"), 
            'updated_at_forms' => date("Y-m-d H:i:s"), 
            'page_url' => $page_url
        ]);
        
        if(isset($form->id))
        {
            $Landingpagecallservice = Landingpagecallservice::find($Landingpagecallservice->id);
            $Landingpagecallservice->status = 1;
            $Landingpagecallservice->form_id_leadservice = $form->id;
            $Landingpagecallservice->save();
        }        
    }
}
