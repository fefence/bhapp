<?php
Route::get('/boo', function(){
	return Updater::getPPMMatches();
	return Updater::update();
});

//details views
Route::get('/details/{home}/{away}/{date}', "GamesController@detailsPPM");
Route::get('/details/{team}/{date}', "GamesController@details");

//pool management
Route::post('/pools/get', "PoolsController@poolsGet");
Route::post('/pools/insert', "PoolsController@poolsInsert");
Route::get('/poolmanagement', "PoolsController@managePools");

//leagues to play management
Route::get('/addleagues', "GroupController@addLeaguesToPlay");
Route::post('/saveleaguestoplay', "GroupController@saveLeagues");

//PPM views
Route::get('/ppm/{from?}/{to?}', "PPMController@display");
Route::get('/ppm/{from?}/{to?}/odds', "PPMController@getOdds");

//livescore
Route::get('/livescore/{from?}/{to?}', "LivescoreController@livescore");

//Groups views
Route::get('/group/{id}', 'GamesController@getGamesForGroup');
Route::get('/group/{groups_id}/odds', 'GamesController@getMatchOddsForGames');

//games actions
Route::post('/save', 'GamesController@saveTable');
Route::get('/confirm/{game_id}', 'GamesController@confirmGame');
Route::get('/delete/{game_id}', 'GamesController@removeMatch');


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
Route::get('/home/{from?}/{to?}', array('as' => 'home', 'uses' => 'GamesController@getGroups'));
Route::get('/', function(){
	return Redirect::route('home');
});

//stats views
Route::get('/drawstats/{country}/{league}', 'SeriesController@percentStat');
Route::get('/drawspercent', 'SeriesController@percentDraws');
Route::get('/roundpercent/{country}/{league}', 'SeriesController@percentDrawsPerRound');
Route::get('countries', array('as' => 'countries', 'uses' => 'LeagueDetailsController@getCountriesPlusLeagues'));
Route::get('{country}', array('as' => 'country', 'uses' => 'LeagueDetailsController@getLeaguesForCountry'));
Route::get('{country}/{league}/archive', array('as' => 'archive', 'uses' => "LeagueDetailsController@getImportedSeasons"));
Route::get('{country}/{league}/{season}/stats', array('as' => 'stats', 'uses' => "MatchController@getStats"));
