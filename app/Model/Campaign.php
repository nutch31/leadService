<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = "campaigns";
    protected $fillable = [
        'campaign_id', 
        'name', 
        'status', 
        'start_date', 
        'end_date', 
        'account_id', 
        'created_at_campaigns', 
        'updated_at_campaigns'
    ];

    public function account() {
        return $this->belongsTo('App\Model\Account');
    }

    public function channels(){
        return $this->hasMany('App\Model\Channel', 'campaign_id', 'campaign_id');
    }
}