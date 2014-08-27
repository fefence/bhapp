<?php
Route::get('/boo', function(){
//    SeriesController::calculatePPMSeries(100);
    return Checker::getAllMatches();
//    return Parser::parseMatchesForGroup()
//    return Updater::updateFree();
//    return Match::getScore(Match::find("IRLFElFp"));
//    return Parser::parseOdds(Match::find("IRLFElFp"));
//        return LivescoreController::matchScore("IRLFElFp");
//    return GamesController::getMatchOddsForAll('2014-08-19', '2014-08-19');
});

Route::get('/settings2', function(){
   return View::make('settings2');
});

//free views
Route::get('/free/manage', "FreeController@manage");
Route::get('/free/{from?}/{to?}', "FreeController@display");
Route::post('/savefree', "FreeController@saveTable");
Route::post('/saveteam', "FreeController@save");
Route::get('/confirmfree/{game_id}', 'FreeController@confirmGame');
Route::get('/deletefree/{game_id}', 'FreeController@deleteGame');

//details views
Route::get('/details/ppm/{date}/{game}', "DetailsController@detailsPPM");
Route::get('/details/{team}/{date}', "DetailsController@details");
Route::get('/detailsfree/{match}/', "DetailsController@detailsFree");

//pool management
Route::post('/pools/get/{free?}', "PoolsController@poolsGet");
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
Route::get('/livescore/match/{match_id}', "LivescoreController@matchScore");
Route::get('/livescore/{from?}/{to?}', "LivescoreController@livescore");

//Groups views
Route::get('/pps/group/{id}/{fromdate?}/{todate?}', 'GamesController@getGamesForGroup');
Route::get('/grouphistory/{id}/{offset}', 'GamesController@getGamesForGroupOffset');
Route::get('/groupodds/{groups_id}', 'GamesController@getMatchOddsForGames');
Route::get('/ppsodds/{fromdate?}/{todate?}', 'GamesController@getMatchOddsForAll');

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
Route::post('/settings/saveforleague', 'SettingsController@saveSettingsForLeague');
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
