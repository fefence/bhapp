<?php

class SettingsController extends BaseController
{

    public function display()
    {
        // $settings = Settings::where('user_id', '=', Auth::user()->id);
        $settings = Settings::getSettingsAsArray();
        return View::make('settings')->with(array('ppm' => $settings[1], 'pps' => $settings[0], 'save' => true));
    }

    public function saveSettings()
    {
        $leagues = LeagueDetails::get(['id']);
        foreach ($leagues as $league) {
            for ($i = 1; $i < 5; $i++) {
                $dd = Input::get($league->id . "-" . $i . "-opt");
                if ($dd != '') {
                    $gr = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first();
                    $match_count = $gr->matches()->where('resultShort', '<>', '-')->count();
                    if ($match_count > 0) {
//                        continue;
//                        return Redirect::back()->with('error', $league->displayName." has more matches from the round");
                    }
                    $setting = Settings::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'game_type_id' => $i]);
                    $oldFrom = $setting->from;
                    $oldTo = $setting->to;
                    $oldMultiplier = $setting->multiplier;
                    $setting->auto = $dd;
                    if ($dd == '1') {
                        $setting->from = Input::get($league->id . '-from-' . $i);
                        $setting->to = Input::get($league->id . '-to-' . $i);
                        $setting->multiplier = Input::get($league->id . '-mul-' . $i);
                        $setting->save();
                        $pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $setting->league_details_id, 'game_type_id' => $i]);
//                        return $pool;
                        $pool->save();
//                        return $pool;
                    } else if ($dd == '2') {
                        $setting->from = Input::get($league->id . '-gt-' . $i);
                        $setting->to = 0;
                        $setting->multiplier = Input::get($league->id . '-mult-' . $i);
                        $setting->save();
                        $pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $setting->league_details_id, 'game_type_id' => $i]);
                        $pool->save();
                    } else if ($dd == '0') {
                        $pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $setting->league_details_id)->where('game_type_id', '=', $i)->first();
                        if ($pool != null) {
                            if($pool->amount == 0 && $pool->income == 0 && $pool->profit == 0 && $pool->account == 0) {
                                $pool->delete();
                            }
                        }
                        $group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first(['id']);
                        Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $group->id)->where('confirmed', '=', 0)->delete();
                        $setting->delete();
                    }
                    $group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first(['id']);
                    if ($dd != '0' && $group != NULL && $group->id != "" && ($oldMultiplier != $setting->multiplier || $oldFrom != $setting->from || $oldTo != $setting->to)) {
                        Updater::recalculateGroup($group->id, Auth::user()->id);
                    }
                }
            }
        }
        $ppm = Input::get('ppm');
        $enabled = array();
        if ($ppm != null && count($ppm) > 0) {
            foreach ($ppm as $p) {
                $arr = explode("#", $p);
                $setting = Settings::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $arr[0], 'game_type_id' => $arr[1]]);
                $setting->from = 0;
                $setting->to = 0;
                $setting->multiplier = 0;
                $setting->auto = 0;
                $setting->save();
                $pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $arr[0], 'game_type_id' => $arr[1]]);
                $pool->save();
                Updater::addPPMMatchForUser($arr[0], $arr[1], Auth::user()->id);
                if(!array_key_exists($arr[0], $enabled)) {
                    $enabled[$arr[0]] = array();
                }
                array_push($enabled[$arr[0]], $arr[1]);
            }
        }
//        return $enabled;
        return Redirect::back()->with('message', "Settings saved");
    }

    private static function createOperationalGroup($league_details_id, $round)
    {
        $current = Groups::firstOrNew(['league_details_id' => $league_details_id, 'round' => $round, 'state' => 2]);
        $current->save();
        $next = Groups::firstOrNew(['league_details_id' => $league_details_id, 'round' => ($round + 1), 'state' => 3]);
        $next->save();
        if ($league_details_id == 112) {
            Parser::parseMatchesForUSA($current, $next);
            Parser::parseLeagueSeriesUSA($league_details_id);
        } else {
            Parser::parseLeagueSeries($league_details_id);
            Parser::parseMatchesForGroup($current, $next);
        }
        $str = Standings::where('league_details_id', '=', $league_details_id)
            ->select(DB::raw('streak, count(*) as c'))
            ->groupBy('streak')
            ->get();
        foreach($str as $s) {
            $g = new GroupToStreaks();
            $g->groups_id = $current->id;
            $g->streak_length = $s->streak;
            $g->streak_count = $s->c;
            $g->save();
        }
    }

    public function addLeaguesToPlay()
    {
        $leagues = LeagueDetails::orderBy('country')->get();
        $toPlay = Groups::where('state', '=', 2)->lists('round', 'league_details_id');
        return View::make('addleagues')->with(['leagues' => $leagues, 'toPlay' => $toPlay]);
    }

    public function saveLeagues()
    {
        $ids = Input::get('ids');
        $notIn = Groups::where('state', '=', 2)->lists('league_details_id');
        foreach ($ids as $id) {
            if (!in_array($id, $notIn)) {
                $round = Input::get('v-' . $id);
                SettingsController::createOperationalGroup($id, $round);
            }
        }
        return Redirect::back()->with("message", "League added");
    }
}