<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

//stats

Route::get('/boo', function(){
	// $league_details_id = 104;
	// $matches = Match::where('league_details_id', '=', $league_details_id)->get();
	// foreach ($matches as $m) {
	// 	Updater::updateDetails($m);
	// }
	// Parser::parseLeagueSeries(Groups::find(43));
	// Parser::parseMatchesForGroup(Groups::find(37), Groups::find(41));
	// Updater::recalculateGroup(37,2);
	// Updater::recalculateGroup(37,1);
	return Updater::getPPMMatches();
	return Updater::update();

	// return GroupController::createOperationalGroup(31,13);
	// $matches = Match::where('groups_id', '=', 43)->get();
	// foreach ($matches as $match) {
	// 	Match::updateMatchDetails($match);
	// 	$bet365 = Odds1x2::where('match_id', '=', $match->id)->where('bookmaker_id', '=', 1)->first();
	// 	$betfair = Odds1x2::where('match_id', '=', $match->id)->where('bookmaker_id', '=', 2)->first();
	// 	echo $match->home."-".$match->away.":".$bet365->oddsX."/".$betfair->oddsX.'<br>';
	// }
});
Route::get('/details/{home}/{away}/{date}', "GamesController@detailsPPM");

Route::get('/details/{team}/{date}', "GamesController@details");

Route::post('/pools/get', "PoolsController@poolsGet");
Route::post('/pools/insert', "PoolsController@poolsInsert");

Route::get('/poolmanagement', "PoolsController@managePools");
Route::get('/addleagues', "GroupController@addLeaguesToPlay");
Route::post('/saveleaguestoplay', "GroupController@saveLeagues");

Route::get('/ppm/{from?}/{to?}', "PPMController@display");
Route::get('/ppm/{from?}/{to?}/odds', "PPMController@getOdds");

Route::get('/livescore/{from?}/{to?}', "LivescoreController@livescore");

// Route::post('/pools/get', 'PoolsController@getFromMain');

Route::get('/group/{id}', 'GamesController@getGamesForGroup');
Route::get('/group/{groups_id}/odds', 'GamesController@getMatchOddsForGames');

Route::post('/save', 'GamesController@saveTable');
Route::get('/confirm/{game_id}', 'GamesController@confirmGame');
Route::get('/delete/{game_id}', 'GamesController@removeMatch');


Route::get('/nextmatches/{country}/{league}', 'MatchController@getNextMatchesForPlay');

Route::get('/drawstats/{country}/{league}', 'SeriesController@percentStat');
Route::get('/drawspercent', 'SeriesController@percentDraws');
Route::get('/roundpercent/{country}/{league}', 'SeriesController@percentDrawsPerRound');

Route::get('/simulator/{country?}/{league?}/{seasoncount?}', 'SimulatorController@getSimMatches');
Route::post('/simulator/{country?}/{league?}/{seasoncount?}', 'SimulatorController@newSim');

Route::get('/simulatormerged/{count?}', 'SimulatorController@getSimMerged');
Route::post('/simulatormerged', 'SimulatorController@newSimMerged');

Route::get('/calculateseriesppm', "SeriesController@calculatePPMSeries");

Route::get('/show', "SeriesController@getSeries");

Route::get('login', 'SessionsController@create');

Route::get('logout', 'SessionsController@destroy');

Route::resource('sessions', 'SessionsController', ['only'  => ['create', 'store', 'destroy']]);

Route::get('/settings', 'SettingsController@display');
Route::post('/settings/save', 'SettingsController@saveSettings');
Route::post('/settings/remove', 'SettingsController@remove');

Route::get('/home/{from?}/{to?}', array('as' => 'home', 'uses' => 'GamesController@getGroups'));
Route::get('/', function(){
	return Redirect::route('home');
});

Route::get('countries', array('as' => 'countries', 'uses' => 'LeagueDetailsController@getCountriesPlusLeagues'));


Route::get('{country}', array('as' => 'country', 'uses' => 'LeagueDetailsController@getLeaguesForCountry'));

Route::get('{country}/{league}/archive', array('as' => 'archive', 'uses' => "LeagueDetailsController@getImportedSeasons"));

Route::get('{country}/{league}/{season}/stats', array('as' => 'stats', 'uses' => "MatchController@getStats"));
