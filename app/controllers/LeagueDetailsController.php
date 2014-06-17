<?php

class LeagueDetailsController extends BaseController
{

    public function getImportedSeasons($country, $league)
    {
        $seasons = LeagueDetails::distinct()->where('country', '=', $country)->where('fullName', '=', $league)->first()->importedSeasons;

        return View::make('seasons')->with(['seasons' => $seasons, 'country' => $country,'league' => $league]);
	}

    public function getLeaguesForCountry($country)
    {
        $leagues = LeagueDetails::where('country', '=', $country)->get();

        return View::make('leagues')->with(['leagues' => $leagues, 'country' => $country]);
    }

    public function getCountriesPlusLeagues()
    {
        $data = LeagueDetails::getLeaguesAsArray();
        return View::make('countries')->with('data', $data);
    }
}