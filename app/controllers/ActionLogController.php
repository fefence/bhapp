<?php

class ActionLogController extends \BaseController{

    public static function display($fromdate = "", $todate ='') {
//        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        if ($fromdate == "") {
            $fromdate = date("Y-m-d H:i:s", time()-15*24*60*60);
        }
        if ($todate == "") {
            $todate = date("Y-m-d H:i:s", time());
        }
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $logs = ActionLog::where('created_at', '>=', $fromdate)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'action_log.league_details_id')
            ->join('game_type', 'game_type.id', '=', 'action_log.game_type_id')
            ->select(DB::raw("action_log.*, leagueDetails.*, game_type.type as game_type"))
            ->where('user_id', '=', Auth::user()->id)
            ->where('created_at', '<=', $todate)
            ->orderBy('created_at', "desc")
            ->get();
        return View::make('actionlog')->with(['data' => $logs, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }
} 