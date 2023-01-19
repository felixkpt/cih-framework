<?php

namespace Cih\Framework\Repositories;

use App\Models\Core\Deal;
use App\Models\Core\LogActivity;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Request;

class LogActivityRepository
{

    public static function addToLog($subject)
    {
        $agent = new Agent();
// agent detection influences the view storage path
        $device = null;
        $device_name = null;
        if ($agent->isMobile()) {
            $device = 0;
            $device_name = "mobile";
        } elseif ($agent->isTablet()) {
            $device = 1;
            $device_name = "tablet";
        } elseif ($agent->isDesktop()) {
            $device = 2;
            $device_name = "desktop";
        }

        $log = [];
        $log['subject'] = $subject;
        $log['url'] = Request::fullUrl();
        $log['method'] = Request::method();
        $log['ip'] = Request::ip();
        $log['agent'] = Request::header('user-agent');
        $log['device'] = $device_name;
        $log['user_id'] = auth()->user()->id;
        $log['status'] = 1;
        $log['deals_created'] = Deal::where('user_id', auth()->user()->id)->count();
        $log['last_activity'] = Carbon::now()->timezone('Africa/Nairobi');
        LogActivity::updateOrCreate(['user_id' => auth()->user()->id], $log);

        return true;
    }

    private function getDealsCount()
    {
        return Deal::where('user_id', auth()->user()->id)->count();
    }
}
