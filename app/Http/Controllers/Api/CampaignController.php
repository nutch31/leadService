<?php

namespace App\Http\Controllers\Api;

use App\Model\Campaign;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class CampaignController extends BaseController
{
    public function postCampaign(Request $request)
    {
        $this->validate($request, [
            'account_id' => 'required',            
            'campaign_id' => 'required'
        ]);

        $count = Campaign::where('campaign_id', '=', $request->campaign_id)->count();

        if($count == 0)
        {
            $campaign = Campaign::create([
                'campaign_id' => $request->campaign_id, 
                'name' => $request->name, 
                'status' => $request->status,
                'start_date' => $request->start_date, 
                'end_date' => $request->end_date, 
                'account_id' => $request->account_id,
                'created_at_campaigns' => $request->created_at_campaigns, 
                'updated_at_campaigns' => $request->updated_at_campaigns
            ]);

            return response($campaign, '201');
        }
        else
        {
            $campaign = Campaign::where('campaign_id', '=', $request->campaign_id)
            ->update([                
                'campaign_id' => $request->campaign_id, 
                'name' => $request->name, 
                'status' => $request->status,
                'start_date' => $request->start_date, 
                'end_date' => $request->end_date, 
                'account_id' => $request->account_id,
                'created_at_campaigns' => $request->created_at_campaigns, 
                'updated_at_campaigns' => $request->updated_at_campaigns
            ]);

            return response($campaign, '200');
        }
    }
}