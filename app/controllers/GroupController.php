<?php

class GroupController extends \BaseController
{

    public function getGroups($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $data = LeagueDetails::getLeaguesWithMatches($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $res = array();
        foreach($data as $league) {
            $res[$league->id]['league'] = $league;
            $prev_goup = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 1)->orderBy('round')->orderBy('id')->first();
            $curr_group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first();
            $all = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $curr_group->id)->where('confirmed', '=', 0)->count();
            $confirmed = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $curr_group->id)->where('confirmed', '=', 1)->distinct('standings_id')->count('standings_id');
            $all_series = Standings::where('league_details_id', '=', $league->id)->count();
            $playing = $curr_group->matches()->count();

//                return $confirmed;
            $res[$league->id]['all'] = $all;
            $res[$league->id]['conf'] = $confirmed;
            $res[$league->id]['all_series'] = $all_series;
            $res[$league->id]['playing'] = $playing * 2;
            if ($prev_goup) {
                $sum = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $prev_goup->id)->where('confirmed', '=', 1)->sum('bsf');
                $res[$league->id]['prev'] = $sum;
            } else {
                $res[$league->id]['prev'] = 0;
            }
        }
//        return $res;
        return View::make('games')->with(['data' => $res, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }

}
