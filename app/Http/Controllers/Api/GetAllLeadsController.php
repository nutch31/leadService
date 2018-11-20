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

class GetAllLeadsController extends BaseController
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
        $this->start_date = '2016-10-01 00:00:00';
        $this->end_date = date("Y-m-d");
    }

    /**
    * Get All Lead Forms
    *
    * @param optional page, limit
    */
    public function getAllLeadsByMonth()
    {
        $response = $this->Response_getAllLeads();
        return response($response, '200');
    }
    
    public function Response_getAllLeads()
    {              
        $response = array();
        
        $start_date = $this->start_date;

        $DiffInMonths = Carbon::parse($this->start_date)->DiffInMonths($this->end_date);

        for($x=0;$x<=$DiffInMonths;$x++)
        {
            $array_datetime = explode(" ", $start_date);
            $array_date = explode("-", $array_datetime[0]);

            $dt = Carbon::create($array_date[0], $array_date[1], $array_date[2], 0);            
            $dt->startOfMonth();
            $startDateTime = $dt->subHours(7);

            $dt = Carbon::create($array_date[0], $array_date[1], $array_date[2], 0, 0);            
            $dt->endOfMonth();  
            $endDateTime = $dt->subHours(7);    
            
            $dt = Carbon::create($array_date[0], $array_date[1], $array_date[2], 0);
            $start_date = $dt->addMonths(1);                                    

            $totalLeadSubmit = DB::table('forms')  
                        ->whereBetween('forms.created_at_forms', [$startDateTime, $endDateTime])
                        ->count();    
    
            $totalLeadCall = DB::table('calls')  
                        ->whereBetween('calls.created_at_calls', [$startDateTime, $endDateTime])
                        ->count();    
    
            $totalLead = $totalLeadSubmit + $totalLeadCall;
                        
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $startDateTime);
            //$dt->setTimezone($this->timezone);
            $StartDateTime = $dt->format(DateTime::ISO8601);   
                
            $dt2 = Carbon::createFromFormat('Y-m-d H:i:s', $endDateTime);
            //$dt2->setTimezone($this->timezone);
            $EndDateTime = $dt2->format(DateTime::ISO8601); 
    
            $response[$x]['fromDateTime'] = "$StartDateTime";
            $response[$x]['totalLeadSubmit'] = "$totalLeadSubmit";
            $response[$x]['totalLeadCall'] = "$totalLeadCall";
            $response[$x]['totalLead'] = "$totalLead";
            $response[$x]['toDateTime'] = "$EndDateTime";
        }
        
        $response = json_encode($response);
        return $response;

    }
}
