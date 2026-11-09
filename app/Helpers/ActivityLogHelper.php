<?php

use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

if (! function_exists('activity_log')) {
    function activity_log($action, $details = null)
    {

        $agent = new Agent;

        $user_agent = [
            'device' => $agent->device(),
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
        ];

        $activity_log = new UserActivity;
        $activity_log->user_id = Auth::user()->id;
        $activity_log->action = $action;
        $activity_log->details = $details;
        $activity_log->ip_address = request()->ip();
        $activity_log->user_agent = json_encode($user_agent);
        $activity_log->save();
    }
}
