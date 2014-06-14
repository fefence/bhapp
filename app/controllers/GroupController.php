<?php

class GroupController extends \BaseController {

    public function getGroups($fromdate = "", $todate = "")
    {
        if ($fromdate == "") {
            $fromdate = date("Y-m-d", time());
        }
        if ($todate == "") {
            $todate = date("Y-m-d", time());
        }
        $league_details_ids = Settings::where('user_id', '=', Auth::user()->id)->lists('league_details_id');

        if (count($league_details_ids) > 0) {
            $ids = Groups::whereIn('groups.league_details_id', $league_details_ids)
                ->join('match', 'match.groups_id', '=', 'groups.id')
                ->where('matchDate', '>=', $fromdate)
                ->where('matchDate', '<=', $todate)
                ->select('match.league_details_id as lids')
                ->lists('lids');
            if (count($ids) > 0) {
                $data = LeagueDetails::whereIn('id', $ids)->get(['country', 'fullName', 'id']);
            } else {
                $data = array();
            }
        } else {
            $data = array();
        }

        list($big, $small) = HeadingsUtil::calculateHeading($fromdate, $todate);

        return View::make('games')->with(['data' => $data, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }
}
