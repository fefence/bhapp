<?php

class ActionLogController extends \BaseController{

    public static function display($fromdate = "", $todate ='') {
        $user_id = Auth::user()->id;
        if ($fromdate == "") {
            $fromdate = date("Y-m-d H:i:s", time()-15*24*60*60);
        } else {
            $fromdate = $fromdate." 00:00:00";
        }
        if ($todate == "") {
            $todate = date("Y-m-d H:i:s", time());
        } else {
            $todate = $todate." 23:59:59";
        }
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $logs = ActionLog::getLogsForUserDates($fromdate, $todate, $user_id);
        return View::make('actionlog')->with(['data' => $logs, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small, 'all_btn' => 'last 15 days']);
    }

} 