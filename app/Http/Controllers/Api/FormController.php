<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Channel;
use App\Model\Form;
use DB;

class FormController extends BaseController
{
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
    }

    public function getForms(Request $request)
    {
        $response = $this->Response_getForms($request);
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId(Request $request)
    {
        $response = $this->Response_getForms($request);
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId_getStartEndDate(Request $request)
    {        
        $response = array();

        $forms_minDate = DB::table('forms')                    
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where('channels.adwords_campaign_id', '=', $request->analyticCampaignId)->orWhere('channels.facebook_campaign_id', '=', $request->analyticCampaignId)
                    ->min('forms.created_at_forms');

        $forms_maxDate = DB::table('forms')                    
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where('channels.adwords_campaign_id', '=', $request->analyticCampaignId)->orWhere('channels.facebook_campaign_id', '=', $request->analyticCampaignId)
                    ->max('forms.created_at_forms');
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $forms_minDate);
        //$dt->setTimezone($this->timezone);
        $startDate = $dt->format(DateTime::ISO8601);   
                            
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $forms_maxDate);
        //$dt->setTimezone($this->timezone);
        $endDate = $dt->format(DateTime::ISO8601);  

        $response['startDate'] = "$startDate";
        $response['endDate'] = "$endDate";
                
        //$response = json_encode($response);       
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId_CallerPhone_SubmitDateTime(Request $request)
    {
        $request->SubmitDateTime = Carbon::parse($request->SubmitDateTime);
        $request->SubmitDateTime->setTimezone($this->timezone);

        $response = $this->Response_getForms($request);
        return response($response, '200');        
    }

    public function getForms_AnalyticCampaignId_StartDateTime_EndDateTime(Request $request)
    {
        $request->StartDateTime = Carbon::parse($request->StartDateTime);
        $request->StartDateTime->setTimezone($this->timezone);

        $request->EndDateTime = Carbon::parse($request->EndDateTime);
        $request->EndDateTime->setTimezone($this->timezone);
        
        $response = $this->Response_getForms($request);       
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Count(Request $request)
    {        
        $request->StartDateTime = Carbon::parse($request->StartDateTime);
        $request->StartDateTime->setTimezone($this->timezone);

        $request->EndDateTime = Carbon::parse($request->EndDateTime);
        $request->EndDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_Count($request);    
        return response($response, '200');
    }

    public function Response_getForms(Request $request)
    {
        $response = array();

        $forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->select(
                        'channels.adwords_campaign_id', 'channels.facebook_campaign_id',
                        'forms.id', 'forms.name', 'forms.email', 'forms.phone', 'forms.created_at_forms'
                    );

        if(isset($request->analyticCampaignId))
        {
            $forms = $forms->where('channels.adwords_campaign_id', '=', $request->analyticCampaignId)->orWhere('channels.facebook_campaign_id', '=', $request->analyticCampaignId);
        }
        else
        {
            $forms = $forms->where('channels.adwords_campaign_id', '!=' ,'')->orWhere('channels.facebook_campaign_id', '!=', '');
        }
        
        if(isset($request->CallerPhone))
        {
            $forms = $forms->where('forms.phone', '=', $request->CallerPhone);
        }

        if(isset($request->SubmitDateTime))
        {
            $forms = $forms->where('forms.created_at_forms', '=', $request->SubmitDateTime);
        }

        if(isset($request->StartDateTime) && isset($request->EndDateTime))
        {
            $forms = $forms->whereBetween('forms.created_at_forms', [$request->StartDateTime, $request->EndDateTime]);
        }
                    
        $forms = $forms->orderBy('forms.form_id', 'asc')->get();

        foreach($forms as $formKey => $form)
        {                               
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
            //$dt->setTimezone('GMT');
            $submitDateTime = $dt->format(DateTime::ISO8601);   

            if(trim($form->adwords_campaign_id) != "")
            {
                $analyticCampaignId = $form->adwords_campaign_id;
            }
            else
            {
                $analyticCampaignId = $form->facebook_campaign_id;
            }

            $array_name = explode(" ", $form->name);
            $firstName = $array_name[0];
            $lastName = "";

            for($x=1;$x<count($array_name);$x++)
            {
                $lastName.= $array_name[$x].' ';
            }

            $lastName = substr($lastName, 0, -1);
            
            $response['links'] = array();
            $response['content'][$formKey]['landingPageCallEvent']['rowId'] = "$form->id";
            $response['content'][$formKey]['landingPageCallEvent']['analyticCampaignId'] = "$analyticCampaignId";
            $response['content'][$formKey]['landingPageCallEvent']['firstName'] = "$firstName";
            $response['content'][$formKey]['landingPageCallEvent']['lastName'] = "$lastName";
            $response['content'][$formKey]['landingPageCallEvent']['email'] = "$form->email";
            $response['content'][$formKey]['landingPageCallEvent']['phone'] = "$form->phone";
            $response['content'][$formKey]['landingPageCallEvent']['submitDateTime'] = "$submitDateTime";

            $response['content'][$formKey]['links'][0]['rel'] = "self";
            $response['content'][$formKey]['links'][0]['href'] = "http://128.199.186.53/leadService/public/index.php/getforms/".$analyticCampaignId."/".$form->phone."/".$submitDateTime;
            $response['content'][$formKey]['links'][0]['hreflang'] = null;
            $response['content'][$formKey]['links'][0]['media'] = null;
            $response['content'][$formKey]['links'][0]['title'] = null;
            $response['content'][$formKey]['links'][0]['type'] = null;
            $response['content'][$formKey]['links'][0]['deprecation'] = null;
        }
        
        //$response = json_encode($response);
        return $response;
    }

    public function Reponse_getForms_Count(Request $request)
    {
        $response = array();

        $count = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where('channels.adwords_campaign_id', '=', $request->analyticCampaignId)->orWhere('channels.facebook_campaign_id', '=', $request->analyticCampaignId)
                    ->whereBetween('forms.created_at_forms', [$request->StartDateTime, $request->EndDateTime])
                    ->count();    
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $request->StartDateTime);
        //$dt->setTimezone($this->timezone);
        $StartDateTime = $dt->format(DateTime::ISO8601);   
            
        $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->EndDateTime);
        //$dt2->setTimezone($this->timezone);
        $EndDateTime = $dt2->format(DateTime::ISO8601);   

        $response['fromDateTime'] = "$StartDateTime";
        $response['totalCalls'] = "$count";
        $response['analyticCampaignId'] = "$request->analyticCampaignId";
        $response['toDateTime'] = "$EndDateTime";
        
        //$response = json_encode($response);
        return $response;

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