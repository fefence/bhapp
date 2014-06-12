<?php

class SettingsController extends BaseController {

	public function display() {
		// $settings = Settings::where('user_id', '=', Auth::user()->id);

		$ppm = array();

		$pps = array();

		$league = LeagueDetails::orderBy('country')->get();
		
		foreach ($league as $l) {
			if ($l->ppm == 1) {
				if (!array_key_exists($l->country, $ppm)) {
				$ppm[$l->country] = array();
				}
				$ppm[$l->country][0] = $l->id;
				$ppm[$l->country][5] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 5)->where('user_id', '=', Auth::user()->id)->first();
				$ppm[$l->country][6] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 6)->where('user_id', '=', Auth::user()->id)->first();
				$ppm[$l->country][7] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 7)->where('user_id', '=', Auth::user()->id)->first();
				$ppm[$l->country][8] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 8)->where('user_id', '=', Auth::user()->id)->first();
			
			}
			if (!array_key_exists($l->country, $pps)) {
				$pps[$l->country] = array();
			}
			if (!array_key_exists($l->fullName, $pps[$l->country])) {
				$pps[$l->country][$l->fullName] = array();
			}
			$pps[$l->country][$l->fullName][0] = $l->id;
			$pps[$l->country][$l->fullName][1] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 1)->where('user_id', '=', Auth::user()->id)->first();
			$pps[$l->country][$l->fullName][2] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 2)->where('user_id', '=', Auth::user()->id)->first();
			$pps[$l->country][$l->fullName][3] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 3)->where('user_id', '=', Auth::user()->id)->first();
			$pps[$l->country][$l->fullName][4] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 4)->where('user_id', '=', Auth::user()->id)->first();
		}
		return View::make('settings')->with(array('ppm' => $ppm, 'pps' => $pps, 'save' => true));
	}

	public function saveSettings() {
		// return Input::all();
		$leagues = LeagueDetails::get(['id']);
		foreach ($leagues as $league) {
			$dd = Input::get($league->id.'-opt');
			$setting = Settings::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'game_type_id' => 1]);
			$oldFrom = $setting->from;
			$oldTo = $setting->to;
			$oldMultiplier = $setting->multiplier;
			$setting->game_type_id = 1;
			if ($dd == 'auto') {
				$setting->from = Input::get($league->id.'-from');
				$setting->to = Input::get($league->id.'-to');
				$setting->multiplier = Input::get($league->id.'-mul');
				$setting->auto = 1;
				$setting->save();
				$pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'ppm' => 0]);
				$pool->save();
			} else if($dd == 'fixed') {
				$setting->from = Input::get($league->id.'-lt');
				$setting->to=0;
				$setting->multiplier = Input::get($league->id.'-mul1');
				$setting->auto = 2;
				$setting->save();
				$pool = Pools::firstOrNew(['user_id' => Auth::user()->id, 'league_details_id' => $league->id, 'ppm' => 0]);
				$pool->save();
			} else if($dd == 'disabled') {
				//$setting->delete();
			}
			$group = Groups::where('league_details_id', '=', $league->id)->where('state', '=', 2)->first(['id']);
			if ($group != NULL && $group->id != "" && ($oldMultiplier != $setting->multiplier || $oldFrom != $setting->from || $oldTo != $setting->to)) {
				Updater::recalculateGroup($group->id, Auth::user()->id);
			}
		}
		$ppm = Input::get('ppm');
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
		return Redirect::back();
	}

}