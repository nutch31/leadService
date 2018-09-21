<?php

namespace App\Http\Controllers\Api;

use App\Model\Account;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AccountController extends BaseController
{
    public function postAccount(Request $request)
    {
        $this->validate($request, [
            'account_id' => 'required'
        ]);

        $count = Account::where('account_id', '=', $request->account_id)->count();

        if($count == 0)
        {
            $account = Account::create([
                'account_id' => $request->account_id, 
                'company_name' => $request->company_name, 
                'company_street' => $request->company_street,
                'company_number' => $request->company_number, 
                'company_compliment' => $request->company_compliment, 
                'company_city' => $request->company_city,
                'company_zip_code' => $request->company_zip_code, 
                'company_state' => $request->company_state, 
                'company_country' => $request->company_country,
                'adwords_account_id' => $request->adwords_account_id, 
                'advanced_analytics' => $request->advanced_analytics, 
                'created_at_accounts' => $request->created_at_accounts,
                'updated_at_accounts' => $request->updated_at_accounts, 
                'skin' => $request->skin, 
                'logo_id' => $request->logo_id, 
                'facebook_account_id' => $request->facebook_account_id,
                'contract_end_date' => $request->contract_end_date, 
                'currency' => $request->currency, 
                'active_on_sunday' => $request->active_on_sunday, 
                'active_on_monday' => $request->active_on_monday,
                'active_on_tuesday' => $request->active_on_tuesday, 
                'active_on_wednesday' => $request->active_on_wednesday, 
                'active_on_thursday' => $request->active_on_thursday,
                'active_on_friday' => $request->active_on_friday, 
                'active_on_saturday' => $request->active_on_saturday, 
                'status' => $request->status, 
                'analytics' => $request->analytics
            ]);

            return response($account, '201');
        }
        else
        {
            $account = Account::where('account_id', '=', $request->account_id)
            ->update([
                'account_id' => $request->account_id, 
                'company_name' => $request->company_name, 
                'company_street' => $request->company_street,
                'company_number' => $request->company_number, 
                'company_compliment' => $request->company_compliment, 
                'company_city' => $request->company_city,
                'company_zip_code' => $request->company_zip_code, 
                'company_state' => $request->company_state, 
                'company_country' => $request->company_country,
                'adwords_account_id' => $request->adwords_account_id, 
                'advanced_analytics' => $request->advanced_analytics, 
                'created_at_accounts' => $request->created_at_accounts,
                'updated_at_accounts' => $request->updated_at_accounts, 
                'skin' => $request->skin, 
                'logo_id' => $request->logo_id, 
                'facebook_account_id' => $request->facebook_account_id,
                'contract_end_date' => $request->contract_end_date, 
                'currency' => $request->currency, 
                'active_on_sunday' => $request->active_on_sunday, 
                'active_on_monday' => $request->active_on_monday,
                'active_on_tuesday' => $request->active_on_tuesday, 
                'active_on_wednesday' => $request->active_on_wednesday, 
                'active_on_thursday' => $request->active_on_thursday,
                'active_on_friday' => $request->active_on_friday, 
                'active_on_saturday' => $request->active_on_saturday, 
                'status' => $request->status, 
                'analytics' => $request->analytics
            ]);

            return response($account, '200');
        }
    }
}