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

class VerifyLeadsEveryCampaign extends BaseController
{    
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->local_timezone = 'Asia/Bangkok';
    }
    
    public function verifyLeadsEveryCampaign()
    {   
        $array_response = array();

        $accounts = Account::select( 
                                    'account_id',
                                    'company_name', 
                                    'adwords_account_id', 
                                    'facebook_account_id'
                                    )
                                    ->where('status', '=', 'active')
                                    ->where(function($q){
                                        $q->where('facebook_account_id', '!=', '')->orWhere('adwords_account_id', '!=', '');
                                    })
                                    ->whereIn('account_id', array('89'))
                                    ->orderBy('id', 'asc')
                                    ->get();

        foreach($accounts as $keyAccount => $account)
        {   
            $array_response[$keyAccount]["campaign_name"] = $account->company_name;
            $array_response[$keyAccount]["status"] = "Success";

            /** Find Campaign */
            $campaigns = Campaign::select('campaign_id')
                                    ->where('account_id', '=', $account->account_id)
                                    ->orderBy('account_id', 'asc')
                                    ->get();

            $array_campaign = array();

            foreach($campaigns as $keyCampaign => $campaign)
            {
                $array_campaign[$keyCampaign] = $campaign->campaign_id;
            }

            /** Find Channel */
            $channel_calls = Channel::select('channel_id', 'tracking_phone')
                                    ->whereIn('campaign_id', $array_campaign)
                                    ->where('tracking_phone', '!=', '')
                                    ->orderBy('channel_id', 'asc')
                                    ->get();
            
            /** Find Channel Call */
            $array_channel_call             = array();
            $array_channel_tracking_phone   = array();
            $channel_tracking_phone_string  = "";

            foreach($channel_calls as $keyChannelCall => $channel_call)
            {
                $array_channel_call[$keyChannelCall]            = $channel_call->channel_id;
                $array_channel_tracking_phone[$keyChannelCall]  = $channel_call->tracking_phone;
                $channel_tracking_phone_string                  .= "&didPhone[]=".$channel_call->tracking_phone;
            }

            $channel_forms = Channel::select('channel_id', 'facebook_campaign_id', 'adwords_campaign_id')
                                    ->whereIn('campaign_id', $array_campaign)
                                    ->where('tracking_phone', '=', '')
                                    ->where(function($q){
                                        $q->where('facebook_campaign_id', '!=', '')->orWhere('adwords_campaign_id', '!=', '');
                                    })
                                    ->orderBy('channel_id', 'asc')
                                    ->get();

            /** Find Channel Form */
            $array_channel_form                     = array();
            $array_channel_analytic_campaign_id     = array();
            $array_channel_analytic_campaign_id_total = array();
            $channel_analytic_campaign_id_string    = "";
            
            foreach($channel_forms as $keyChannelForm => $channel_form)
            {
                $array_channel_form[$keyChannelForm] = $channel_form->channel_id;

                if($channel_form->facebook_campaign_id != "")
                {
                    $array_channel_analytic_campaign_id[$keyChannelForm] = $channel_form->facebook_campaign_id;
                    $array_channel_analytic_campaign_id_total[$keyChannelForm] = $channel_form->facebook_campaign_id;
                    $channel_analytic_campaign_id_string .= "&analyticCampaignId[]=".$channel_form->facebook_campaign_id;
                }
                else if($channel_form->adwords_campaign_id != "")
                {
                    $array_channel_analytic_campaign_id[$keyChannelForm] = $channel_form->adwords_campaign_id;
                    $array_channel_analytic_campaign_id_total[$keyChannelForm] = $channel_form->adwords_campaign_id;
                    $channel_analytic_campaign_id_string .= "&analyticCampaignId[]=".$channel_form->adwords_campaign_id;
                }
                else
                {
                    $array_channel_analytic_campaign_id[$keyChannelForm] = $channel_form->channel_id;
                    $array_channel_analytic_campaign_id_total[$keyChannelForm] = $channel_form->channel_id;
                    $channel_analytic_campaign_id_string .= "&channelId[]=".$channel_form->channel_id;
                }                
            }
            
            /** Call Alpha API Find CampaignId */
            $facebook_account_id = $account->facebook_account_id;
            $adwords_account_id  = $account->adwords_account_id;

            if($facebook_account_id == "")
            {   
                $facebook_account_id = "null";
            }

            if($adwords_account_id == "")
            {   
                $adwords_account_id = "null";
            }

            $get_campaign_ids_by_account_id = $this->call_alpha0("get-campaign-ids-by-account-id?google_account_id=".$adwords_account_id."&facebook_account_id=".$facebook_account_id);

            $count_get_campaign_ids_by_account_id = count($get_campaign_ids_by_account_id["data"]);

            /** if == 1 have 1 campaign */
            if($count_get_campaign_ids_by_account_id > 0)
            {
                $campaignId = $get_campaign_ids_by_account_id["data"][0];
                    
                //$get_aci_didphone = $this->call_alpha0("campaign/get-aci-didphone/".$campaignId);

                $get_aci_didphone = array(
                    "analytic_campaign_id" => array(
                        '882652958','23842959770300379','906387271','909716143','903321083','881199553','911385987','881420614'
                    ),
                    "did_phone" => array(
                        '21068465'
                    )
                );

                $result_analytic_campaign_id = array_diff($array_channel_analytic_campaign_id_total, $get_aci_didphone["analytic_campaign_id"]);
                $result_did_phone = array_diff($array_channel_tracking_phone, $get_aci_didphone["did_phone"]); 
                
                $array_response[$keyAccount]["remark_forms_error"] = array_values($result_analytic_campaign_id);    
                $array_response[$keyAccount]["remark_calls_error"] = array_values($result_did_phone);              

                /** Call Alpha API Find StartDate, EndDate CampaignId */
                $date_range = $this->call_alpha("crm/get-date-range/".$campaignId);

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
                $total_called_alpha       = $this->call_alpha("crm/widget/total-called/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                /** Call leadService API total-called*/
                $total_called_leadService = $this->call_leadservice("getCalls2/byPeriod/unique?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_tracking_phone_string);            

                if($total_called_alpha["data"] == $total_called_leadService["totalUniqueCalls"])
                {                
                    $array_response[$keyAccount]["total_called"]["status"] = "Success";
                }
                else
                {
                    $array_response[$keyAccount]["status"] = "Error";
                    $array_response[$keyAccount]["total_called"]["status"] = "Error";
                }
                
                $array_response[$keyAccount]["total_called"]["total_called_alpha"]       = $total_called_alpha["data"];
                $array_response[$keyAccount]["total_called"]["total_called_leadservice"] = $total_called_leadService["totalUniqueCalls"];

                /** Call Alpha API total-missed-calls*/
                $total_missed_calls_alpha = $this->call_alpha("crm/widget/total-missed-calls/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                /** Call leadService API total-missed-calls*/
                $total_missed_calls_leadService = $this->call_leadservice("getCalls2/byPeriod/unique?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_tracking_phone_string."&status=2");            

                if($total_missed_calls_alpha["data"] == $total_missed_calls_leadService["totalUniqueCalls"])
                {                
                    $array_response[$keyAccount]["total_missed_calls"]["status"] = "Success";
                }
                else
                {
                    $array_response[$keyAccount]["status"] = "Error";
                    $array_response[$keyAccount]["total_missed_calls"]["status"] = "Error";
                }
                
                $array_response[$keyAccount]["total_missed_calls"]["total_missed_calls_alpha"]       = $total_missed_calls_alpha["data"];
                $array_response[$keyAccount]["total_missed_calls"]["total_missed_calls_leadservice"] = $total_missed_calls_leadService["totalUniqueCalls"];
                                
                /** Call Alpha API total-missed-calls*/
                $total_form_submitted = $this->call_alpha("crm/widget/total-form-submitted/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                /** Call leadService API total-missed-calls*/
                $total_form_submitted_leadService = $this->call_leadservice("getForms2/byPeriod/unique?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string);            

                if($total_form_submitted["data"] == $total_form_submitted_leadService["totalUniqueCalls"])
                {                
                    $array_response[$keyAccount]["total_form_submitted"]["status"] = "Success";
                }
                else
                {
                    $array_response[$keyAccount]["status"] = "Error";
                    $array_response[$keyAccount]["total_form_submitted"]["status"] = "Error";
                }
                
                $array_response[$keyAccount]["total_form_submitted"]["total_form_submitted_alpha"]       = $total_form_submitted["data"];
                $array_response[$keyAccount]["total_form_submitted"]["total_form_submitted_leadservice"] = $total_form_submitted_leadService["totalUniqueCalls"];

                /** Call Alpha API total-missed-calls*/
                $total_unique_leads = $this->call_alpha("crm/widget/total-unique-leads/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                /** Call leadService API total-missed-calls*/
                $total_unique_leads_leadService = $this->call_leadservice("getCallsForms/byPeriodDidPhoneAnalyticCampaignId/?page=1&limit=1&is_duplicated=0&startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string.$channel_tracking_phone_string);                        

                if($total_unique_leads["data"] == $total_unique_leads_leadService["total"])
                {                
                    $array_response[$keyAccount]["total_unique_leads"]["status"] = "Success";
                }
                else
                {
                    $array_response[$keyAccount]["status"] = "Error";
                    $array_response[$keyAccount]["total_unique_leads"]["status"] = "Error";
                }

                $array_response[$keyAccount]["total_unique_leads"]["total_unique_leads_alpha"]       = $total_unique_leads["data"];
                $array_response[$keyAccount]["total_unique_leads"]["total_unique_leads_leadservice"] = $total_unique_leads_leadService["total"];

                /** Call Alpha API recent-leads*/
                $recent_leads = $this->call_alpha("crm/recent-leads/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);

                $count_calls = 0; 
                $count_forms = 0;
                $array_response[$keyAccount]["recent_leads"]["status"] = "Success";

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

                        $caller_phone_number = $recent_lead["client_crm_customer_calling"]["caller_phone_number"];

                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
                        //$dt->setTimezone($this->timezone);
                        $dateTimeSubmit = $dt->format(DateTime::ISO8601);

                        if($caller_phone_number == $call->phone)
                        {
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["status"]                    = "Success";
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["date_time_submit"]          = $dateTimeSubmit;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_alpha"]        = $caller_phone_number;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_leadservice"]  = $call->phone;
                        }
                        else
                        {
                            $array_response[$keyAccount]["status"] = "Error";
                            $array_response[$keyAccount]["recent_leads"]["status"]                                = "Error";
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["status"]                    = "Error";
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["date_time_submit"]          = $dateTimeSubmit;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_alpha"]        = $caller_phone_number;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_leadservice"]  = $call->phone;
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

                                        
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
                        //$dt->setTimezone($this->timezone);
                        $dateTimeSubmit = $dt->format(DateTime::ISO8601);

                        $email = $recent_lead["email"];

                        if($email == $form->email)
                        {
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["status"]                   = "Success";
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["date_time_submit"]         = $dateTimeSubmit;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_alpha"]       = $email;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_leadservice"] = $form->email;
                        }
                        else
                        {
                            $array_response[$keyAccount]["status"] = "Error";
                            $array_response[$keyAccount]["recent_leads"]["status"]                               = "Error";
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["status"]                   = "Error";
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["date_time_submit"]         = $dateTimeSubmit;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_alpha"]       = $email;
                            $array_response[$keyAccount]["recent_leads"][$keyRecent]["recent_leads_leadservice"] = $form->email;
                        }

                        $count_forms++;
                    }
                }//end foreach recent leads

                /** Call Alpha Api contacts-over-time*/
                $contacts_over_times = $this->call_alpha("crm/contacts-over-time/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                /** Call leadService 2 Api (Calls, Forms) contacts-over-time*/
                $contacts_over_times_call_leadservice = $this->call_leadservice("getCalls2/byPeriod/unique/daybyday?startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string.$channel_tracking_phone_string);                        
                $contacts_over_times_forms_leadservice = $this->call_leadservice("getForms2/byPeriod/unique/daybyday?&startDateTime=".$startDateSearch."&endDateTime=".$endDateSearch.$channel_analytic_campaign_id_string.$channel_tracking_phone_string);
                
                $array_response[$keyAccount]["contacts_over_time"]["status"] = "Success";

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

                    if($contacts_over_time_calls == $contacts_over_times_call_leadservice[$keyContactsOverTimes]["totalUniqueCalls"])
                    {                        
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["status"]           = "Success";
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["date"]             = $contacts_over_time_date;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["call_alpha"]       = $contacts_over_time_calls;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["call_leadservice"] = $contacts_over_times_call_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];

                    }
                    else
                    {                    
                        $array_response[$keyAccount]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_over_time"]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["status"]           = "Error";
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["date"]             = $contacts_over_time_date;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["call_alpha"]       = $contacts_over_time_calls;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["calls"]["call_leadservice"] = $contacts_over_times_call_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];
                    }

                    if($contacts_over_time_forms == $contacts_over_times_forms_leadservice[$keyContactsOverTimes]["totalUniqueCalls"])
                    {
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["status"]            = "Success";
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["date"]              = $contacts_over_time_date;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["form_alpha"]        = $contacts_over_time_forms;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["form_leadservice"]  = $contacts_over_times_forms_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];

                    }
                    else
                    {                    
                        $array_response[$keyAccount]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_over_time"]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["status"]            = "Error";
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["date"]              = $contacts_over_time_date;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["form_alpha"]        = $contacts_over_time_forms;
                        $array_response[$keyAccount]["contacts_over_time"][$keyContactsOverTimes]["forms"]["form_leadservice"]  = $contacts_over_times_forms_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];
                    }
                    
                    $contacts_over_times_call_leadservice_summary  += $contacts_over_times_call_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];
                    $contacts_over_times_forms_leadservice_summary += $contacts_over_times_forms_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];
                    $contacts_over_times_total_leadservice_summary += $contacts_over_times_call_leadservice[$keyContactsOverTimes]["totalUniqueCalls"] + $contacts_over_times_forms_leadservice[$keyContactsOverTimes]["totalUniqueCalls"];
                    
                    $array_contacts_over_times_call_leadservice_summary[$keyContactsOverTimes]  = $contacts_over_times_call_leadservice_summary;
                    $array_contacts_over_times_forms_leadservice_summary[$keyContactsOverTimes] = $contacts_over_times_forms_leadservice_summary;  
                    $array_contacts_over_times_total_leadservice_summary[$keyContactsOverTimes] = $contacts_over_times_total_leadservice_summary;               
                }

                /** Call Alpha Api contacts-over-time*/
                $contacts_to_date = $this->call_alpha("crm/contacts-to-date/".$campaignId."/".$date_range["startDate"]."/".$date_range["endDate"]);
                
                $array_response[$keyAccount]["contacts_to_date"]["status"] = "Success";

                foreach($contacts_to_date["data"] as $keyContactsToDate => $contacts_to_date)
                {                    
                    $contacts_to_date_name       = $contacts_to_date["name"];
                    $contacts_to_date_calls = $contacts_to_date["value"]["calls"];
                    $contacts_to_date_forms = $contacts_to_date["value"]["forms"];
                    $contacts_to_date_total = $contacts_to_date["value"]["total_leads"];
                    
                    if($contacts_to_date_calls == $array_contacts_over_times_call_leadservice_summary[$keyContactsToDate])
                    {
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["status"]           = "Success";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["date"]             = $contacts_to_date_name;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["call_alpha"]       = $contacts_to_date_calls;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["call_leadservice"] = $array_contacts_over_times_call_leadservice_summary[$keyContactsToDate];

                    }
                    else
                    {                    
                        $array_response[$keyAccount]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_to_date"]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["status"]           = "Error";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["date"]             = $contacts_to_date_name;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["call_alpha"]       = $contacts_to_date_calls;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["calls"]["call_leadservice"] = $array_contacts_over_times_call_leadservice_summary[$keyContactsToDate];
                    }

                    if($contacts_to_date_forms == $array_contacts_over_times_forms_leadservice_summary[$keyContactsToDate])
                    {
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["status"]            = "Success";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["date"]             = $contacts_to_date_name;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["form_alpha"]        = $contacts_to_date_forms;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["form_leadservice"]  = $array_contacts_over_times_forms_leadservice_summary[$keyContactsToDate];

                    }
                    else
                    {                    
                        $array_response[$keyAccount]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_to_date"]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["status"]            = "Error";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["date"]              = $contacts_to_date_name;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["form_alpha"]        = $contacts_to_date_forms;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["forms"]["form_leadservice"]  = $array_contacts_over_times_forms_leadservice_summary[$keyContactsToDate];
                    }

                    if($contacts_to_date_total == $array_contacts_over_times_total_leadservice_summary[$keyContactsToDate])
                    {
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["status"]                   = "Success";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["date"]                     = $contacts_to_date_name;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["total_leads_alpha"]        = $contacts_to_date_total;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["total_leads_leadservice"]  = $array_contacts_over_times_total_leadservice_summary[$keyContactsToDate];

                    }
                    else
                    {                    
                        $array_response[$keyAccount]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_to_date"]["status"] = "Error";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["status"]                   = "Error";
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["date"]                     = $contacts_to_date_name;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["total_leads_alpha"]        = $contacts_to_date_total;
                        $array_response[$keyAccount]["contacts_to_date"][$keyContactsToDate]["total_leads"]["total_leads_leadservice"]  = $array_contacts_over_times_total_leadservice_summary[$keyContactsToDate];
                    }
                }
                
                /** Call Alpha Api list small Crm*/
                $list_small_crm_alpha = $this->call_alpha("crm/list/".$campaignId."?start_date=".$date_range["startDate"]."%2000%3A00%3A00&end_date=".$date_range["endDate"]."%2023%3A59%3A59&page=0&perPage=1&types%5B0%5D=submitted&types%5B1%5D=answer&types%5B2%5D=missed-call");

                if($total_unique_leads_leadService["total"] == $list_small_crm_alpha["data"]["total"])
                {                
                    $array_response[$keyAccount]["list_small_crm"]["status"]        = "Success";
                    $array_response[$keyAccount]["list_small_crm"]["alpha"]         = $list_small_crm_alpha["data"]["total"];
                    $array_response[$keyAccount]["list_small_crm"]["leadservice"]   = $total_unique_leads_leadService["total"];
                }
                else
                {
                    $array_response[$keyAccount]["status"] = "Error";
                    $array_response[$keyAccount]["list_small_crm"]["status"]        = "Error";
                    $array_response[$keyAccount]["list_small_crm"]["alpha"]         = $list_small_crm_alpha["data"]["total"];
                    $array_response[$keyAccount]["list_small_crm"]["leadservice"]   = $total_unique_leads_leadService["total"];
                }                
            }
            else
            {
                $array_response[$keyAccount]["status"] = "Error";
                $array_response[$keyAccount]["remark_error"] = "Not have campaignId on alpha";
            }            
        }

        return $array_response;
        
        //request campaign_id
        //request startdate enddate https://alpha1.heroleads.co.th/api/crm/get-date-range/5bee2b26905b817d024362bd
        //check Total Unique Called https://alpha1.heroleads.co.th/api/crm/widget/total-called/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Total Missed Calls https://alpha1.heroleads.co.th/api/crm/widget/total-missed-calls/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Total Unique Forms Submitted https://alpha1.heroleads.co.th/api/crm/widget/total-form-submitted/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Total Unique Leads (Forms + Called) https://alpha1.heroleads.co.th/api/crm/widget/total-unique-leads/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Recent Leads https://alpha1.heroleads.co.th/api/crm/recent-leads/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Daily Unique Leads https://alpha1.heroleads.co.th/api/crm/contacts-over-time/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check Accumulated Daily Unique Leads https://alpha1.heroleads.co.th/api/crm/contacts-to-date/5bee2b26905b817d024362bd/2018-01-19/2019-01-18
        //check leads management https://alpha1.heroleads.co.th/api/crm/list/5bee2b26905b817d024362bd?start_date=2018-01-19%2000%3A00%3A00&end_date=2019-01-18%2023%3A59%3A59&page=0&perPage=25&types%5B0%5D=submitted&types%5B1%5D=answer&types%5B2%5D=missed-call

        //check This is how people discovered you. https://alpha1.heroleads.co.th/api/crm/how-people-discovered-you/5bee2b26905b817d024362bd/2018-01-19/2019-01-18                
    }

    public function call_alpha0($api)
    {
        $url = env("ALPHA_API_ZERO").$api;
        $token = env("TOKEN_ALPHA");
                  
        // create curl resource 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$token
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
        $token = env("TOKEN_ALPHA");
                  
        // create curl resource 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$token
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
}
