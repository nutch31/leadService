<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Comment_call extends Model
{
    protected $table = "comment_calls";
    protected $fillable = [
        'call_id', 
        'first_name', 
        'last_name', 
        'remark_id', 
        'remark_value', 
        'remark_type_of_action', 
        'source_id', 
        'source_value', 
        'source_type_of_action', 
        'status_id', 
        'status_value', 
        'status_type_of_action',
        'data', 
        'created_at', 
        'updated_at'
    ];
}