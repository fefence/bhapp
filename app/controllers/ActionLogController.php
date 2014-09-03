<?php

class ActionLogController extends \BaseController{

    public static function display($fromdate = "", $todate ='') {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
//        return $fromdate;
        $logs = ActionLog::where('created_at', '>=', $fromdate." 00:00:00")
                ->where('created_at', '<=', $todate." 23:59:59")
                ->get();
        $res = array();
        foreach($logs as $log) {
            $res[$log->id]['log'] = $log;

            if ($log->type == 'pps') {
                try {
                    $game = Games::find($log->element_id);
                    $match = $game->match;
                    $res[$log->id]['descr'] = $match->home.'-'.$match->away;
                    $res[$log->id]['user'] = User::find($game->user_id)->name;
                } catch (ErrorException $e) {
                    $match = null;
                    $res[$log->id]['user'] = "unknown";
                    $res[$log->id]['descr'] = "no game found";
                }
//                return $match;
//                $match = PPS::find($log->element_id)->
            }
            if ($log->type == 'ppm') {
                try {
                    $game = PPM::find($log->element_id);
                    $match = $game->match;
                    $res[$log->id]['descr'] = $match->home.'-'.$match->away;
                    $res[$log->id]['user'] = User::find($game->user_id)->name;
                } catch (ErrorException $e) {
                    $match = null;
                    $res[$log->id]['user'] = "unknown";
                    $res[$log->id]['descr'] = "no game found";
                }
            }
            if ($log->type == 'pools') {
                try {
                    $pool = Pools::find($log->element_id);
                    $league = LeagueDetails::find($pool->league_details_id);
                    $res[$log->id]['descr'] = $league->country." ".$league->displayName;
                    $res[$log->id]['user'] = User::find($pool->user_id)->name;
                } catch (ErrorException $e) {
                    $match = null;
                    $res[$log->id]['user'] = "unknown";
                    $res[$log->id]['descr'] = "no pool found";
                }
            }
            if ($log->type == 'free') {
                try {
                    $game = FreeGames::find($log->element_id);
                    $match = $game->match;
                    $res[$log->id]['descr'] = $match->home.'-'.$match->away;
                    $res[$log->id]['user'] = User::find($game->user_id)->name;
                } catch (ErrorException $e) {
                    $match = null;
                    $res[$log->id]['user'] = "unknown";
                    $res[$log->id]['descr'] = "no game found";
                }
            }
            if ($log->type == 'settings') {
                try {
                    $pool = Settings::find($log->element_id);
                    $league = LeagueDetails::find($pool->league_details_id);
                    $res[$log->id]['descr'] = $league->country." ".$league->displayName;
                    $res[$log->id]['user'] = User::find($pool->user_id)->name;
                } catch (ErrorException $e) {
                    $match = null;
                    $res[$log->id]['user'] = "unknown";
                    $res[$log->id]['descr'] = "no pool found";
                }
            }
            if ($log->type == 'ppm_placeholder') {
                try {
                    $game = PPMPlaceHolder  ::find($log->element_id);
                    $match = $game->match;
                    $res[$log->id]['descr'] = $match->home.'-'.$match->away;
                    $res[$log->id]['user'] = User::find($game->user_id)->name;
                } catch (ErrorException $e) {
                    $match = null;
                    $res[$log->id]['user'] = "unknown";
                    $res[$log->id]['descr'] = "no game found";
                }
            }
        }
//        return $res;
        return View::make('actionlog')->with(['data' => $res, 'base_minus' => '', 'base_plus' => '']);
    }
} 