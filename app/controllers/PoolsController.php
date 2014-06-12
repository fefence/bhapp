<?php

class PoolsController extends \BaseController {

	public function managePools(){
		$ppspoolsq = Pools::where('user_id', '=', Auth::user()->id)
			->where('pools.ppm', '=', 0)
			->join('leagueDetails', 'leagueDetails.id', '=', 'pools.league_details_id');
		$ppspools = $ppspoolsq->get();
		$ppstotal = $ppspoolsq->select(DB::raw("sum(profit) as profit, sum(account) as account, sum(amount) as amount"))->first();
		// return $ppstotal;
		$ppmpoolsq = Pools::where('user_id', '=', Auth::user()->id)
			->where('pools.ppm', '=', 1)
			->join('leagueDetails', 'leagueDetails.id', '=', 'pools.league_details_id');
		$ppmpools = $ppmpoolsq->get();
		$ppmtotal = $ppmpoolsq->select(DB::raw("sum(profit) as profit, sum(account) as account, sum(amount) as amount"))->first();
		
		$free = array();
		return View::make('poolmanagement')->with(['ppmpools' => $ppmpools, 'ppspools' => $ppspools, 'free' => $free, 'ppstotal' => $ppstotal, 'ppmtotal' => $ppmtotal]);
	}

	public function poolsGet(){
		$league = Input::get('league');
		$amount = Input::get('amount');
		$pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $league)->first();
		$main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
		$pool->amount = $pool->amount - $amount;
		$main->in_transit = $main->in_transit + $amount;
		$pool->save();
		$main->save();

		if ($pool->ppm == 0){
			$groups_id = Groups::where('league_details_id', '=', $league)->where('state', '=', 2)->first(['id'])->id;
			Updater::recalculateGroup($groups_id, Auth::user()->id);
		}
		return Redirect::back();
	}

	public function poolsInsert(){
		$league = Input::get('league');
		$amount = Input::get('amount');
		$pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $league)->first();
		$main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
		$pool->amount = $pool->amount + $amount;
		$main->in_transit = $main->in_transit - $amount;
		$pool->save();
		$main->save();
		if ($pool->ppm == 0){
			$groups_id = Groups::where('league_details_id', '=', $league)->where('state', '=', 2)->first(['id'])->id;
			Updater::recalculateGroup($groups_id, Auth::user()->id);
		}
		return Redirect::back();
	}
}
