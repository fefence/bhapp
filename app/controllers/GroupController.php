<?php

class GroupController extends \BaseController
{

    public function getGroups($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $data = LeagueDetails::getLeaguesWithMatches($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $res = array();
//        return $data;
        foreach($data as $groups_id => $league_id) {
            $league = LeagueDetails::find($league_id);
            $res[$league->id]['league'] = $league;
            $prev_goup = Groups::where('league_details_id', '=', $league->id)->where('id', '<', $groups_id)->orderBy('round')->orderBy('id')->first();
            $curr_group = Groups::find($groups_id);
            $all = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $curr_group->id)->where('confirmed', '=', 0)->count();
            $confirmed = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $curr_group->id)->where('confirmed', '=', 1)->distinct('standings_id')->count('standings_id');
            $all_series = Standings::where('league_details_id', '=', $league->id)->count();
            $playing = $curr_group->matches()->count();
            $res[$league->id]['filter'] = array();
            $settings = Settings::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $league_id)->first();
            if ($settings->auto == 2) {
//                $current = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', $settings->from)->sum('streak_count');
                $plus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($settings->from + 1))->sum('streak_count');
                $minus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($settings->from - 1))->sum('streak_count');
                $res[$league->id]['filter'][$settings->from - 1] = $minus;
//                $res[$league->id]['filter'][$settings->from] = $current;
                $res[$league->id]['filter'][$settings->from + 1] = $plus;
            } else if ($settings->auto == 1) {
                $to = $settings->to;
                for ($i = 1; $i < 100; $i ++) {
                    $current = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', $i)->sum('streak_count');
                    if ($current <= $to) {
                        if ($current >= $settings->from) {
                            $plus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($i + 1))->sum('streak_count');
                            $minus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($i - 1))->sum('streak_count');
                            $res[$league->id]['filter'][$i - 1] = $minus;
//                            $res[$league->id]['filter'][$i] = $current;
                            $res[$league->id]['filter'][$i + 1] = $plus;
                            break 1;
                        } else {
//                            $current = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($i - 1))->sum('streak_count');
                            $plus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', $i)->sum('streak_count');
                            $minus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($i - 2))->sum('streak_count');
                            $res[$league->id]['filter'][$i - 2] = $minus;
//                            $res[$league->id]['filter'][$i - 1] = $current;
                            $res[$league->id]['filter'][$i] = $plus;
                            break 1;
                        }
                    }
                }
            }

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
