<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log_call extends Model
{
    protected $table = "log_calls";
    protected $fillable = [
        'call_id', 
        'call_id_herobase', 
        'date', 
        'duration', 
        'recording_url', 
        'status', 
        'phone', 
        'channel_id',
        'is_duplicated',
        'parent_id_duplicated',
        'location',
        'created_at_calls_herobase',
        'updated_at_calls_herobase',
        'client_number',
        'call_uuid',
        'call_mapped',
        'created_at_calls',
        'updated_at_calls',
        'user_id', 
        'created_at', 
        'updated_at'
    ];
    
    /*
    public function channel() {
        return $this->belongsTo('App\Model\Channel');
    }
    */
}