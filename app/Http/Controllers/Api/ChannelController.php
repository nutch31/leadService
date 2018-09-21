<?php

namespace App\Http\Controllers\Api;

use App\Model\Channel;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class ChannelController extends BaseController
{
    public function postChannel(Request $request)
    {
        $this->validate($request, [    
            'campaign_id' => 'required',
            'channel_id' => 'required'       
        ]);

        $count = Channel::where('channel_id', '=', $request->channel_id)->count();

        if($count == 0)
        {
            $channel = Channel::create([
                'channel_id' => $request->channel_id, 
                'campaign_id' => $request->campaign_id, 
                'adwords_campaign_id' => $request->adwords_campaign_id,
                'kind' => $request->kind, 
                'status' => $request->status, 
                'url' => $request->url, 
                'tracking_phone' => $request->tracking_phone,
                'forward_phone' => $request->forward_phone, 
                'created_at_channels' => $request->created_at_channels, 
                'updated_at_channels' => $request->updated_at_channels,
                'facebook_campaign_id' => $request->facebook_campaign_id, 
                'name' => $request->name, 
                'daily_net_budget' => $request->daily_net_budget,
                'daily_gross_budget' => $request->daily_gross_budget, 
                'daily_min_leads' => $request->daily_min_leads, 
                'daily_max_leads' => $request->daily_max_leads
            ]);

            return response($channel, '201');
        }
        else
        {
            $channel = Channel::where('campaign_id', '=', $request->campaign_id)
            ->update([                
                'channel_id' => $request->channel_id, 
                'campaign_id' => $request->campaign_id, 
                'adwords_campaign_id' => $request->adwords_campaign_id,
                'kind' => $request->kind, 
                'status' => $request->status, 
                'url' => $request->url, 
                'tracking_phone' => $request->tracking_phone,
                'forward_phone' => $request->forward_phone, 
                'created_at_channels' => $request->created_at_channels, 
                'updated_at_channels' => $request->updated_at_channels,
                'facebook_campaign_id' => $request->facebook_campaign_id, 
                'name' => $request->name, 
                'daily_net_budget' => $request->daily_net_budget,
                'daily_gross_budget' => $request->daily_gross_budget, 
                'daily_min_leads' => $request->daily_min_leads, 
                'daily_max_leads' => $request->daily_max_leads
            ]);

            return response($channel, '200');
        }
    }
}