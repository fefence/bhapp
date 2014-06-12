<?php

class LeagueDetailsController extends BaseController {

	public function getImportedSeasons($country, $league) {
		
		$seasons = LeagueDetails::distinct()->where('country', '=', $country)->where('fullName', '=', $league)->first()->importedSeasons;
		$data = array('seasons' => $seasons, 
						'country' => $country, 
						'league' => $league);

		return View::make('seasons')->with('data', $data);
	}

	public function getLeaguesForCountry($country) {
		$leagues = LeagueDetails::where('country', '=', $country)->get();
	    $arr = array('leagues' => $leagues, 'country' => $country);

	    return View::make('leagues')->with('data', $arr);
	}

	public function getCountriesPlusLeagues() {
		$countries = LeagueDetails::distinct()->orderBy('country')->get(array('country'));

		$data = array();
		foreach ($countries as $country) {
			$leagues = LeagueDetails::where('country', '=', $country->country)->get(array('fullName', 'id'));
			$names = array();
			foreach ($leagues as $league) {
				$seasons = ImportedSeasons::distinct()->where('league_details_id', '=', $league->id)->orderBy('season', 'DESC')->get();
				$s = array();
				foreach ($seasons as $season) {
					array_push($s, $season->season);
				}
				$names[$league->fullName] = $s;
				// array_push($names, $league->fullName['season']);
			}
			$data[$country->country] = $names;
		}
    	return View::make('countries')->with('data', $data);
	}
}