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
                    if ($gr == null) {
                        return $league;
                    }
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
                            $aLog = new ActionLog;
                            $aLog->type = "settings";
                            $aLog->action = "disable";
                            $aLog->amount = 0;
                            $aLog->element_id = $setting->id;
                            $aLog->save();
                        }
                        $group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first(['id']);
                        Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $group->id)->where('confirmed', '=', 0)->where('game_type_id', '=', $i)->delete();
                        $setting->delete();
                    }
                    $group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first(['id']);
                    if ($dd != '0' && $group != NULL && $group->id != "" && ($oldMultiplier != $setting->multiplier || $oldFrom != $setting->from || $oldTo != $setting->to)) {
                        Updater::recalculateGroup($group->id, Auth::user()->id, $i);
                        if ($oldMultiplier != $setting->multiplier) {
                            $aLog = new ActionLog;
                            $aLog->type = "settings";
                            $aLog->action = "change multiplier";
                            $aLog->amount = $setting->multiplier;
                            $aLog->element_id = $setting->id;
                            $aLog->save();
                        }
                        if ($oldFrom != $setting->from) {
                            $aLog = new ActionLog;
                            $aLog->type = "settings";
                            $aLog->action = "change from";
                            $aLog->amount = $setting->from;
                            $aLog->element_id = $setting->id;
                            $aLog->save();
                        }
                        if ($oldTo != $setting->to) {
                            $aLog = new ActionLog;
                            $aLog->type = "settings";
                            $aLog->action = "change to";
                            $aLog->amount = $setting->to;
                            $aLog->element_id = $setting->id;
                            $aLog->save();
                        }
                    }
                }
            }
        }
        $ppm = Input::get('ppm');
        $enabled = array();
        if ($ppm != null && count($ppm) > 0) {
            foreach ($ppm as $p) {
                $arr = explode("#", $p);
                $setting = Settings::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $arr[0])->where('game_type_id', '=', $arr[1])->first();
                if ($setting == null) {
                    $setting = new Settings(['user_id' => Auth::user()->id, 'league_details_id' => $arr[0], 'game_type_id' => $arr[1]]);
                    $setting->save();
                    $aLog = new ActionLog;
                    $aLog->type = "settings";
                    $aLog->action = "add ppm league";
                    $aLog->amount = $arr[0];
                    $aLog->element_id = $setting->id;
                    $aLog->save();
                }
//                $setting = Settings::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $arr[0], 'game_type_id' => $arr[1]]);
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

        $ppmSettings = Settings::where('game_type_id', '>=', 5)
            ->where('game_type_id', '<=', 8)
            ->where('user_id', '=', Auth::user()->id)
            ->get();

//        return $enabled;
        foreach($ppmSettings as $s) {
            if (!array_key_exists($s->league_details_id, $enabled) || (array_key_exists($s->league_details_id, $enabled) && !in_array($s->game_type_id, $enabled[$s->league_details_id]))){
//                return $enabled[$s->league_details_id];
//                echo $s->league_details_id." ".$s->game_type_id;
//                echo ""
                $p = Pools::where('user_id', '=', Auth::user()->id)
                    ->where('league_details_id', '=', $s->league_details_id)
                    ->where('game_type_id', '=', $s->game_type_id)
                    ->first();
                if ($p != null && $p->amount == 0 && $p->income == 0 && $p->profit == 0 && $p->account == 0) {
                    $p->delete();
                }
                $todelete = PPM::join('match', 'match.id', '=', 'ppm.match_id')
                    ->where('resultShort', '=', '-')
                    ->where('league_details_id', '=', $s->league_details_id)
                    ->where('game_type_id', '=', $s->game_type_id)
                    ->where('user_id', '=', $s->user_id)
                    ->select('ppm.*')
                    ->get();
                foreach($todelete as $d) {
                    $d->delete();
                }
                $aLog = new ActionLog;
                $aLog->type = "settings";
                $aLog->action = "disable ppm league";
                $aLog->amount = $s->game_type_id;
                $aLog->element_id = $s->league_details_id;
                $aLog->save();
                $s->delete();
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