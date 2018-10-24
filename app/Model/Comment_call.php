<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Comment_call extends Model
{
    protected $table = "comment_calls";
    protected $fillable = [
        'call_id', 
        'firstName',
        'lastName',
        'remarkId',
        'remarkValue',
        'reporterId',
        'typeOfAction',
        'sourceId',
        'statusId',
        'data',
        'created_at', 
        'updated_at'
    ];
}