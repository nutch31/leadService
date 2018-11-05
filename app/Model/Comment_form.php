<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Comment_form extends Model
{
    protected $table = "comment_forms";
    protected $fillable = [
        'form_id', 
        'firstName',
        'lastName',
        'remarkId',
        'remarkValue',
        'reporterId',
        'typeOfAction',
        'email',
        'sourceId',
        'statusId',
        'data',
        'created_at', 
        'updated_at'
    ];
}