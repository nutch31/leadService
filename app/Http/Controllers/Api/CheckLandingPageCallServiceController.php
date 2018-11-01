<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Form;
use App\Model\Channel;

class CheckLandingPageCallServiceController extends BaseController
{    
    public function CheckLandingPageCallService(Request $request)
    {
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
        
        $CampaignId = Channel::where('channel_id', '=', $channel_id)->select('campaign_id')->first();

        $ChannelIds = Channel::where('campaign_id', '=', $CampaignId->campaign_id)->select('channel_id')->get(); 
        $array_channels = [];
        foreach($ChannelIds as $ChannelIdKey => $ChannelId)
        {
            $array_channels[$ChannelIdKey] = $ChannelId->channel_id;
        }

        $parent_id_duplicated = "";

        $count = Form::whereIn('channel_id', $array_channels)->where('is_duplicated', '=', '0')->where(function($query) use ($email, $phone_number)
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

            $parent_id_duplicated = Form::whereIn('channel_id', $array_channels)->where('is_duplicated', '=', '0')->where(function($query) use ($email, $phone_number)
            {
                $query->where('email', '=', $email)->orWhere('phone', '=', $phone_number);
            })->select('id')->first();

            if($parent_id_duplicated)            
            {
                $parent_id_duplicated = $parent_id_duplicated->id;
            }
        }

        $response = array(
            "channel_id" => $channel_id,
            "name" => $name,
            "email" => $email,
            "phone_number" => $phone_number,
            "ip_address" => $ip_address,
            "data_json" => $data_json,
            "page_url" => $page_url,
            "is_duplicated" => $is_duplicated,
            "parent_id_duplicated" => $parent_id_duplicated, 
            'created_at_forms' => date("Y-m-d H:i:s"), 
            'updated_at_forms' => date("Y-m-d H:i:s"), 
        );
        
        return response($response, '200');
    }   
}