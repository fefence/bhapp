<?php
Route::get('/boo', function(){
//    return Updater::updatePPM();
//    $gr = Groups::where('league_details_id', '=', 69)->where('state', '=', 2)->first();
//    $gr1 = Groups::where('league_details_id', '=', 69)->where('state', '=', 3)->first();
//    return Parser::parseMatchesForGroup($gr, $gr1);
//    return Updater::updateGroup(106);
//    return Updater::getNextPPMMatch(Match::find("6wTAnq0P"));
//    return Parser::parseTeamMatches("http://www.betexplorer.com/soccer/poland/ekstraklasa/teaminfo.php?team_id=lIDaZJTc");
});

//free views
Route::get('/free/{from?}/{to?}', "FreeController@display");
Route::get('/managefree', "FreeController@manage");
Route::post('/saveteam', "FreeController@save");

//details views
Route::get('/details/ppm/{date}/{game}', "DetailsController@detailsPPM");
Route::get('/details/{team}/{date}/{game}', "DetailsController@details");

//pool management
Route::post('/pools/get', "PoolsController@poolsGet");
Route::post('/pools/insert', "PoolsController@poolsInsert");
Route::get('/pool', "PoolsController@managePools");

//leagues to play management
Route::get('/addleagues', "SettingsController@addLeaguesToPlay");
Route::post('/saveleaguestoplay', "SettingsController@saveLeagues");

//PPM views
Route::get('/ppm/country/{country}/{from?}/{to?}', "PPMController@display");
Route::get('/ppm/{from?}/{to?}', "PPMController@displayCountries");
Route::get('/ppmflat/{from?}/{to?}', "PPMController@displayFlatView");
Route::get('/ppm/{from?}/{to?}/odds', "PPMController@getOdds");
Route::get('/ppmseries/{id}', "PPMController@displaySeries");

//livescore
Route::get('/livescore', "LivescoreController@livescore");
Route::get('/livescorematch/{match_id}', "LivescoreController@matchScore");

//Groups views
Route::get('/group/{id?}/{fromdate?}/{todate?}', 'GamesController@getGamesForGroup');
Route::get('/groupodds/{groups_id}', 'GamesController@getMatchOddsForGames');

//games actions
Route::post('/save', 'GamesController@saveTable');
Route::get('/confirm/{game_id}/{game_type_id}', 'GamesController@confirmGame');
Route::get('/delete/{game_id}/{game_type_id}', 'GamesController@deleteGame');
Route::get('/addgame/{groups_id}/{standings_id}/{match_id}', 'GamesController@addGame');
Route::get('/confirmall/{group_id}/{fromdate?}/{todate?}', 'GamesController@confirmAllGames');
Route::get('/recalc/{group_id}', 'GamesController@recalculateGroup');

//Route::get('/nextmatches/{country}/{league}', 'MatchController@getNextMatchesForPlay');


//Simulators
Route::get('/simulator/{country?}/{league?}/{seasoncount?}', 'SimulatorController@getSimMatches');
Route::post('/simulator/{country?}/{league?}/{seasoncount?}', 'SimulatorController@newSim');

Route::get('/simulatormerged/{count?}', 'SimulatorController@getSimMerged');
Route::post('/simulatormerged', 'SimulatorController@newSimMerged');

//calculation of ppm series
Route::get('/calculateseriesppm', "SeriesController@calculatePPMSeries");

//Route::get('/show', "SeriesController@getSeries");

//session management
Route::get('login', 'SessionsController@create');
Route::get('logout', 'SessionsController@destroy');
Route::resource('sessions', 'SessionsController', ['only'  => ['create', 'store', 'destroy']]);

//settings
Route::get('/settings', 'SettingsController@display');
Route::post('/settings/save', 'SettingsController@saveSettings');
Route::post('/settings/remove', 'SettingsController@remove');

//home
Route::get('/pps/{from?}/{to?}', array('as' => 'home', 'uses' => 'GroupController@getGroups'));
Route::get('/', function(){
	return Redirect::route('home');
});
Route::get('/home', function(){
    return Redirect::route('home');
});

//stats views
Route::get('/drawstats/{country}/{league}', 'SeriesController@percentStat');
Route::get('/drawspercent', 'SeriesController@percentDraws');
Route::get('/roundpercent/{country}/{league}', 'SeriesController@percentDrawsPerRound');
Route::get('countries', array('as' => 'countries', 'uses' => 'LeagueDetailsController@getCountriesPlusLeagues'));
Route::get('{country}', array('as' => 'country', 'uses' => 'LeagueDetailsController@getLeaguesForCountry'));
Route::get('{country}/{league}/archive', array('as' => 'archive', 'uses' => "LeagueDetailsController@getImportedSeasons"));
Route::get('{country}/{league}/{season}/stats', array('as' => 'stats', 'uses' => "StatsController@getStats"));
