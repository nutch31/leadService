<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Channel;
use App\Model\Form;

class FormController extends BaseController
{
    public function __construct()
    {
        $this->timezone = 'Asia/Bangkok';
    }

    public function getForms()
    {
        $response = array();

        $channels = Channel::with('forms')
        ->where('adwords_campaign_id', '!=', '')
        ->orWhere('facebook_campaign_id', '!=', '')
        ->orderBy('id', 'asc')->get();

        foreach($channels as $channelKey => $channel)
        {
            foreach($channel->forms as $formKey => $form)
            {                               
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
                $dt->setTimezone('GMT');
                $submitDateTime = $dt->format(DateTime::ISO8601);   

                if($channel->adwords_campaign_id != "")
                {
                    $analyticCampaignId = $channel->adwords_campaign_id;
                }
                else
                {
                    $analyticCampaignId = $channel->facebook_campaign_id;
                }

                $array_name = explode(" ", $form->name);
                $firstName = $array_name[0];
                $lastName = "";

                for($x=1;$x<count($array_name);$x++)
                {
                    $lastName.= $array_name[$x].' ';
                }

                $lastName = substr($lastName, 0, -1);

                $response['content'][$formKey]['landingPageCallEvent']['rowId'] = "$form->id";
                $response['content'][$formKey]['landingPageCallEvent']['analyticCampaignId'] = "$analyticCampaignId";
                $response['content'][$formKey]['landingPageCallEvent']['firstName'] = "$firstName";
                $response['content'][$formKey]['landingPageCallEvent']['lastName'] = "$lastName";
                $response['content'][$formKey]['landingPageCallEvent']['email'] = "$form->email";
                $response['content'][$formKey]['landingPageCallEvent']['phone'] = "$form->phone";
                $response['content'][$formKey]['landingPageCallEvent']['submitDateTime'] = "$submitDateTime";

                
                $response['content'][$formKey]['links'][0]['rel'] = "Null";
                $response['content'][$formKey]['links'][0]['href'] = "";
                $response['content'][$formKey]['links'][0]['hreflang'] = "";
                $response['content'][$formKey]['links'][0]['media'] = "";
                $response['content'][$formKey]['links'][0]['title'] = "";
                $response['content'][$formKey]['links'][0]['type'] = "";
                $response['content'][$formKey]['links'][0]['deprecation'] = "";
            }
        }

        $response = json_encode($response, true);

        return response($response, '200');
    }

    public function postForm(Request $request)
    {
        $this->validate($request, [
            'form_id' => 'required',
            'channel_id' => 'required'
        ]);

        $count = Form::where('form_id', '=', $request->form_id)->count();

        if($count == 0)
        {
            $form = Form::create([
                'form_id' => $request->form_id, 
                'channel_id' => $request->channel_id, 
                'name' => $request->name, 
                'email' => $request->email,
                'phone' => $request->phone, 
                'custom_attributes' => $request->custom_attributes, 
                'is_duplicated' => $request->is_duplicated, 
                'ip' => $request->ip,
                'location' => $request->location, 
                'created_at_forms' => $request->created_at, 
                'updated_at_forms' => $request->updated_at, 
                'page_url' => $request->page_url
            ]);

            return response($form, '201');
        }
        else
        {
            return response()->json('Form ID : '.$request->form_id. ' Duplicated', '409');
        }
    }
}