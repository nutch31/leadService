<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogUpdateLeads extends Model
{
    protected $table = "log_update_leads";
    protected $fillable = [
        'type',
        'request',  
        'response',
        'status',
        'leadservice_id_existing',
        'leadservice_id_insert',
        'created_at', 
        'updated_at'
    ];
    
    /*
    public function channel() {
        return $this->belongsTo('App\Model\Channel');
    }
    */
}