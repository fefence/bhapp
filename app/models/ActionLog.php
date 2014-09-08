<?php


class ActionLog extends Eloquent{
    protected $table = 'action_log';
    public static $unguarded = true;
    public static function getLogsForUserDates($fromdate, $todate, $user_id)
    {
        $logs = ActionLog::where('created_at', '>=', $fromdate)
            ->where('created_at', '<=', $todate)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'action_log.league_details_id')
            ->join('game_type', 'game_type.id', '=', 'action_log.game_type_id')
            ->select(DB::raw("action_log.*, leagueDetails.*, game_type.type as game_type"))
            ->where('user_id', '=', $user_id)
            ->orderBy('created_at', "desc")
            ->get();
        return $logs;
    }
}