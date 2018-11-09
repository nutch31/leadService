<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class pbxcallservice extends Model
{
    protected $table = "pbxcallservice";
    protected $fillable = [
        'response', 
        'status',
        'status_pbx',
        'status_alpha',
        'request_alpha',
        'call_id_leadservice',
        'created_at', 
        'updated_at'
    ];
}