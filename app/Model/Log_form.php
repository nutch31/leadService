<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log_form extends Model
{
    protected $table = "log_forms";
    protected $fillable = [
        'form_id', 
        'form_id_herobease',
        'channel_id',
        'name',
        'email',
        'phone',
        'custom_attributes',
        'is_duplicated',
        'parent_id_duplicated',
        'ip',
        'location',
        'created_at_forms_herobase',
        'updated_at_forms_herobase',
        'page_url',
        'created_at_forms',
        'updated_at_forms',
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