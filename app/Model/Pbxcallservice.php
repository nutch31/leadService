<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class pbxcallservice extends Model
{
    protected $table = "pbxcallservice";
    protected $fillable = [
        'response', 
        'status',
        'call_id_leadservice',
        'created_at', 
        'updated_at'
    ];
     
    /*
    public function channel() {
        return $this->belongsTo('App\Model\Channel');
    }
    */
}