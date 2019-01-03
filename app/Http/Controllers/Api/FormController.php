<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use App\Model\Channel;
use App\Model\Form;
use DB;

class FormController extends BaseController
{
    /**
    * Config
    *
    * @param timezone = GMT
    * @param limit = 99999999
    */
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->limit = 99999999;
    }

    /**
    * Get All Lead Forms
    *
    * @param optional page, limit
    */
    public function getForms(Request $request)
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

        $response = $this->Response_getForms($request);
        return response($response, '200');
    }

    /**
    * Get All Lead Forms by analyticCampaignId
    *
    * @param request analyticCampaignId
    * @param optional page, limit
    */
    public function getForms_AnalyticCampaignId(Request $request)
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
        
        $response = $this->Response_getForms($request);
        return response($response, '200');
    }

    /**
    * Get All Lead Forms
    *
    * @param request analyticCampaignId
    * @param optional page, limit
    */
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
        
        if(is_null($forms_minDate) || is_null($forms_maxDate))
        {   
            return response('{"response":"Not Have Leads"}', '200');
        }
                    
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
        $request->limit = $this->limit;
        
        $request->SubmitDateTime = Carbon::parse($request->SubmitDateTime);
        $request->SubmitDateTime->setTimezone($this->timezone);

        $response = $this->Response_getForms($request);
        return response($response, '200');        
    }

    public function getForms_AnalyticCampaignId_StartDateTime_EndDateTime(Request $request)
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

    public function getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Count_Daybyday(Request $request)
    {                
        $array_StartDate = explode("+", $request->StartDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->EndDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->StartDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $request->count = CarbonPeriod::create($request->StartDateTime, $request->EndDateTime);
        $count = $request->count->count();
        
        $response = $this->Reponse_getForms_Count_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }
    
    public function getForms_AnalyticCampaignId_MonthYear_Count_Daybyday(Request $request)
    {              
        $timezone_StartDate = $request->TimeZone;
        $timezone_EndDate = $request->TimeZone;

        $StartDateTime = $request->Year."-".$request->Month."-01T00:00:00+".$timezone_StartDate;
        $request->StartDateTime = $StartDateTime;

        $dt = Carbon::parse($StartDateTime);
        $count = $dt->daysInMonth;
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $EndDateTime = $request->Year."-".$request->Month."-".$count."T23:59:59+".$timezone_StartDate;
        $request->EndDateTime = $EndDateTime;

        $dt = Carbon::parse($EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_Count_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Unique(Request $request)
    {        
        $request->StartDateTime = Carbon::parse($request->StartDateTime);
        $request->StartDateTime->setTimezone($this->timezone);

        $request->EndDateTime = Carbon::parse($request->EndDateTime);
        $request->EndDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_Unique($request);    
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId_StartDateTime_EndDateTime_Unique_Daybyday(Request $request)
    {        
        $array_StartDate = explode("+", $request->StartDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->EndDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->StartDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $request->count = CarbonPeriod::create($request->StartDateTime, $request->EndDateTime);
        $count = $request->count->count();
        
        $response = $this->Reponse_getForms_Unique_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getForms_AnalyticCampaignId_MonthYear_Unique_Daybyday(Request $request)
    {           
        $timezone_StartDate = $request->TimeZone;
        $timezone_EndDate = $request->TimeZone;

        $StartDateTime = $request->Year."-".$request->Month."-01T00:00:00+".$timezone_StartDate;
        $request->StartDateTime = $StartDateTime;

        $dt = Carbon::parse($StartDateTime);
        $count = $dt->daysInMonth;
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $EndDateTime = $request->Year."-".$request->Month."-".$count."T23:59:59+".$timezone_StartDate;
        $request->EndDateTime = $EndDateTime;

        $dt = Carbon::parse($EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_Unique_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function Response_getForms(Request $request)
    {
        $response = array();

        $forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->select(
                        'channels.adwords_campaign_id', 'channels.facebook_campaign_id',
                        'forms.id', 'forms.name', 'forms.email', 'forms.phone', 'forms.custom_attributes', 'forms.created_at_forms'
                    );

        if(isset($request->analyticCampaignId))
        {
            $analyticCampaignId_use = $request->analyticCampaignId;

            $forms = $forms->where(function($query) use ($analyticCampaignId_use)
            {
                $query->where('channels.adwords_campaign_id', '=', $analyticCampaignId_use)->orWhere('channels.facebook_campaign_id', '=', $analyticCampaignId_use);
            });
        }
        else
        {
            $analyticCampaignId_use = "";

            $forms = $forms->where(function($query) use ($analyticCampaignId_use)
            {
                $query->where('channels.adwords_campaign_id', '!=', $analyticCampaignId_use)->orWhere('channels.facebook_campaign_id', '!=', $analyticCampaignId_use);
            });
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
                    
        $forms = $forms->orderBy('forms.created_at_forms', 'asc')
                        ->paginate($request->limit);
                                                
        $response['paging']['count'] = $forms->count();
        $response['paging']['currentPage'] = $forms->currentPage();
        $response['paging']['firstItem'] = $forms->firstItem();
        $response['paging']['hasMorePages'] = $forms->hasMorePages();
        $response['paging']['lastItem'] = $forms->lastItem();
        $response['paging']['lastPage'] = $forms->lastPage();
                
        if(!is_null($forms->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl();
        }
                
        $response['paging']['onFirstPage'] = $forms->onFirstPage();
        //$response['paging']['perPage'] = $forms->perPage();
                
        if(!is_null($forms->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl();
        }
                
        $response['paging']['total'] = $forms->total();

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
            $response['content'][$formKey]['landingPageCallEvent']['custom_attributes'] = "$form->custom_attributes";
            $response['content'][$formKey]['landingPageCallEvent']['submitDateTime'] = "$submitDateTime";

            $response['content'][$formKey]['links'][0]['rel'] = "self";
            $response['content'][$formKey]['links'][0]['href'] = "http://leadservice.heroleads.co.th/leadService/public/index.php/getForms/".$analyticCampaignId."/".$form->phone."/".$submitDateTime;
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

        $analyticCampaignId_use = $request->analyticCampaignId;

        $count = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where(function($query) use ($analyticCampaignId_use)
                    {
                        $query->where('channels.adwords_campaign_id', '=', $analyticCampaignId_use)->orWhere('channels.facebook_campaign_id', '=', $analyticCampaignId_use);
                    })                    
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

    public function Reponse_getForms_Count_Daybyday(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {
        $response = array();

        $last_day = $count-1;
        
        for($day=0;$day<$count;$day++)
        {            
            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;

                $array = explode("T",$request->StartDateTime);
                $EndDateTime = $array[0]." 23:59:59"."+".$timezone_StartDate;

                $dt = Carbon::parse($EndDateTime);
                $EndDateTime = $dt->setTimezone($this->timezone);
            }
            else if($day == $last_day)
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();
                                
                $EndDateTime = $EndDateTime_Convert;
            }
            else
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();

                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $EndDateTime = $dt->addDay();
            }
            
            $analyticCampaignId_use = $request->analyticCampaignId;

            $count_forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where(function($query) use ($analyticCampaignId_use)
                    {
                        $query->where('channels.adwords_campaign_id', '=', $analyticCampaignId_use)->orWhere('channels.facebook_campaign_id', '=', $analyticCampaignId_use);
                    })                    
                    ->whereBetween('forms.created_at_forms', [$StartDateTime, $EndDateTime])
                    ->count();    

            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
    
            $response[$day]['fromDateTime'] = "$Start_DateTime";
            $response[$day]['totalCalls'] = "$count_forms";            
            $response[$day]['analyticCampaignId'] = "$request->analyticCampaignId";
            $response[$day]['toDateTime'] = "$End_DateTime";
        }

        //$response = json_encode($response);
        return $response;
    }

    public function Reponse_getForms_Unique(Request $request)
    {
        $response = array();

        $analyticCampaignId_use = $request->analyticCampaignId;

        $count = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where(function($query) use ($analyticCampaignId_use)
                    {
                        $query->where('channels.adwords_campaign_id', '=', $analyticCampaignId_use)->orWhere('channels.facebook_campaign_id', '=', $analyticCampaignId_use);
                    })                                  
                    ->where('is_duplicated', '=', '0')      
                    ->whereBetween('forms.created_at_forms', [$request->StartDateTime, $request->EndDateTime])
                    ->count();    
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $request->StartDateTime);
        //$dt->setTimezone($this->timezone);
        $StartDateTime = $dt->format(DateTime::ISO8601);   
            
        $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->EndDateTime);
        //$dt2->setTimezone($this->timezone);
        $EndDateTime = $dt2->format(DateTime::ISO8601);   

        $response['fromDateTime'] = "$StartDateTime";
        $response['totalUniqueCalls'] = "$count";
        $response['analyticCampaignId'] = "$request->analyticCampaignId";
        $response['toDateTime'] = "$EndDateTime";
        
        //$response = json_encode($response);
        return $response;

    }
    
    public function Reponse_getForms_Unique_Daybyday(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {        
        $response = array();

        $last_day = $count-1;
        
        for($day=0;$day<$count;$day++)
        {            
            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;

                $array = explode("T",$request->StartDateTime);
                $EndDateTime = $array[0]." 23:59:59"."+".$timezone_StartDate;

                $dt = Carbon::parse($EndDateTime);
                $EndDateTime = $dt->setTimezone($this->timezone);
            }
            else if($day == $last_day)
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();
                                
                $EndDateTime = $EndDateTime_Convert;
            }
            else
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();

                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $EndDateTime = $dt->addDay();
            }
            
            $analyticCampaignId_use = $request->analyticCampaignId;

            $count_forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where(function($query) use ($analyticCampaignId_use)
                    {
                        $query->where('channels.adwords_campaign_id', '=', $analyticCampaignId_use)->orWhere('channels.facebook_campaign_id', '=', $analyticCampaignId_use);
                    })        
                    ->where('is_duplicated', '=', '0')                 
                    ->whereBetween('forms.created_at_forms', [$StartDateTime, $EndDateTime])
                    ->count();    

            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
    
            $response[$day]['fromDateTime'] = "$Start_DateTime";
            $response[$day]['totalUniqueCalls'] = "$count_forms";            
            $response[$day]['analyticCampaignId'] = "$request->analyticCampaignId";
            $response[$day]['toDateTime'] = "$End_DateTime";
        }

        //$response = json_encode($response);
        return $response;
    }

    public function postForm(Request $request)
    {
        $this->validate($request, [
            'form_id' => 'required',
            'channel_id' => 'required'
        ]);
        
        $StartDateTime = date("Y-m-d H:i:s", (strtotime(date($request->created_at)) - 3));
        $EndDateTime = date("Y-m-d H:i:s", (strtotime(date($request->created_at)) + 3));

        $count = Form::whereBetween('created_at_forms', [$StartDateTime, $EndDateTime])
                    ->where('channel_id', '=', $request->channel_id)
                    ->where('email', '=', $request->email)
                    ->where('phone', '=', $request->phone)
                    ->count();

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
    
    public function UpdateParentIdDuplicatedForms(Request $request)
    {
        $forms = DB::table('forms')
                    ->select('id','channel_id','phone','email','created_at_forms')
                    ->orderBy('forms.created_at_forms', 'asc')
                    ->paginate($request->limit);
                        
        $response['paging']['count'] = $forms->count();
        $response['paging']['currentPage'] = $forms->currentPage();
        $response['paging']['firstItem'] = $forms->firstItem();
        $response['paging']['hasMorePages'] = $forms->hasMorePages();
        $response['paging']['lastItem'] = $forms->lastItem();
        $response['paging']['lastPage'] = $forms->lastPage();
                
        if(!is_null($forms->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl();
        }
                
        $response['paging']['onFirstPage'] = $forms->onFirstPage();
        //$response['paging']['perPage'] = $forms->perPage();
                
        if(!is_null($forms->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl();
        }
                
        $response['paging']['total'] = $forms->total();

        foreach($forms as $formKey => $form)
        {   
            $email = $form->email;   
            $phone_number = $form->phone;

            $CampaignId = Channel::where('channel_id', '=', $form->channel_id)->select('campaign_id')->first();

            $ChannelIds = Channel::where('campaign_id', '=', $CampaignId->campaign_id)->select('channel_id')->get(); 
            $array_channels = [];
            foreach($ChannelIds as $ChannelIdKey => $ChannelId)
            {
                $array_channels[$ChannelIdKey] = $ChannelId->channel_id;
            }
                    
            $count = Form::whereIn('channel_id', $array_channels)->where('id', '!=', $form->id)->where('created_at_forms', '<', $form->created_at_forms)->where(function($query) use ($email, $phone_number)
            {
                $query->where('email', '=', $email)->where('email', '!=', '')->orWhere('phone', '=', $phone_number)->where('phone', '!=', '');
            })->count();

            if($count > 0)
            {
                $parent_id_duplicated = Form::whereIn('channel_id', $array_channels)->where('id', '!=', $form->id)->where('created_at_forms', '<', $form->created_at_forms)->where(function($query) use ($email, $phone_number)
                {
                    $query->where('email', '=', $email)->where('email', '!=', '')->orWhere('phone', '=', $phone_number)->where('phone', '!=', '');
                })->orderBy('created_at_forms', 'asc')->select('id')->first();
                                     
                $form_update = Form::find($form->id);
                $form_update->is_duplicated = 1;
                $form_update->parent_id_duplicated = $parent_id_duplicated->id;
                $form_update->save();                
            }
            else
            {
                $form_update = Form::find($form->id);
                $form_update->is_duplicated = 0;
                $form_update->parent_id_duplicated = '';
                $form_update->save();
            } 
                                        
            $response['content'][$formKey]['landingPageCallEvent']['rowId'] = "$form->id";
            $response['content'][$formKey]['landingPageCallEvent']['is_duplicated'] = "$form_update->is_duplicated";
            $response['content'][$formKey]['landingPageCallEvent']['parent_id_duplicated'] = "$form_update->parent_id_duplicated";
        }        
        return $response;
    }    

    public function deleteForm(Request $request)
    {
        $form = Form::findOrFail($request->id);
                
        $log_form = new \App\Model\Log_form;
        
        $log_form->form_id = $form->id;
        $log_form->form_id_herobease = $form->form_id;
        $log_form->channel_id = $form->channel_id;
        $log_form->name = $form->name;
        $log_form->email = $form->email;
        $log_form->phone = $form->phone;
        $log_form->custom_attributes = $form->custom_attributes;
        $log_form->is_duplicated = $form->is_duplicated;
        $log_form->parent_id_duplicated = $form->parent_id_duplicated;
        $log_form->ip = $form->ip;
        $log_form->location = $form->location;
        $log_form->created_at_forms_herobase = $form->created_at_forms;
        $log_form->updated_at_forms_herobase = $form->updated_at_forms;
        $log_form->page_url = $form->page_url;
        $log_form->created_at_forms = $form->created_at;
        $log_form->updated_at_forms = $form->updated_at;
        $log_form->user_id = $request->userId;

        $log_form->save();
        
        $form->delete();

        $response["Result"] = "Success";
        $response["Data"] = $form;
        
        return response($response, '200');
    }

    //
    public function getForms_AnalyticCampaignId_ChannelId(Request $request)
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
        
        $response = $this->Response_getForms_AnalyticCampaignId_ChannelId($request);
        return response($response, '200');
    }

    public function getForms_channelId_getStartEndDate(Request $request)
    {          
        $response = array();

        $forms_minDate = DB::table('forms')                    
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where('channels.channel_id', '=', $request->channelId)
                    ->min('forms.created_at_forms');

        $forms_maxDate = DB::table('forms')                    
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where('channels.channel_id', '=', $request->channelId)
                    ->max('forms.created_at_forms');                    
        
        if(is_null($forms_minDate) || is_null($forms_maxDate))
        {   
            return response('{"response":"Not Have Leads"}', '200');
        }
                    
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

    public function getForms_channelId_StartDateTime_EndDateTime(Request $request)
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

        $request->startDateTime = Carbon::parse($request->startDateTime);
        $request->startDateTime->setTimezone($this->timezone);

        $request->endDateTime = Carbon::parse($request->endDateTime);
        $request->endDateTime->setTimezone($this->timezone);
        
        $response = $this->Response_getForms_AnalyticCampaignId_ChannelId($request);       
        return response($response, '200');
    }

    public function getForms_channelId_StartDateTime_EndDateTime_Count(Request $request)
    {        
        $request->startDateTime = Carbon::parse($request->startDateTime);
        $request->startDateTime->setTimezone($this->timezone);

        $request->endDateTime = Carbon::parse($request->endDateTime);
        $request->endDateTime->setTimezone($this->timezone);
        
        $response = $this->Response_getForms_AnalyticCampaignId_ChannelId_Count($request);    
        return response($response, '200');
    }

    public function getForms_channelId_StartDateTime_EndDateTime_Count_Daybyday(Request $request)
    {        
        $array_StartDate = explode("+", $request->startDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->endDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->startDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->endDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $request->count = CarbonPeriod::create($request->startDateTime, $request->endDateTime);
        $count = $request->count->count();
        
        $response = $this->Reponse_getForms_AnalyticCampaignId_ChannelId_Count_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getForms_channelId_MonthYear_Count_Daybyday(Request $request)
    {        
        $timezone_StartDate = $request->TimeZone;
        $timezone_EndDate = $request->TimeZone;

        $StartDateTime = $request->Year."-".$request->Month."-01T00:00:00+".$timezone_StartDate;
        $request->startDateTime = $StartDateTime;

        $dt = Carbon::parse($StartDateTime);
        $count = $dt->daysInMonth;
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $EndDateTime = $request->Year."-".$request->Month."-".$count."T23:59:59+".$timezone_StartDate;
        $request->endDateTime = $EndDateTime;

        $dt = Carbon::parse($EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_AnalyticCampaignId_ChannelId_Count_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }
    
    public function getForms_channelId_StartDateTime_EndDateTime_Unique(Request $request)
    {        
        $request->startDateTime = Carbon::parse($request->startDateTime);
        $request->startDateTime->setTimezone($this->timezone);

        $request->endDateTime = Carbon::parse($request->endDateTime);
        $request->endDateTime->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_AnalyticCampaignId_ChannelId_Unique($request);    
        return response($response, '200');
    }

    public function getForms_channelId_StartDateTime_EndDateTime_Unique_Daybyday(Request $request)
    {        
        $array_StartDate = explode("+", $request->startDateTime);
        $timezone_StartDate = $array_StartDate[1];

        $array_EndDate = explode("+", $request->endDateTime);
        $timezone_EndDate = $array_EndDate[1];

        $dt = Carbon::parse($request->startDateTime);
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);

        $dt = Carbon::parse($request->endDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);

        $request->count = CarbonPeriod::create($request->startDateTime, $request->endDateTime);
        $count = $request->count->count();
        
        $response = $this->Reponse_getForms_AnalyticCampaignId_ChannelId_Unique_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getForms_channelId_MonthYear_Unique_Daybyday(Request $request)
    {    
        $timezone_StartDate = $request->TimeZone;
        $timezone_EndDate = $request->TimeZone;

        $StartDateTime = $request->Year."-".$request->Month."-01T00:00:00+".$timezone_StartDate;
        $request->startDateTime = $StartDateTime;

        $dt = Carbon::parse($StartDateTime);
        $count = $dt->daysInMonth;
        $StartDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $EndDateTime = $request->Year."-".$request->Month."-".$count."T23:59:59+".$timezone_StartDate;
        $request->endDateTime = $EndDateTime;

        $dt = Carbon::parse($EndDateTime);
        $EndDateTime_Convert = $dt->setTimezone($this->timezone);
        
        $response = $this->Reponse_getForms_AnalyticCampaignId_ChannelId_Unique_Daybyday($request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count);    
        return response($response, '200');
    }

    public function getForms_channelId_CallerPhone_SubmitDateTime(Request $request)
    {        
        $request->limit = $this->limit;
        
        $request->SubmitDateTime = Carbon::parse($request->SubmitDateTime);
        $request->SubmitDateTime->setTimezone($this->timezone);

        $response = $this->Response_getForms_channelId_CallerPhone_SubmitDateTime($request);
        return response($response, '200');                         
    }

    public function Response_getForms_channelId_CallerPhone_SubmitDateTime($request)
    {
        $response = array();

        $forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->where('channels.channel_id', '=', $request->channelId)
                    ->where('forms.phone', '=', $request->CallerPhone)
                    ->where('forms.created_at_forms', '=', $request->SubmitDateTime)
                    ->select(
                        'channels.adwords_campaign_id', 'channels.facebook_campaign_id', 'channels.channel_id',
                        'forms.id', 'forms.name', 'forms.email', 'forms.phone', 'forms.channel_id', 'forms.custom_attributes', 'forms.created_at_forms'
                    )
                    ->orderBy('forms.created_at_forms', 'asc')
                    ->paginate($request->limit);
                                                
        $response['paging']['count'] = $forms->count();
        $response['paging']['currentPage'] = $forms->currentPage();
        $response['paging']['firstItem'] = $forms->firstItem();
        $response['paging']['hasMorePages'] = $forms->hasMorePages();
        $response['paging']['lastItem'] = $forms->lastItem();
        $response['paging']['lastPage'] = $forms->lastPage();
                
        if(!is_null($forms->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl();
        }
                
        $response['paging']['onFirstPage'] = $forms->onFirstPage();
        //$response['paging']['perPage'] = $forms->perPage();
                
        if(!is_null($forms->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl();
        }
                
        $response['paging']['total'] = $forms->total();

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
            $response['content'][$formKey]['landingPageCallEvent']['channelId'] = "$form->channel_id";
            $response['content'][$formKey]['landingPageCallEvent']['firstName'] = "$firstName";
            $response['content'][$formKey]['landingPageCallEvent']['lastName'] = "$lastName";
            $response['content'][$formKey]['landingPageCallEvent']['email'] = "$form->email";
            $response['content'][$formKey]['landingPageCallEvent']['phone'] = "$form->phone";
            $response['content'][$formKey]['landingPageCallEvent']['custom_attributes'] = "$form->custom_attributes";
            $response['content'][$formKey]['landingPageCallEvent']['submitDateTime'] = "$submitDateTime";

            $response['content'][$formKey]['links'][0]['rel'] = "self";                
            $response['content'][$formKey]['links'][0]['href'] = "http://leadservice.heroleads.co.th/leadService/public/index.php/getForms2/".$form->channel_id."/".$form->phone."/".$submitDateTime;
            $response['content'][$formKey]['links'][0]['hreflang'] = null;
            $response['content'][$formKey]['links'][0]['media'] = null;
            $response['content'][$formKey]['links'][0]['title'] = null;
            $response['content'][$formKey]['links'][0]['type'] = null;
            $response['content'][$formKey]['links'][0]['deprecation'] = null;
        }
        
        //$response = json_encode($response);
        return $response;         
    }

    public function Response_getForms_AnalyticCampaignId_ChannelId(Request $request)
    {
        $array_channel = array(); 
        $key = 0;

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('channel_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $key++;
                }
            }
        }

        $response = array();

        $forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->whereIn('channels.channel_id', $array_channel)
                    ->select(
                        'channels.adwords_campaign_id', 'channels.facebook_campaign_id', 'channels.channel_id',
                        'forms.id', 'forms.name', 'forms.email', 'forms.phone', 'forms.channel_id', 'forms.custom_attributes', 'forms.created_at_forms'
                    );

        if(isset($request->startDateTime) && isset($request->endDateTime))
        {
            $forms = $forms->whereBetween('forms.created_at_forms', [$request->startDateTime, $request->endDateTime]);
        }
                    
        $forms = $forms->orderBy('forms.created_at_forms', 'asc')
                        ->paginate($request->limit);
                                                
        $response['paging']['count'] = $forms->count();
        $response['paging']['currentPage'] = $forms->currentPage();
        $response['paging']['firstItem'] = $forms->firstItem();
        $response['paging']['hasMorePages'] = $forms->hasMorePages();
        $response['paging']['lastItem'] = $forms->lastItem();
        $response['paging']['lastPage'] = $forms->lastPage();
                
        if(!is_null($forms->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $forms->nextPageUrl();
        }
                
        $response['paging']['onFirstPage'] = $forms->onFirstPage();
        //$response['paging']['perPage'] = $forms->perPage();
                
        if(!is_null($forms->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl()."&limit=".$request->limit;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $forms->previousPageUrl();
        }
                
        $response['paging']['total'] = $forms->total();

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
            $response['content'][$formKey]['landingPageCallEvent']['channelId'] = "$form->channel_id";
            $response['content'][$formKey]['landingPageCallEvent']['firstName'] = "$firstName";
            $response['content'][$formKey]['landingPageCallEvent']['lastName'] = "$lastName";
            $response['content'][$formKey]['landingPageCallEvent']['email'] = "$form->email";
            $response['content'][$formKey]['landingPageCallEvent']['phone'] = "$form->phone";
            $response['content'][$formKey]['landingPageCallEvent']['custom_attributes'] = "$form->custom_attributes";
            $response['content'][$formKey]['landingPageCallEvent']['submitDateTime'] = "$submitDateTime";

            $response['content'][$formKey]['links'][0]['rel'] = "self";                
            $response['content'][$formKey]['links'][0]['href'] = "http://leadservice.heroleads.co.th/leadService/public/index.php/getForms2/".$form->channel_id."/".$form->phone."/".$submitDateTime;
            $response['content'][$formKey]['links'][0]['hreflang'] = null;
            $response['content'][$formKey]['links'][0]['media'] = null;
            $response['content'][$formKey]['links'][0]['title'] = null;
            $response['content'][$formKey]['links'][0]['type'] = null;
            $response['content'][$formKey]['links'][0]['deprecation'] = null;
        }
        
        //$response = json_encode($response);
        return $response;                  
    }

    public function Response_getForms_AnalyticCampaignId_ChannelId_Count(Request $request)
    {                
        $array_channel = array(); 
        $key = 0;

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('channel_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $key++;
                }
            }
        }
        
        $response = array();

        $count = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->whereIn('channels.channel_id', $array_channel)                    
                    ->whereBetween('forms.created_at_forms', [$request->startDateTime, $request->endDateTime])
                    ->count();    
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $request->startDateTime);
        //$dt->setTimezone($this->timezone);
        $StartDateTime = $dt->format(DateTime::ISO8601);   
            
        $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->endDateTime);
        //$dt2->setTimezone($this->timezone);
        $EndDateTime = $dt2->format(DateTime::ISO8601);   

        $response['fromDateTime'] = "$StartDateTime";
        $response['totalCalls'] = "$count";
        $response['toDateTime'] = "$EndDateTime";
        
        //$response = json_encode($response);
        return $response;
    }
    
    public function Reponse_getForms_AnalyticCampaignId_ChannelId_Count_Daybyday(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {                
        $array_channel = array(); 
        $key = 0;

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('channel_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $key++;
                }
            }
        }
        
        $response = array();

        $last_day = $count-1;
        
        for($day=0;$day<$count;$day++)
        {            
            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;

                $array = explode("T",$request->startDateTime);
                $EndDateTime = $array[0]." 23:59:59"."+".$timezone_StartDate;

                $dt = Carbon::parse($EndDateTime);
                $EndDateTime = $dt->setTimezone($this->timezone);
            }
            else if($day == $last_day)
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();
                                
                $EndDateTime = $EndDateTime_Convert;
            }
            else
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();

                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $EndDateTime = $dt->addDay();
            }

            $count_forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->whereIn('channels.channel_id', $array_channel)                    
                    ->whereBetween('forms.created_at_forms', [$StartDateTime, $EndDateTime])
                    ->count();    

            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
    
            $response[$day]['fromDateTime'] = "$Start_DateTime";
            $response[$day]['totalCalls'] = "$count_forms";            
            $response[$day]['toDateTime'] = "$End_DateTime";
        }

        //$response = json_encode($response);
        return $response;
    }
    
    public function Reponse_getForms_AnalyticCampaignId_ChannelId_Unique(Request $request)
    {                        
        $array_channel = array(); 
        $key = 0;

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('channel_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $key++;
                }
            }
        }

        $response = array();

        $count = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->whereIn('channels.channel_id', $array_channel)                                  
                    ->where('is_duplicated', '=', '0')      
                    ->whereBetween('forms.created_at_forms', [$request->startDateTime, $request->endDateTime])
                    ->count();    
                    
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $request->startDateTime);
        //$dt->setTimezone($this->timezone);
        $StartDateTime = $dt->format(DateTime::ISO8601);   
            
        $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->endDateTime);
        //$dt2->setTimezone($this->timezone);
        $EndDateTime = $dt2->format(DateTime::ISO8601);   

        $response['fromDateTime'] = "$StartDateTime";
        $response['totalUniqueCalls'] = "$count";
        $response['toDateTime'] = "$EndDateTime";
        
        //$response = json_encode($response);
        return $response;

    }
        
    public function Reponse_getForms_AnalyticCampaignId_ChannelId_Unique_Daybyday(Request $request, $timezone_StartDate, $timezone_EndDate, $StartDateTime_Convert, $EndDateTime_Convert, $count)
    {                                  
        $array_channel = array(); 
        $key = 0;

        if(isset($request->analyticCampaignId))
        {
            $count_analyticCampaignId = count($request->analyticCampaignId);
            if($count_analyticCampaignId > 0)
            {
                $channels_analyticCampaignIds = Channel::whereIn('adwords_campaign_id', $request->analyticCampaignId)->orWhereIn('facebook_campaign_id', $request->analyticCampaignId)->get();
                
                foreach($channels_analyticCampaignIds as $channels_analyticCampaignId)
                {
                    $array_channel[$key] = $channels_analyticCampaignId->channel_id;
                    $key++;
                }
            }
        }

        if(isset($request->channelId))
        {
            $count_channelId = count($request->channelId);
            if($count_channelId > 0)
            {
                $channels_channelIds = Channel::whereIn('channel_id', $request->channelId)->get();
                
                foreach($channels_channelIds as $channels_channelId)
                {
                    $array_channel[$key] = $channels_channelId->channel_id;
                    $key++;
                }
            }
        }

        $response = array();      

        $last_day = $count-1;
        
        for($day=0;$day<$count;$day++)
        {            
            if($day ==0)
            {
                $StartDateTime = $StartDateTime_Convert;

                $array = explode("T",$request->startDateTime);
                $EndDateTime = $array[0]." 23:59:59"."+".$timezone_StartDate;

                $dt = Carbon::parse($EndDateTime);
                $EndDateTime = $dt->setTimezone($this->timezone);
            }
            else if($day == $last_day)
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();
                                
                $EndDateTime = $EndDateTime_Convert;
            }
            else
            {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $StartDateTime = $dt->addSecond();

                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
                $EndDateTime = $dt->addDay();
            }
            
            $analyticCampaignId_use = $request->analyticCampaignId;

            $count_forms = DB::table('forms')
                    ->join('channels', 'channels.channel_id', '=', 'forms.channel_id')
                    ->whereIn('channels.channel_id', $array_channel)  
                    ->where('is_duplicated', '=', '0')                 
                    ->whereBetween('forms.created_at_forms', [$StartDateTime, $EndDateTime])
                    ->count();    

            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $StartDateTime);
            //$dt->setTimezone($this->timezone);
            $Start_DateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $EndDateTime);
            //$dt2->setTimezone($this->timezone);
            $End_DateTime = $dt2->format(DateTime::ISO8601);   
    
            $response[$day]['fromDateTime'] = "$Start_DateTime";
            $response[$day]['totalUniqueCalls'] = "$count_forms";            
            $response[$day]['toDateTime'] = "$End_DateTime";
        }

        //$response = json_encode($response);
        return $response;
    }
}
