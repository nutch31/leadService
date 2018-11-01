<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = "forms";
    protected $fillable = [
        'form_id', 
        'channel_id', 
        'name', 
        'email', 
        'phone', 
        'custom_attributes', 
        'is_duplicated', 
        'parent_id_duplicated',
        'ip', 
        'location', 
        'created_at_forms', 
        'updated_at_forms', 
        'page_url', 
        'created_at', 
        'updated_at'
    ];
     
    /*
    public function channel() {
        return $this->belongsTo('App\Model\Channel');
    }
    */
}