<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Form;
use App\Model\Channel;
use App\Model\LogUpdateLeads;

class updateLeadsFormToAlpha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateLeadsFormToAlpha';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Leads Form To Alpha';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->type = "submitted";
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channels = Channel::select('adwords_campaign_id', 'facebook_campaign_id', 'channel_id', 'kind')
                                ->where('facebook_campaign_id', '!=', '')
                                ->orWhere('adwords_campaign_id', '!=', '')
                                ->orderBy('id', 'desc')
                                ->get();
         
        foreach($channels as $channel)
        {
            $leads = Form::where('channel_id', '=', $channel->channel_id)->count();

            if($leads > 0)
            {
                if(!empty($channel->adwords_campaign_id))
                {
                    $analytic_campaign_id = $channel->adwords_campaign_id;
                }
                else
                {
                    $analytic_campaign_id = $channel->facebook_campaign_id;
                }

                $arr = array(
                    'type' => $this->type,
                    'analytic_campaign_id' => $analytic_campaign_id,
                    'leads' => $leads
                );
                $val = json_encode($arr);
                
                $url = env("ALPHA_API_TEST");
                $url .= "checking-leads-data";

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        
                curl_setopt($ch, CURLOPT_VERBOSE, true);
        
                curl_setopt($ch, CURLOPT_POSTFIELDS, $val);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($val))
                );     
                $response_json = curl_exec($ch);
                $info = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
                curl_close($ch);

                $response = json_decode($response_json, true);

                $array_leadservice_id = array();
                        
                if($response["status"] == "not equal")
                {                                        
                    $forms = Form::select('id', 'channel_id', 'name', 'phone', 'email', 'is_duplicated', 'parent_id_duplicated', 'custom_attributes', 'created_at_forms')
                                    ->where('channel_id', '=', $channel->channel_id)
                                    ->whereNotIn('id', $response["lead_service_ids"])
                                    ->orderBy('id', 'desc')
                                    ->get();

                    foreach($forms as $formKey => $form)
                    {                                    
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $form->created_at_forms);
                        $dt->setTimezone($this->timezone);
                        $submitted_date_time = $dt->format(DateTime::ISO8601); 

                        $param = app()->make('App\Http\Controllers\Api\LandingPageCallServiceController');
                        $param->call_alpha_test(
                            $channel->channel_id, 
                            $form->name, 
                            $form->phone, 
                            $form->email, 
                            $submitted_date_time, 
                            Null, 
                            $form->id, 
                            $form->is_duplicated, 
                            $form->parent_id_duplicated, 
                            $form->custom_attributes,                             
                            $channel->kind
                        );
                        
                        $array_leadservice_id[$formKey] = $form->id;
                    }       
                }
                                            
                $LogUpdateLeads = new LogUpdateLeads;
                $LogUpdateLeads->type = $this->type;
                $LogUpdateLeads->request = $val;
                $LogUpdateLeads->response = $response_json;
                $LogUpdateLeads->status = $response["status"];
                $LogUpdateLeads->leadservice_id_existing = json_encode($response["lead_service_ids"]);
                $LogUpdateLeads->leadservice_id_insert = json_encode($array_leadservice_id);
                $LogUpdateLeads->save();
            }
        }
        
        return response(array(
            'Status' => 'Success'
        ), '200');
    }
}
