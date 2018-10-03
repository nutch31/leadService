<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Landingpagecallservice extends Model
{
    protected $table = "landingpagecallservice";
    protected $fillable = [
        'response', 
        'status',
        'status_alpha',
        'form_id_leadservice',
        'created_at', 
        'updated_at'
    ];
}