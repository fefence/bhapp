<?php

class BaseController extends Controller {
	public function __construct() {
		if (!Auth::guest()) {
			$global = CommonPools::where('user_id', '=', Auth::user()->id)->first();
		    View::share('global', $global);	
		    View::share('base', Request::segment('1'));
            $free = FreeGames::join('match', 'match.id', '=', 'freeplay.match_id')
                ->join('freeplay_teams', 'freeplay_teams.team_id', '=', 'freeplay.team_id')
                ->where('matchDate', '=', date("Y-m-d", time()))
                ->where('freeplay.user_id', '=', Auth::user()->id)
                ->where('freeplay_teams.user_id', '=', Auth::user()->id)
                ->where('confirmed', '=', 0)
                ->where('hidden', '=', 0)
                ->count();
            View::share('free_count', $free);

        }
	}

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{

			$this->layout = View::make($this->layout);
	    }
	}

}