<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Account;
use App\Model\Campaign;
use App\Model\Channel;
use App\Model\Call;
use App\Model\Form;
use DB;

class VerifyLeadsEveryCampaignController extends BaseController
{    
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->local_timezone = 'Asia/Bangkok';
        $this->awareness = "5c0f3def905b810140086712";
        $this->send_mail_api = "api/verifyLeadsEveryCampaignEmail";
    }
    
    public function verifyLeadsEveryCampaign()
    {   
        $array_response = array();

        $get_data_verify_leads = $this->call_alpha0("lead-service/campaign/get-data-verify-leads");
        
        foreach($get_data_verify_leads as $keyGetDataVerifyLeads => $get_data_verify_lead)
        {            

            $findme   = 'Test';
            $pos = strpos($get_data_verify_lead["campaign_name"], $findme);

            // Note our use of ===.  Simply == would not work as expected
            // because the position of 'a' was the 0th (first) character.
            if ($pos === false) {

                $array_response[$keyGetDataVerifyLeads]["campaign_id"]         = $get_data_verify_lead["campaign_id"];
                $array_response[$keyGetDataVerifyLeads]["campaign_name"]       = $get_data_verify_lead["campaign_name"];
                $array_response[$keyGetDataVerifyLeads]["email_account_manager"] = $get_data_verify_lead["email_account_manager"];
                $array_response[$keyGetDataVerifyLeads]["name_account_manager"]  = $get_data_verify_lead["name_account_manager"];
                $array_response[$keyGetDataVerifyLeads]["email"]               = $get_data_verify_lead["email"];
                $array_response[$keyGetDataVerifyLeads]["name"]                = $get_data_verify_lead["name"];
                $array_response[$keyGetDataVerifyLeads]["email_from"]          = "alpha-noreply@heroleads.com";
                $array_response[$keyGetDataVerifyLeads]["email_bcc"]           = array('nut@heroleads.com', 'palakorn@heroleads.com', 'pongchrist@heroleads.com', 'tarn@heroleads.com', 'nan@heroleads.com', 'pierre@heroleads.com', 'ch@heroleads.com');
                $array_response[$keyGetDataVerifyLeads]["subject_name"]        = "Subject : Notify Incomplete Campaigns Data";
                $array_response[$keyGetDataVerifyLeads]["lead_status"]         = "Success";

                $campaignId = $get_data_verify_lead["campaign_id"];

                if(isset($get_data_verify_lead["facebook_account_id"]))
                {
                    $facebook_account_id = $get_data_verify_lead["facebook_account_id"];
                }
                else
                {
                    $facebook_account_id = null;
                }

                if(isset($get_data_verify_lead["google_account_id"]))
                {
                    $google_account_id = $get_data_verify_lead["google_account_id"];
                }
                else
                {
                    $google_account_id = null;
                }
                
                if(isset($get_data_verify_lead["objective_id"]))
                {
                    $objective_id = $get_data_verify_lead["objective_id"];
                }
                else
                {
                    $objective_id = null;
                }

                //Remark
                if(is_null($facebook_account_id) && is_null($google_account_id))
                {
                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                    $array_response[$keyGetDataVerifyLeads]["remark"]["Not enter Facebook Account ID and Google Account ID (Client ID)."] = "-";
                    $array_response[$keyGetDataVerifyLeads]["link"]["Not enter Facebook Account ID and Google Account ID (Client ID)."] = env("ALPHA1")."campaigns/overview/".$campaignId."/edit";
                }
                
                $account = Account::select('account_id', 'company_name', 'adwords_account_id', 'facebook_account_id')
                ->where(function($q) use ($facebook_account_id, $google_account_id){                                        
                    $q->where('facebook_account_id', $facebook_account_id)->where('facebook_account_id', '!=', '')
                    ->orWhere('adwords_account_id', $google_account_id)->where('adwords_account_id', '!=', '');
                })
                ->orderBy('account_id', 'asc')
                ->first(); 

                if(empty($account))
                {
                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                    $array_response[$keyGetDataVerifyLeads]["remark"]["Herobase not enter Facebook Account ID and Google Account ID."] = "Missing Facebook Account ID {".$facebook_account_id."} and Google Account ID {".$google_account_id."}";                
                    $array_response[$keyGetDataVerifyLeads]["link"]["Herobase not enter Facebook Account ID and Google Account ID."] = env("HEROBASE");
                }

                if(is_null($objective_id))
                {
                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                    $array_response[$keyGetDataVerifyLeads]["remark"]["Not enter Campaign Objective."] = "-";
                    $array_response[$keyGetDataVerifyLeads]["link"]["Not enter Campaign Objective."] = env("ALPHA1")."campaigns/overview/".$campaignId."/edit";
                }
                
                /** Call Alpha API Find StartDate, EndDate CampaignId */
                $date_range = $this->call_alpha0("crm/get-date-range/".$campaignId);

                if(empty($date_range))
                {
                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                    $array_response[$keyGetDataVerifyLeads]["remark"]["Not enter Campaign Start Date and End Date."] = "-";
                    $array_response[$keyGetDataVerifyLeads]["link"]["Not enter Campaign Start Date and End Date."] = env("ALPHA1")."campaigns/overview/".$campaignId."/edit";
                }

                if(!is_null($facebook_account_id) || !is_null($google_account_id))
                {
                    $array_response[$keyGetDataVerifyLeads]["facebook_account_id"] = $facebook_account_id;
                    $array_response[$keyGetDataVerifyLeads]["google_account_id"]   = $google_account_id;      
        
                    if(!empty($account))
                    {   
                        /** Find Campaigns in each Account */
                        $array_campaign = array();
        
                        $campaigns = Campaign::select('campaign_id')
                                                ->where('account_id', '=', $account->account_id)
                                                ->orderBy('campaign_id', 'asc')
                                                ->get();
        
                        foreach($campaigns as $keyCampaign => $campaign)
                        {
                            $array_campaign[$keyCampaign] = $campaign->campaign_id;
                        }
        
                        /** Find Channels Call in each Campaign */                           
                        $array_channel_call             = array();
                        $array_channel_tracking_phone   = array();
                        $channel_tracking_phone_string  = "";
        
                        $channel_calls = DB::table('channels')                    
                                                ->join('calls', 'calls.channel_id', '=', 'channels.channel_id')
                                                ->select('channels.channel_id', 'channels.tracking_phone')
                                                ->whereIn('campaign_id', $array_campaign);
                                        if(!empty($date_range))
                                        {   
                                            $startDate_ = $date_range["startDate"]." 00:00:00";
                                            $endDate_ = $date_range["endDate"]." 23:59:59";
                                            $channel_calls = $channel_calls->whereBetween('date', array($startDate_, $endDate_));
                                        }
                                                //->where('tracking_phone', '!=', '')
                        $channel_calls = $channel_calls->groupBy('channels.channel_id')
                                                    ->get();
        
                        foreach($channel_calls as $keyChannelCall => $channel_call)
                        {
                            $array_channel_call[$keyChannelCall]            = $channel_call->channel_id;
                            $array_channel_tracking_phone[$keyChannelCall]  = $channel_call->tracking_phone;
                            $channel_tracking_phone_string                  .= "&didPhone[]=".$channel_call->tracking_phone;
                        }
        
                        /** Find Channels Form in each Campaign */
                        $array_channel_form                         = array();
                        $array_channel_analytic_campaign_id         = array();
                        $array_channel_channel_id                   = array();
                        $channel_analytic_campaign_id_string        = "";
        
                        $channel_forms = DB::table('channels')  
                                                ->join('forms', 'forms.channel_id', '=', 'channels.channel_id')
                                                ->select('channels.channel_id', 'channels.facebook_campaign_id', 'channels.adwords_campaign_id')
                                                ->whereIn('campaign_id', $array_campaign)
                                                ->where('tracking_phone', '=', '');
                                                //->where(function($q){
                                                //    $q->where('facebook_campaign_id', '!=', '')->orWhere('adwords_campaign_id', '!=', '');
                                                //})
                                        if(!empty($date_range))
                                        {
                                            $startDate_ = $date_range["startDate"]." 00:00:00";
                                            $endDate_ = $date_range["endDate"]." 23:59:59";
                                            $channel_forms = $channel_forms->whereBetween('created_at_forms', array($startDate_, $endDate_));
                                        }
                        $channel_forms = $channel_forms->groupBy('channels.channel_id')
                                                    ->get();
                        
                        foreach($channel_forms as $keyChannelForm => $channel_form)
                        {
                            $array_channel_form[$keyChannelForm] = $channel_form->channel_id;
        
                            if($channel_form->facebook_campaign_id != "")
                            {
                                $array_channel_analytic_campaign_id[$keyChannelForm]    = $channel_form->facebook_campaign_id;
                                $channel_analytic_campaign_id_string                    .= "&analyticCampaignId[]=".$channel_form->facebook_campaign_id;
                            }
                            else if($channel_form->adwords_campaign_id != "")
                            {
                                $array_channel_analytic_campaign_id[$keyChannelForm]    = $channel_form->adwords_campaign_id;
                                $channel_analytic_campaign_id_string                    .= "&analyticCampaignId[]=".$channel_form->adwords_campaign_id;
                            }
                            else
                            {
                                $array_channel_channel_id[$keyChannelForm]              = $channel_form->channel_id;
                                $channel_analytic_campaign_id_string                    .= "&channelId[]=".$channel_form->channel_id;
                            }                
                        }
                        
                        if(!is_null($objective_id))
                        {
                            //Awareness Leads 
                            if($objective_id != $this->awareness)
                            {
                                $array_response[$keyGetDataVerifyLeads]["existing_in_leadservice"]["analytic_campaign_id"] = array_values(array_filter($array_channel_analytic_campaign_id)); 
                                $array_response[$keyGetDataVerifyLeads]["existing_in_leadservice"]["channel_id"]           = array_values(array_filter($array_channel_channel_id));       
                                $array_response[$keyGetDataVerifyLeads]["existing_in_leadservice"]["did_phone"]            = array_values(array_filter($array_channel_tracking_phone));    
        
                                $array_response[$keyGetDataVerifyLeads]["existing_in_alpha"]["analytic_campaign_id"]       = array_values($get_data_verify_lead["analytic_campaign_id"]);                                
                                $array_response[$keyGetDataVerifyLeads]["existing_in_alpha"]["channel_id"]                 = array_values(array()); 
                                $array_response[$keyGetDataVerifyLeads]["existing_in_alpha"]["did_phone"]                  = array_values($get_data_verify_lead["did_phone"]);   
                                                    
                                $result_analytic_campaign_id = array_diff($array_channel_analytic_campaign_id, $get_data_verify_lead["analytic_campaign_id"]);
                                $result_analytic_channel_id  = array_diff($array_channel_channel_id, array());
                                $result_did_phone            = array_diff($array_channel_tracking_phone, $get_data_verify_lead["did_phone"]); 
                                    
                                $array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["analytic_campaign_id"]   = array_values(array_filter($result_analytic_campaign_id));  
                                $array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["channel_id"]             = array_values(array_filter($result_analytic_channel_id));  
                                $array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["did_phone"]              = array_values(array_filter($result_did_phone));                               
                                    
                                if(!empty($array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["analytic_campaign_id"]))
                                {
                                    $_facebook_campaign_id = array();
                                    $_google_campaign_id = array();

                                    foreach($array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["analytic_campaign_id"] as $keyNotExisting => $_analytic_campaign_id)
                                    {

                                        if(strlen($_analytic_campaign_id) > 10)
                                        {
                                            $_facebook_campaign_id[$keyNotExisting] = $_analytic_campaign_id;
                                        }
                                        else
                                        {
                                            $_google_campaign_id[$keyNotExisting] = $_analytic_campaign_id;
                                        }
                                    }

                                    if(!empty($_facebook_campaign_id) && !empty($_google_campaign_id))
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["remark"]["Not Enter Media Campaign ID."] = "Missing Facebook Campaign ID {".implode(',',$_facebook_campaign_id)."} <br> Missing Google Campaign ID {".implode(',',$_google_campaign_id)."}";
                                    }
                                    else if(!empty($_facebook_campaign_id))
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["remark"]["Not Enter Media Campaign ID."] = "Missing Facebook Campaign ID {".implode(',',$_facebook_campaign_id)."}";
                                    }
                                    else if(!empty($_google_campaign_id))
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["remark"]["Not Enter Media Campaign ID."] = "Missing Google Campaign ID {".implode(',',$_google_campaign_id)."}";
                                    }
                                    
                                    $array_response[$keyGetDataVerifyLeads]["link"]["Not Enter Media Campaign ID."] = env("ALPHA1")."campaigns/overview/".$campaignId."/edit";
                                }    
                                if(!empty($array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["channel_id"]))
                                {
                                    $array_response[$keyGetDataVerifyLeads]["remark"]["Not Enter Media Campaign ID."] = "Missing Channel ID {".implode(',',$array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["channel_id"])."}";                                
                                    $array_response[$keyGetDataVerifyLeads]["link"]["Not Enter Media Campaign ID."] = env("ALPHA1")."campaigns/overview/".$campaignId."/edit";
                                }
                                if(!empty($array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["did_phone"]))
                                {
                                    $array_response[$keyGetDataVerifyLeads]["remark"]["Not Enter Media Campaign ID."] = "{".implode(',',$array_response[$keyGetDataVerifyLeads]["not_existing_in_alpha"]["did_phone"])."} missing DID Phone number.";                            
                                    $array_response[$keyGetDataVerifyLeads]["link"]["Not Enter Media Campaign ID."] = env("ALPHA1")."campaigns/overview/".$campaignId."/edit";
                                }
        
                                if(!empty($date_range))
                                {                                   
                                    $array_response[$keyGetDataVerifyLeads]["campaign_start_date"] = $date_range["startDate"];                            
                                    $array_response[$keyGetDataVerifyLeads]["campaign_end_date"]   = $date_range["endDate"];  
                                        
                                    /** Default endDate Today Y-m-d */                
                                    $date_range["endDate"] = date("Y-m-d");
        
                                    $startDate  = $date_range["startDate"];
                                    $endDate    = $date_range["endDate"];
        
                                    /** Create startDateTime for search leadService API*/  
                                    $dt              = Carbon::parse($startDate. "00:00:00", $this->local_timezone);
                                    //$startDate       = $dt->setTimezone($this->timezone);       
                                    $startDateSearch = $dt->format(DateTime::ISO8601); 
                                    $startDateSearch = urlencode($startDateSearch);
        
                                    /** Create endDateTime for search leadService API*/
                                    $dt             = Carbon::parse($endDate. "23:59:59", $this->local_timezone);
                                    //$endDate        = $dt->setTimezone($this->timezone);            
                                    $endDateSearch  = $dt->format(DateTime::ISO8601);  
                                    $endDateSearch  = urlencode($endDateSearch);
                                                        
                                    /** Call Alpha API total-called*/
                                    $total_called_alpha       = $this->call_alpha0("crm/widget/total-called/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                                    /** Call leadService API total-called*/
                                    $total_called_leadService = $this->call_leadservice("getCalls2/byPeriod/unique?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_tracking_phone_string);            
        
                                    if($total_called_alpha["data"] == $total_called_leadService["totalUniqueCalls"])
                                    {                
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_called"]["status"] = "Success";
                                    }
                                    else
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_called"]["status"] = "Error";
                                    }
                                        
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_called"]["total_unique_called_alpha"]       = $total_called_alpha["data"];
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_called"]["total_unique_called_leadservice"] = $total_called_leadService["totalUniqueCalls"];
        
                                    /** Call Alpha API total-missed-calls*/
                                    $total_missed_calls_alpha = $this->call_alpha0("crm/widget/total-missed-calls/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                                    /** Call leadService API total-missed-calls*/
                                    $total_missed_calls_leadService = $this->call_leadservice("getCalls2/byPeriod/unique?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_tracking_phone_string."&status=2");            
        
                                    if($total_missed_calls_alpha["data"] == $total_missed_calls_leadService["totalUniqueCalls"])
                                    {                
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_missed_calls"]["status"] = "Success";
                                    }
                                    else
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_missed_calls"]["status"] = "Error";
                                    }
                                        
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_missed_calls"]["total_missed_calls_alpha"]       = $total_missed_calls_alpha["data"];
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_missed_calls"]["total_missed_calls_leadservice"] = $total_missed_calls_leadService["totalUniqueCalls"];
                                                        
                                    /** Call Alpha API total-missed-calls*/
                                    $total_form_submitted = $this->call_alpha0("crm/widget/total-form-submitted/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                                    /** Call leadService API total-missed-calls*/
                                    $total_form_submitted_leadService = $this->call_leadservice("getForms2/byPeriod/unique?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string);            
        
                                    if($total_form_submitted["data"] == $total_form_submitted_leadService["totalUniqueCalls"])
                                    {                
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_forms_submitted"]["status"] = "Success";
                                    }
                                    else
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_forms_submitted"]["status"] = "Error";
                                    }
                                        
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_forms_submitted"]["total_unique_forms_submitted_alpha"]       = $total_form_submitted["data"];
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_forms_submitted"]["total_unique_forms_submitted_leadservice"] = $total_form_submitted_leadService["totalUniqueCalls"];
        
                                    /** Call Alpha API total-missed-calls*/
                                    $total_unique_leads = $this->call_alpha0("crm/widget/total-unique-leads/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                                    /** Call leadService API total-missed-calls*/
                                    $total_unique_leads_leadService = $this->call_leadservice("getCallsForms/byPeriodDidPhoneAnalyticCampaignId/?page=1&limit=1&is_duplicated=0&startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string.$channel_tracking_phone_string);                        
        
                                    if($total_unique_leads["data"] == $total_unique_leads_leadService["total"])
                                    {                
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_leads"]["status"] = "Success";
                                    }
                                    else
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_leads"]["status"] = "Error";
                                    }
        
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_leads"]["total_unique_leads_alpha"]       = $total_unique_leads["data"];
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["total_unique_leads"]["total_unique_leads_leadservice"] = $total_unique_leads_leadService["total"];
        
                                    /** Call Alpha API recent-all-leads*/
                                    $recent_leads = $this->call_alpha0("crm/recent-leads/all/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
        
                                    $count_calls = 0; 
                                    $count_forms = 0;
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"]["status"] = "Success";
        
                                    foreach($recent_leads["data"] as $keyRecent => $recent_lead)
                                    {
                                        if($recent_lead["type"] == "phone")
                                        {   
                                            /** On leadService not have recent-leads Api so we will find by query table*/
                                            $call = Call::select('phone', 'date')
                                                                ->whereIn('channel_id', $array_channel_call)
                                                                ->skip($count_calls)
                                                                ->orderBy('date', 'desc')
                                                                ->first();

                                            if(!empty($call))
                                            {
                                                $caller_phone_number = $recent_lead["client_crm_customer_calling"]["caller_phone_number"];
            
                                                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
                                                //$dt->setTimezone($this->timezone);
                                                $dateTimeSubmit = $dt->format(DateTime::ISO8601);
            
                                                if($caller_phone_number == $call->phone)
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["status"]                    = "Success";
                                                }
                                                else
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"]["status"]                                = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["status"]                    = "Error";
                                                }
                                                    
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["date_time_submit"]          = $dateTimeSubmit;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["recent_leads_alpha"]        = $caller_phone_number;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["recent_leads_leadservice"]  = $call->phone;
                                            }

                                            $count_calls++;
                                        }
                                        else
                                        {
                                            /** On leadService not have recent-leads Api so we will find by query table*/
                                            $form = Form::select('email', 'created_at_forms')
                                                                ->whereIn('channel_id', $array_channel_form)
                                                                ->skip($count_forms)
                                                                ->orderBy('created_at_forms', 'desc')
                                                                ->first();

                                            if(!empty($form))
                                            {                                                            
                                                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
                                                //$dt->setTimezone($this->timezone);
                                                $dateTimeSubmit = $dt->format(DateTime::ISO8601);
            
                                                $email = $recent_lead["email"];
            
                                                if($email == $form->email)
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["status"]                   = "Success";
                                                }
                                                else
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"]["status"]                               = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["status"]                   = "Error";
                                                }
                                                    
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["date_time_submit"]         = $dateTimeSubmit;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["recent_leads_alpha"]       = $email;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_all_leads"][$keyRecent]["recent_leads_leadservice"] = $form->email;        
                                            }

                                            $count_forms++;
                                        }
                                    }//end foreach recent all leads
        
                                    /** Call Alpha API recent-call-leads*/
                                    $recent_leads_phone = $this->call_alpha0("crm/recent-leads/calls/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
        
                                    $count_calls = 0; 
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"]["status"] = "Success";
        
                                    foreach($recent_leads_phone["data"] as $keyRecent => $recent_lead)
                                    {
                                        if($recent_lead["type"] == "phone")
                                        {   
                                            /** On leadService not have recent-leads Api so we will find by query table*/
                                            $call = Call::select('phone', 'date')
                                                                ->whereIn('channel_id', $array_channel_call)
                                                                ->skip($count_calls)
                                                                ->orderBy('date', 'desc')
                                                                ->first();

                                            if(!empty($call))
                                            {
                                                $caller_phone_number = $recent_lead["client_crm_customer_calling"]["caller_phone_number"];
            
                                                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
                                                //$dt->setTimezone($this->timezone);
                                                $dateTimeSubmit = $dt->format(DateTime::ISO8601);
            
                                                if($caller_phone_number == $call->phone)
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"][$keyRecent]["status"]                    = "Success";                                        
                                                }
                                                else
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"]["status"]                                = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"][$keyRecent]["status"]                    = "Error";
                                                }
            
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"][$keyRecent]["date_time_submit"]          = $dateTimeSubmit;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"][$keyRecent]["recent_leads_alpha"]        = $caller_phone_number;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_calls_leads"][$keyRecent]["recent_leads_leadservice"]  = $call->phone;
                                            }
        
                                            $count_calls++;
                                        }                        
                                    }//end foreach recent call leads
        
                                    /** Call Alpha API recent-forms-leads*/
                                    $recent_leads_form = $this->call_alpha0("crm/recent-leads/forms/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
        
                                    $count_forms = 0;
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"]["status"] = "Success";
        
                                    foreach($recent_leads_form["data"] as $keyRecent => $recent_lead)
                                    {
                                        if($recent_lead["type"] == "submitted")
                                        {
                                            /** On leadService not have recent-leads Api so we will find by query table*/
                                            $form = Form::select('email', 'created_at_forms')
                                                                ->whereIn('channel_id', $array_channel_form)
                                                                ->skip($count_forms)
                                                                ->orderBy('created_at_forms', 'desc')
                                                                ->first();
                                            if(!empty($form))
                                            {
                                                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
                                                //$dt->setTimezone($this->timezone);
                                                $dateTimeSubmit = $dt->format(DateTime::ISO8601);
            
                                                $email = $recent_lead["email"];
            
                                                if($email == $form->email)
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"][$keyRecent]["status"]                   = "Success";
                                                }
                                                else
                                                {
                                                    $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"]["status"]                               = "Error";
                                                    $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"][$keyRecent]["status"]                   = "Error";
                                                }
                                                    
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"][$keyRecent]["date_time_submit"]         = $dateTimeSubmit;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"][$keyRecent]["recent_leads_alpha"]       = $email;
                                                $array_response[$keyGetDataVerifyLeads]["widget"]["recent_forms_leads"][$keyRecent]["recent_leads_leadservice"] = $form->email;
                                            }
                                                                
                                            $count_forms++;
                                        }
                                    }//end foreach recent form leads
        
                                    /** Call Alpha Api contacts-over-time*/
                                    $contacts_over_times = $this->call_alpha0("crm/contacts-over-time/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                                    /** Call leadService 2 Api (Calls, Forms) contacts-over-time*/
                                    //$contacts_over_times_call_leadservice = $this->call_leadservice("getCalls2/byMonthYear/unique/daybyday?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string.$channel_tracking_phone_string);                        
                                    //$contacts_over_times_forms_leadservice = $this->call_leadservice("getForms2/byMonthYear/unique/daybyday?&startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string.$channel_tracking_phone_string);
                                        
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"]["status"] = "Success";
        
                                    $array_contacts_over_times_call_leadservice_summary  = array();
                                    $array_contacts_over_times_forms_leadservice_summary = array();
                                    $array_contacts_over_times_total_leadservice_summary = array();
        
                                    $contacts_over_times_call_leadservice_summary  = 0;
                                    $contacts_over_times_forms_leadservice_summary = 0;
                                    $contacts_over_times_total_leadservice_summary = 0;
                                        
                                    foreach($contacts_over_times["data"] as $keyContactsOverTimes => $contacts_over_time)
                                    {
                                        $contacts_over_time_date  = $contacts_over_time["name"]; 
                                        $contacts_over_time_calls = $contacts_over_time["value"]["calls"];
                                        $contacts_over_time_forms = $contacts_over_time["value"]["forms"];
        
                                        $dt = Carbon::parse($contacts_over_time_date. "-01 00:00:00");    
                                            
                                        $contacts_over_start_time_date = Carbon::createFromFormat('Y-m-d H:i:s', $dt, $this->local_timezone)->setTimezone($this->timezone);
                                            
                                        $contacts_over_end_time_date = $dt->endOfMonth();                            
                                        $contacts_over_end_time_date = Carbon::createFromFormat('Y-m-d H:i:s', $contacts_over_end_time_date, $this->local_timezone)->setTimezone($this->timezone);   
                                            
                                        $contacts_over_times_call_leadservice = Call::whereIn('channel_id', $array_channel_call)->whereBetween('date', [$contacts_over_start_time_date, $contacts_over_end_time_date])->count();
                                        $contacts_over_times_forms_leadservice = Form::whereIn('channel_id', $array_channel_form)->whereBetween('created_at_forms', [$contacts_over_start_time_date, $contacts_over_end_time_date])->count();
        
                                        if($contacts_over_time_calls == $contacts_over_times_call_leadservice)
                                        {                        
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["calls"]["status"]           = "Success";
                                        }
                                        else
                                        {                    
                                            $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"]["status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["calls"]["status"]           = "Error";
                                        }
                                            
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["calls"]["date"]             = $contacts_over_time_date;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["calls"]["calls_alpha"]       = $contacts_over_time_calls;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["calls"]["calls_leadservice"] = $contacts_over_times_call_leadservice;
        
                                        if($contacts_over_time_forms == $contacts_over_times_forms_leadservice)
                                        {
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["forms"]["status"]            = "Success";
                                        }
                                        else
                                        {                    
                                            $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"]["status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["forms"]["status"]            = "Error";
                                        }
                                            
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["forms"]["date"]              = $contacts_over_time_date;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["forms"]["forms_alpha"]        = $contacts_over_time_forms;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["daily_unique_leads"][$keyContactsOverTimes]["forms"]["forms_leadservice"]  = $contacts_over_times_forms_leadservice;
                                            
                                        $contacts_over_times_call_leadservice_summary  += $contacts_over_times_call_leadservice;
                                        $contacts_over_times_forms_leadservice_summary += $contacts_over_times_forms_leadservice;
                                        $contacts_over_times_total_leadservice_summary += $contacts_over_times_call_leadservice + $contacts_over_times_forms_leadservice;
                                            
                                        $array_contacts_over_times_call_leadservice_summary[$keyContactsOverTimes]  = $contacts_over_times_call_leadservice_summary;
                                        $array_contacts_over_times_forms_leadservice_summary[$keyContactsOverTimes] = $contacts_over_times_forms_leadservice_summary;  
                                        $array_contacts_over_times_total_leadservice_summary[$keyContactsOverTimes] = $contacts_over_times_total_leadservice_summary;               
                                    }
        
                                    /** Call Alpha Api contacts-over-time*/
                                    $contacts_to_date = $this->call_alpha0("crm/contacts-to-date/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                                        
                                    $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"]["status"] = "Success";
        
                                    foreach($contacts_to_date["data"] as $keyContactsToDate => $contacts_to_date)
                                    {                    
                                        $contacts_to_date_name  = $contacts_to_date["name"];
                                        $contacts_to_date_calls = $contacts_to_date["value"]["calls"];
                                        $contacts_to_date_forms = $contacts_to_date["value"]["forms"];
                                        $contacts_to_date_total = $contacts_to_date["value"]["total_leads"];
                                            
                                        if($contacts_to_date_calls == $array_contacts_over_times_call_leadservice_summary[$keyContactsToDate])
                                        {
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["calls"]["status"]           = "Success";
                                        }
                                        else
                                        {                    
                                            $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"]["status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["calls"]["status"]           = "Error";
                                        }
                                            
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["calls"]["date"]             = $contacts_to_date_name;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["calls"]["calls_alpha"]       = $contacts_to_date_calls;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["calls"]["calls_leadservice"] = $array_contacts_over_times_call_leadservice_summary[$keyContactsToDate];
        
                                        if($contacts_to_date_forms == $array_contacts_over_times_forms_leadservice_summary[$keyContactsToDate])
                                        {
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["forms"]["status"]            = "Success";
                                        }
                                        else
                                        {                    
                                            $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"]["status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["forms"]["status"]            = "Error";
                                        }
                                            
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["forms"]["date"]              = $contacts_to_date_name;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["forms"]["forms_alpha"]        = $contacts_to_date_forms;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["forms"]["forms_leadservice"]  = $array_contacts_over_times_forms_leadservice_summary[$keyContactsToDate];
        
                                        if($contacts_to_date_total == $array_contacts_over_times_total_leadservice_summary[$keyContactsToDate])
                                        {
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["total_leads"]["status"]                   = "Success";
                                        }
                                        else
                                        {                    
                                            $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"]["status"] = "Error";
                                            $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["total_leads"]["status"]                   = "Error";
                                        }
                                            
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["total_leads"]["date"]                     = $contacts_to_date_name;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["total_leads"]["total_leads_alpha"]        = $contacts_to_date_total;
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["accumulated_daily_unique_leads"][$keyContactsToDate]["total_leads"]["total_leads_leadservice"]  = $array_contacts_over_times_total_leadservice_summary[$keyContactsToDate];
                                    }
                                        
                                    /** Call Alpha Api list small Crm*/
                                    $list_small_crm_alpha = $this->call_alpha0("crm/list/".$campaignId."?start_date=".$date_range["startDate"]."%2000%3A00%3A00&end_date=".$date_range["endDate"]."%2023%3A59%3A59&page=0&perPage=1&types%5B0%5D=submitted&types%5B1%5D=answer&types%5B2%5D=missed-call");
        
                                    if($total_unique_leads_leadService["total"] == $list_small_crm_alpha["data"]["total"])
                                    {                
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["leads_management"]["status"]        = "Success";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["leads_management"]["all_leads_alpha"]         = $list_small_crm_alpha["data"]["total"];
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["leads_management"]["all_leads_leadservice"]   = $total_unique_leads_leadService["total"];
                                    }
                                    else
                                    {
                                        $array_response[$keyGetDataVerifyLeads]["lead_status"] = "Error";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["leads_management"]["status"]        = "Error";
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["leads_management"]["all_leads_alpha"]         = $list_small_crm_alpha["data"]["total"];
                                        $array_response[$keyGetDataVerifyLeads]["widget"]["leads_management"]["all_leads_leadservice"]   = $total_unique_leads_leadService["total"];
                                    }    
                                }
                            }
                        }                                                                       
                    }
                }
                
            }             
        }

        if(!empty($array_response))
        {
            $this->send_verify_leads_every_campaign_email($array_response);
        }

        return json_encode($array_response);
        
        //request campaign_id
        //request startdate enddate https://alpha1.heroleads.co.th/api/crm/get-date-range/5bee2b26905b817d024362bd
        //check Total Unique Called https://alpha1.heroleads.co.th/api/crm/widget/total-called/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Total Missed Calls https://alpha1.heroleads.co.th/api/crm/widget/total-missed-calls/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Total Unique Forms Submitted https://alpha1.heroleads.co.th/api/crm/widget/total-form-submitted/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Total Unique Leads (Forms + Called) https://alpha1.heroleads.co.th/api/crm/widget/total-unique-leads/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Recent All Leads https://alpha1.heroleads.co.th/api/crm/recent-leads/all/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Recent Call Leads https://alpha1.heroleads.co.th/api/crm/recent-leads/calls/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Recent Form Leads https://alpha1.heroleads.co.th/api/crm/recent-leads/forms/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Daily Unique Leads https://alpha1.heroleads.co.th/api/crm/contacts-over-time/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Accumulated Daily Unique Leads https://alpha1.heroleads.co.th/api/crm/contacts-to-date/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check leads management https://alpha1.heroleads.co.th/api/crm/list/5bee2b26905b817d024362bd?start_date=2018-01-19%2000%3A00%3A00&end_date=2019-01-18%2023%3A59%3A59&page=0&perPage=25&types%5B0%5D=submitted&types%5B1%5D=answer&types%5B2%5D=missed-call

        //check This is how people discovered you. https://alpha1.heroleads.co.th/api/crm/how-people-discovered-you/5bee2b26905b817d024362bd/2018-01-19/2019-01-18                
    }

    public function call_alpha0($api)
    {
        $url = env("ALPHA_API_ZERO").$api;
                  
        // create curl resource 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'secretKey: '.env("APP_SECRET_KEY_ALPHA0"),
            'Authorization: Bearer '.env("TOTKEN_ALPHA0")
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch); 

        if($info == "200" || $info == "201")
        {
            $output = json_decode($output, true);
            return $output;
        }

    }
    
    public function call_alpha($api)
    {
        $url = env("ALPHA_API").$api;
                  
        // create curl resource 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'secretKey: '.env("APP_SECRET_KEY_ALPHA0"),
            'Authorization: Bearer '.env("TOTKEN_ALPHA0")
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch); 

        if($info == "200" || $info == "201")
        {
            $output = json_decode($output, true);
            return $output;
        }
    }
        
    public function call_leadservice($api)
    {
        $url = env("LEADSERVICE_API").$api;
                  
        // create curl resource 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch); 

        if($info == "200" || $info == "201")
        {
            $output = json_decode($output, true);
            return $output;
        }
    }

    public function send_verify_leads_every_campaign_email($data)
    {
        $data = json_encode($data);
        
        $ch = curl_init(env("MESSAGE_API").$this->send_mail_api);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");             
        curl_setopt($ch, CURLOPT_VERBOSE, true);    
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data))
        );     
        $response = curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);   
    }    
}
