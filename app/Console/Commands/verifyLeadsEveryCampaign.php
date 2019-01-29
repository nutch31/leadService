<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;

class verifyLeadsEveryCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:verifyLeadsEveryCampaign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Leads Every Campaign';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';
        $this->type = "direct";
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        
        $url = env("LEADSERVICE_API")."verifyLeadsEveryCampaign";
                  
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
