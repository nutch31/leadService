<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = "accounts";
    protected $fillable = [
        'account_id', 
        'company_name', 
        'company_street', 
        'company_number', 
        'company_compliment', 
        'company_city', 
        'company_zip_code', 
        'company_state', 
        'company_country', 
        'adwords_account_id', 
        'advanced_analytics', 
        'created_at_accounts', 
        'updated_at_accounts', 
        'skin', 
        'logo_id', 
        'facebook_account_id', 
        'contract_end_date', 
        'currency', 
        'active_on_sunday', 
        'active_on_monday', 
        'active_on_tuesday', 
        'active_on_wednesday', 
        'active_on_thursday', 
        'active_on_friday', 
        'active_on_saturday', 
        'status', 
        'analytics'
    ];

    /*
    public function campaigns()
    {
        return $this->hasMany('App\Model\Campaign', 'account_id', 'account_id');
    }
    */
}