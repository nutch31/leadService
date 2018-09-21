<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $table = "calls";
    protected $fillable = [
        'call_id', 
        'date', 
        'duration', 
        'recording_url', 
        'status', 
        'phone', 
        'channel_id', 
        'is_duplicated', 
        'location',
        'created_at_calls', 
        'updated_at_calls', 
        'client_number', 
        'call_uuid', 
        'call_mapped', 
        'created_at', 
        'updated_at'
    ];
    
    public function channel() {
        return $this->belongsTo('App\Model\Channel');
    }
}