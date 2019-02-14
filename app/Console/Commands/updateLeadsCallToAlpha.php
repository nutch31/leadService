<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use App\Model\Call;
use App\Model\Channel;
use App\Model\LogUpdateLeads;

class updateLeadsCallToAlpha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateLeadsCallToAlpha';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Leads Call To Alpha';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->type = "phone";
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channels = Channel::select('channel_id', 'tracking_phone')
                                ->where('tracking_phone', '!=', '')
                                ->orderBy('id', 'desc')
                                ->get();

        foreach($channels as $channel)
        {
            $leads = Call::where('channel_id', '=', $channel->channel_id)->count();

            if($leads > 0)
            {
                $arr = array(
                    'type' => $this->type,
                    'did_phone' => $channel->tracking_phone,
                    'leads' => $leads
                );
                $val = json_encode($arr);
                
                $url = env("ALPHA_API");
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

                    $calls = Call::select('id', 'date', 'duration', 'phone', 'status', 'recording_url', 'is_duplicated', 'parent_id_duplicated')
                                    ->where('channel_id', '=', $channel->channel_id)
                                    ->whereNotIn('id', $response["lead_service_ids"])
                                    ->orderBy('id', 'desc')
                                    ->get();

                    foreach($calls as $callKey => $call)
                    {                                    
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $call->date);
                        $dt->setTimezone($this->timezone);
                        $submitted_date_time = $dt->format(DateTime::ISO8601);

                        if($call->is_duplicated == 0)
                        {
                            $is_duplicated = false;
                        }
                        else
                        {
                            $is_duplicated = true;
                        }

                        $param = app()->make('App\Http\Controllers\Api\PbxCallServiceController');
                        $param->call_alpha(
                            $channel->channel_id, 
                            $submitted_date_time, 
                            $call->phone, 
                            $call->status, 
                            "Incoming", 
                            $call->recording_url, 
                            Null, 
                            $call->id, 
                            $is_duplicated, 
                            $call->parent_id_duplicated, 
                            $call->duration, 
                            $channel->tracking_phone
                        );

                        $array_leadservice_id[$callKey] = $call->id;
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
