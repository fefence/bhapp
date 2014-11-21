<?php
Route::get('/boo', function(){
    $matches = Match::where('league_details_id', 107)->where('matchTime', '=', '00:00:00')->get();
    foreach($matches as $m) {
        Parser::parseTimeDate($m);
    }
//    return SeriesController::calculatePPMSeries($id);
//    return Parser::test();
//    return Parser::parseMatchesForGroup(Groups::find(1888), Groups::find(1892));
//    Updater::updateGroup(1842);
//    return SeriesController::calculatePPMSeries($id);
});

Route::get('/settings2', function(){
   return View::make('settings2');
});
//getPPMSeries
Route::get('/ppm/stats/series/{id}', "SeriesController@getPPMSeries");

Route::get('/log/{from?}/{to?}', "ActionLogController@display");
Route::post('/getres/{id}', "LivescoreController@getMatchCurrentRes");
//free views
Route::get('/free/manage', "FreeController@manage");
Route::get('/free/show/{team_id}', 'FreeController@showTeam');
Route::get('/free/hide/{team_id}', 'FreeController@hideTeam');
Route::get('/free/{from?}/{to?}', "FreeController@display");
Route::post('/savefree', "FreeController@saveTable");
Route::post('/saveteam', "FreeController@save");
Route::get('/confirmfree/{game_id}', 'FreeController@confirmGame');
Route::get('/deletefree/{game_id}/{game_type?}', 'FreeController@deleteGame');
Route::get('/freeodds/{fromdate}/{todate}', 'FreeController@refreshOdds');


//details views
Route::get('/details/ppm/{date}/{game}', "DetailsController@detailsPPM");
Route::get('/details/{team}/{date}', "DetailsController@details");
Route::get('/detailsfree/{team}/{match}', "DetailsController@detailsFree");

//pool management
Route::post('/pools/get/{free?}', "PoolsController@poolsGet");
Route::post('/pools/reset', "PoolsController@resetPPSPool");
Route::post('/pools/toacc/{free?}', "PoolsController@poolsToAccount");
Route::post('/pools/insert/{free?}', "PoolsController@poolsInsert");
Route::get('/pool', "PoolsController@managePools");
Route::get('/pool/flow', function(){
    return View::make("poolflow");
});

//leagues to play management
Route::get('/addleagues', "SettingsController@addLeaguesToPlay");
Route::post('/saveleaguestoplay', "SettingsController@saveLeagues");

//PPM views
Route::get('/ppm/series/{id}', "PPMController@displaySeries");
Route::get('/ppm/flat/{from?}/{to?}', "PPMController@displayFlatView");
Route::get('/ppm/country/{country}/{from?}/{to?}', "PPMController@display");
Route::get('/ppm/{from?}/{to?}', "PPMController@displayCountries");
Route::get('/ppmall/{from?}/{to?}/odds', "PPMController@getOdds");
Route::get('/ppm/{country}/{from}/{to}/odds', "PPMController@getOddsForCountry");
Route::get('/confirmallppm/{country}/{from}/{to}', "GamesController@confirmAllPPM");

//livescore
Route::get('/live/match/{match_id}', "LivescoreController@matchScore");
Route::get('/live/{from?}/{to?}', array('as' => 'home', "uses" => "LivescoreController@livescore"));

//Groups views
Route::get('/pps/group/{id}/{offset}', 'GamesController@getGamesForGroupOffset');
Route::get('/pps/group/{id}/{fromdate?}/{todate?}', 'GamesController@getGamesForGroup');
Route::get('/groupodds/{groups_id}', 'GamesController@getMatchOddsForGames');
Route::get('/ppsodds/{fromdate?}/{todate?}', 'GamesController@getMatchOddsForAll');
Route::get('/pps/remove/{game_id}/{groups_id}', 'GamesController@removeGameFromGroup');



//games actions
Route::post('/save', 'GamesController@saveTable');
Route::get('/confirm/{game_id}/{game_type_id}/{placeholder?}', 'GamesController@confirmGame');
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
Route::post('/settings/saveforleague', 'SettingsController@saveSettingsForLeague');
Route::post('/settings/remove', 'SettingsController@remove');

//home
Route::get('/pps/{from?}/{to?}', 'GroupController@getGroups');
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
