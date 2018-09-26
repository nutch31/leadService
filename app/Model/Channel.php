<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = "channels";
    protected $fillable = [
        'channel_id', 
        'campaign_id', 
        'adwords_campaign_id', 
        'kind', 
        'status', 
        'url', 
        'tracking_phone',
        'forward_phone', 
        'created_at_channels', 
        'updated_at_channels', 
        'facebook_campaign_id', 
        'name', 
        'daily_net_budget', 
        'daily_gross_budget', 
        'daily_min_leads', 
        'daily_max_leads'
    ];
    
    /*
    public function campaign() {
        return $this->belongsTo('App\Model\Campaign');
    }

    public function calls(){
        return $this->hasMany('App\Model\Call', 'channel_id', 'channel_id');
    }

    public function forms(){
        return $this->hasMany('App\Model\Form', 'channel_id', 'channel_id');
    }
    */
}