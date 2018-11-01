<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Landingpagecallservice;
use App\Model\Form;
use App\Model\Channel;

class LandingPageCallServiceController extends BaseController
{
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function LandingPageCallService(Request $request)
    {                        
        $Landingpagecallservice = new Landingpagecallservice;
        $Landingpagecallservice->response = $request;
        $Landingpagecallservice->status = 0;
        $Landingpagecallservice->status_alpha = 0;
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
        
        $CampaignId = Channel::where('channel_id', '=', $channel_id)->select('campaign_id')->first();

        $ChannelIds = Channel::where('campaign_id', '=', $CampaignId->campaign_id)->select('channel_id')->get(); 
        $array_channels = [];
        foreach($ChannelIds as $ChannelIdKey => $ChannelId)
        {
            $array_channels[$ChannelIdKey] = $ChannelId->channel_id;
        }

        $parent_id_duplicated = "";
                   
        $count = Form::whereIn('channel_id', $array_channels)->where(function($query) use ($email, $phone_number)
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
        
        $form = Form::create([
            'form_id' => 0, 
            'channel_id' => $channel_id, 
            'name' => $name, 
            'email' => $email,
            'phone' => $phone_number, 
            'custom_attributes' => $data_json, 
            'is_duplicated' => $is_duplicated, 
            'parent_id_duplicated' => $parent_id_duplicated,
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
            
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
            $dt->setTimezone($this->timezone);
            $submitted_date_time = $dt->format(DateTime::ISO8601);   

            //$this->call_alpha($channel_id, $name, $phone_number, $email, $submitted_date_time, $Landingpagecallservice->id, $form->id);
        }        
    }

    
    public function call_alpha($channel_id, $name, $tel, $email, $submitted_date_time, $Landingpagecallservice_id, $form_id)
    {                  
        $array_name = explode(" ", $name);
        $count = count($array_name);

        $first_name = $array_name[0];
        $last_name = '';

        for($a=1;$a<$count;$a++)
        {
            $last_name .= $array_name[$a].' ';
        }

        $last_name = substr($last_name, 0, -1);

        $arr = array(
                     'type' => 'submitted',
                     'data' => [
                         '_id' => $form_id,
                         'channel_id' => $channel_id,
                         'first_name' => $first_name,
                         'last_name' => $last_name,
                         'tel' => $tel,
                         'email' => $email,
                         'submitted_date_time' => $submitted_date_time
                     ]
                    );
        $val = json_encode($arr);

        $url = env("ALPHA_API");
        $url .= "push-leads-data";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
            
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $val);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($val))
        );     
        $response = curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);

        if(!is_null($Landingpagecallservice_id))
        {
            if($info == "200" || $info == "201")
            {
                $Landingpagecallservice = Landingpagecallservice::find($Landingpagecallservice_id);
                $Landingpagecallservice->status_alpha = 1;
                $Landingpagecallservice->save();
            }
        }
    }
    

    
    public function PullLeadsForms(Request $request)
    {   
        $channel = Channel::where('channels.adwords_campaign_id', '=', $request->analyticCampaignId)->orWhere('channels.facebook_campaign_id', '=', $request->analyticCampaignId)->first();

        $forms = Form::where('channel_id', '=', $channel->channel_id);
        if(isset($request->StartDateTime) && isset($request->EndDateTime))
        {                
            $request->StartDateTime = Carbon::parse($request->StartDateTime);
            $request->StartDateTime->setTimezone($this->timezone);

            $request->EndDateTime = Carbon::parse($request->EndDateTime);
            $request->EndDateTime->setTimezone($this->timezone);
            
            $forms = $forms->whereBetween('forms.created_at_forms', [$request->StartDateTime, $request->EndDateTime]);
        }
        $forms = $forms->orderBy('forms.created_at_forms', 'asc')->get();

        foreach($forms as $form)
        {
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
            $dt->setTimezone($this->timezone);
            $submitted_date_time = $dt->format(DateTime::ISO8601);   

            $this->call_alpha($form->channel_id, $form->name, $form->phone, $form->email, $submitted_date_time, Null, $form->id);
        }
        
        return response(array(
            'Status' => 'Success'
        ), '200');
    }
    
}
