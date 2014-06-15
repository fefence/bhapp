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
        // return Input::all();
        $leagues = LeagueDetails::get(['id']);
        foreach ($leagues as $league) {
            $dd = Input::get($league->id . '-opt');
            $setting = Settings::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'game_type_id' => 1]);
            $oldFrom = $setting->from;
            $oldTo = $setting->to;
            $oldMultiplier = $setting->multiplier;
            $setting->game_type_id = 1;
            if ($dd == 'auto') {
                $setting->from = Input::get($league->id . '-from');
                $setting->to = Input::get($league->id . '-to');
                $setting->multiplier = Input::get($league->id . '-mul');
                $setting->auto = 1;
                $setting->save();
                $pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'ppm' => 0]);
                $pool->save();
            } else if ($dd == 'fixed') {
                $setting->from = Input::get($league->id . '-lt');
                $setting->to = 0;
                $setting->multiplier = Input::get($league->id . '-mul1');
                $setting->auto = 2;
                $setting->save();
                $pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'ppm' => 0]);
                $pool->save();
            } else if ($dd == 'disabled') {
                //$setting->delete();
            }
            $group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first(['id']);
            if ($group != NULL && $group->id != "" && ($oldMultiplier != $setting->multiplier || $oldFrom != $setting->from || $oldTo != $setting->to)) {
                Updater::recalculateGroup($group->id, Auth::user()->id);
            }
        }
        $ppm = Input::get('ppm');
        if ($ppm != null && count($ppm) > 0) {
            foreach ($ppm as $p) {
                $arr = explode("#", $p);
                $setting = Settings::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $arr[0], 'game_type_id' => $arr[1]]);

                $setting->from = 0;
                $setting->to = 0;
                $setting->multiplier = 0;
                $setting->auto = 0;
                $setting->save();
                $pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $arr[0], 'ppm' => 1]);
                $pool->save();
                Updater::addPPMMatchForUser($arr[0], $arr[1], Auth::user()->id);
            }
        }
        return Redirect::back();
    }

    private static function createOperationalGroup($league_details_id, $round)
    {
        $current = Groups::firstOrNew(['league_details_id' => $league_details_id, 'round' => $round, 'state' => 2]);
        $current->save();
        // return $current;
        $next = Groups::firstOrNew(['league_details_id' => $league_details_id, 'round' => ($round + 1), 'state' => 3]);
        $next->save();
        Parser::parseMatchesForGroup($current, $next);
        Parser::parseLeagueSeries($current);
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
        return Redirect::back();
    }
}