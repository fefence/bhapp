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
            $prev_goup = Groups::where('league_details_id', '=', $league->id)->where('id', '<', $groups_id)->orderBy('round', 'desc')->orderBy('id', 'desc')->first();
            $curr_group = Groups::find($groups_id);
            $all = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $curr_group->id)->where('confirmed', '=', 0)->count();
            $today = Games::where('user_id', '=', Auth::user()->id)
                ->where('games.groups_id', '=', $curr_group->id)
                ->where('confirmed', '=', 0)
                ->join('match', 'match.id', '=', 'games.match_id')
                ->where('matchDate', '>=', $fromdate)
                ->where('matchDate', '<=', $todate)
                ->count();
            $confirmed = Games::where('user_id', '=', Auth::user()->id)
                ->where('games.groups_id', '=', $curr_group->id)
                ->where('confirmed', '=', 1)
                ->join('match', 'match.id', '=', 'games.match_id')
                ->where('matchDate', '>=', $fromdate)
                ->where('matchDate', '<=', $todate)
                ->select(DB::raw('count(distinct(games.standings_id)) as c'))
                ->pluck('c');
            $all_series = Standings::where('league_details_id', '=', $league->id)->count();
            $playing = $curr_group->matches()->count();
            $res[$league->id]['all'] = $all;
            $res[$league->id]['conf'] = $confirmed;
            $res[$league->id]['today'] = $today;
            $res[$league->id]['all_series'] = $all_series;
            $res[$league->id]['playing'] = $playing * 2;
            if ($prev_goup) {
                $sum = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $prev_goup->id)->where('confirmed', '=', 1)->select(DB::raw('sum(bsf) as sum'))->pluck('sum');
                $res[$league->id]['prev'] = $sum;
            } else {
                $res[$league->id]['prev'] = 0;
            }
            $res[$league->id]['bsf'] = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $curr_group->league_details_id)->where('game_type_id', '=', 1)->first(['amount'])->amount;
//              TODO: add new implementation after 15.09.2014
//            $res[$league->id]['prev'] = GroupToBSF::where('groups_id', '=', $prev_goup->id)->where('user_id', '=', Auth::user()->id)->first();
//            $res[$league->id]['bsf'] = GroupToBSF::where('groups_id', '=', $curr_group->id)->where('user_id', '=', Auth::user()->id)->first();

        }
//        return $res;
        return View::make('games')->with(['hide_all' => true, 'data' => $res, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small, 'ppsall' => true]);
    }


    public static function getMatchesCountForChangedSettings($user_id, $league_id, $groups_id) {
        $settings = Settings::where('user_id', '=', $user_id)->where('league_details_id', '=', $league_id)->first();
        if ($settings->auto == 2) {
            $plus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($settings->from + 1))->sum('streak_count');
            $minus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', ($settings->from - 1))->sum('streak_count');

        } else if ($settings->auto == 1) {
            $to = $settings->to;
            for ($i = 1; $i < 100; $i ++) {
                $current = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>=', $i)->sum('streak_count');
                if ($current <= $to) {
                    if ($current < $settings->from) {
                        $plus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>', ($i + 1))->sum('streak_count');
                        $minus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>', ($i - 1))->sum('streak_count');

                        break 1;
                    } else {
                        $plus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>', $i)->sum('streak_count');
                        $minus = GroupToStreaks::where('groups_id', '=', $groups_id)->where('streak_length', '>', ($i - 2))->sum('streak_count');

                        break 1;
                    }
                }
            }
        }
        return array($plus, $minus);
    }
}
