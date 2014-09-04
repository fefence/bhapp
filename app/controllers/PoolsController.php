<?php

class PoolsController extends \BaseController
{

    public function managePools()
    {
        $user_id = Auth::user()->id;
        $ppspoolsq = Pools::getPPSPoolsQForUser($user_id);
        $ppspools = $ppspoolsq->get();
        $ppstotal = $ppspoolsq->select(DB::raw("sum(profit) as profit, sum(account) as account, sum(amount) as amount"))->first();
        $ppmpoolsq = Pools::getPPMPoolsQForUser($user_id);
        $ppmpools = $ppmpoolsq->get();
        $ppmtotal = $ppmpoolsq->select(DB::raw("sum(profit) as profit, sum(account) as account, sum(amount) as amount"))->first();
        $freepoolsq = Pools::getFreePoolsQForUser($user_id);
        $freepools = $freepoolsq->get();
//        return $freepools;
        return View::make('poolmanagement')->with(['ppmpools' => $ppmpools, 'ppspools' => $ppspools, 'free' => $freepools, 'ppstotal' => $ppstotal, 'ppmtotal' => $ppmtotal]);
    }

    public function poolsGet($free = 'false')
    {
        $id = Input::get('id');
        $amount = Input::get('amount');
        if ($free == "false") {
            $pool = Pools::find($id);
        } else {
            $pool = FreePool::find($id);
        }
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $old = $pool->amount;
        $pool->amount = $pool->amount - $amount;
        $main->in_transit = $main->in_transit + $amount;
        $pool->save();
        $main->save();
        $log = new PoolLog;
        $log->pools_id = $pool->id;
        $log->amount = $amount;
        $log->action = "-";
        $log->save();
        $aLog = new ActionLog;
        $aLog->type = "pools";
        $aLog->action = "substract";
        $aLog->amount = $amount;
        $aLog->element_id = $pool->id;
        $aLog->user_id = $pool->user_id;
        $aLog->description = $amount." removed. Old value ".$old.", new value ".$pool->amount;
        $aLog->league_details_id = $pool->league_details_id;
        $aLog->game_type_id = $pool->game_type_id;
        $aLog->save();
        if ($free == "false" && $pool->game_type_id >= 5 && $pool->game_type_id <= 8) {
            $ppms = PPM::join('match', 'match.id', '=', 'ppm.match_id')
                ->where('resultShort', '=', '-')
                ->where('confirmed', '=', 0)
                ->where('user_id', '=', $pool->user_id)
                ->where('game_type_id', '=', $pool->game_type_id)
                ->where('league_details_id', '=', $pool->league_details_id)
                ->select(DB::raw('ppm.*, match.resultShort'))
                ->get();
            foreach($ppms as $ppm) {
                $ppm->bsf = ($pool->amount)/count($ppms);
                $ppm->save();
            }
        }
        if ($free == "true") {
            $fgame = FreeGames::join('freeplay_teams', 'freeplay_teams.team_id', '=', 'freeplay.team_id')
                ->where('freeplay.user_id', '=', $pool->user_id)
                ->where('freeplay.team_id', '=', $pool->team_id)
                ->join('match', 'freeplay.match_id', '=', "match.id")
                ->where('resultShort', '=', '-')
                ->select(DB::raw('freeplay.*, match.resultShort'))
                ->first();
//            return $fgame;
            $fgame->bsf = $pool->amount;
            $fgame->save();
        }
        return Redirect::back()->with("message", $amount. "€ removed from pool");
    }

    public function poolsToAccount($free = 'false')
    {
        $id = Input::get('id');
        $amount = Input::get('amount');
        if ($free == "false") {
            $pool = Pools::find($id);
        } else {
            $pool = FreePool::find($id);
        }
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $old_pool = $pool->amount;
        $old_account = $pool->account;
        $pool->amount = $pool->amount - $amount;
        $pool->account = $pool->account + $amount;
        $main->account = $main->account + $amount;
        $main->amount = $main->amount - $amount;
//        $main->in_transit = $main->in_transit + $amount;
        $pool->save();
        $main->save();
        $aLog = new ActionLog;
        $aLog->type = "pools";
        $aLog->action = "to account";
        $aLog->amount = $amount;
        $aLog->element_id = $pool->id;
        $aLog->user_id = $pool->user_id;
        $aLog->description = $amount." removed. Old pool ".$old_pool.", new pool ".$pool->amount.". Old account ".$old_account.", new account ".($old_account + $amount);
        $aLog->league_details_id = $pool->league_details_id;
        $aLog->game_type_id = $pool->game_type_id;
        $aLog->save();
        if ($free == "false" && $pool->game_type_id >= 5 && $pool->game_type_id <= 8) {
            $ppms = PPM::join('match', 'match.id', '=', 'ppm.match_id')
                ->where('resultShort', '=', '-')
                ->where('confirmed', '=', 0)
                ->where('user_id', '=', $pool->user_id)
                ->where('game_type_id', '=', $pool->game_type_id)
                ->where('league_details_id', '=', $pool->league_details_id)
                ->select(DB::raw('ppm.*, match.resultShort'))
                ->get();
            foreach($ppms as $ppm) {
                $ppm->bsf = ($pool->amount)/count($ppms);
                $ppm->save();
            }
        }
        if ($free == "true") {
            $fgame = FreeGames::join('freeplay_teams', 'freeplay_teams.team_id', '=', 'freeplay.team_id')
                ->where('freeplay.user_id', '=', $pool->user_id)
                ->where('freeplay.team_id', '=', $pool->team_id)
                ->join('match', 'freeplay.match_id', '=', "match.id")
                ->where('resultShort', '=', '-')
                ->select(DB::raw('freeplay.*, match.resultShort'))
                ->first();
//            return $fgame;
            $fgame->bsf = $pool->amount;
            $fgame->save();
        }
        return Redirect::back()->with("message", $amount. "€ removed from pool and added to account");
    }

    public function poolsInsert($free = 'false')
    {
//        return $free;
        $id = Input::get('id');
//        return $id;
        $amount = Input::get('amount');
        if ($free == "false") {
            $pool = Pools::find($id);
        } else {
            $pool = FreePool::find($id);
        }
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $old = $pool->amount;
        $pool->amount = $pool->amount + $amount;
        $main->in_transit = $main->in_transit - $amount;
        $pool->save();
        $main->save();
        $log = new PoolLog;
        $log->pools_id = $pool->id;
        $log->amount = $amount;
        $log->action = "+";
        $log->save();
        $aLog = new ActionLog;
        $aLog->type = "pools";
        $aLog->action = "add";
//        $aLog->amount = $amount;
        $aLog->element_id = $pool->id;
        $aLog->user_id = $pool->user_id;
        $aLog->description = $amount." added. Old value ".$old.", new value ".$pool->amount;
        $aLog->league_details_id = $pool->league_details_id;
        $aLog->game_type_id = $pool->game_type_id;
        $aLog->save();
        if ($free == "false" && $pool->game_type_id >= 5 && $pool->game_type_id <= 8) {
            $ppms = PPM::join('match', 'match.id', '=', 'ppm.match_id')
                ->where('resultShort', '=', '-')
                ->where('confirmed', '=', 0)
                ->where('user_id', '=', $pool->user_id)
                ->where('game_type_id', '=', $pool->game_type_id)
                ->where('league_details_id', '=', $pool->league_details_id)
                ->select(DB::raw('ppm.*, match.resultShort'))
                ->get();
            foreach($ppms as $ppm) {
                $ppm->bsf = ($pool->amount)/count($ppms);
                $ppm->save();
            }
        }
        if ($free == "true") {
            $fgame = FreeGames::join('freeplay_teams', 'freeplay_teams.team_id', '=', 'freeplay.team_id')
                ->where('freeplay.user_id', '=', $pool->user_id)
                ->where('freeplay.team_id', '=', $pool->team_id)
                ->join('match', 'freeplay.match_id', '=', "match.id")
                ->where('resultShort', '=', '-')
                ->select(DB::raw('freeplay.*, match.resultShort'))
                ->first();
//            return $fgame;
            $fgame->bsf = $pool->amount;
            $fgame->save();
        }
        return Redirect::back()->with("message", $amount. "€ added to pool");
    }

    public static function resetPPSPool() {
        $id = Input::get('id');
        $pool = Pools::find($id);
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $log = new PoolLog;
        $log->pools_id = $pool->id;
        $log->action = "reset";
        $log->save();
        $aLog = new ActionLog;
        $aLog->type = "pools";
        $aLog->action = "reset";
        $aLog->amount = $pool->account;
        $aLog->element_id = $pool->id;
        $aLog->user_id = $pool->user_id;
        $aLog->league_details_id = $pool->league_details_id;
        $aLog->description = "Pool reset. Pool removed ".$pool->amount.", profit added ".$pool->account;
        $aLog->game_type_id = $pool->game_type_id;
        $aLog->save();

        $pool->profit = $pool->profit + $pool->account;
        $main->profit = $main->profit + $pool->account;
        $main->amount = $main->amount - $pool->amount;
        $pool->amount = 0;
        $main->amount = $main->account - $pool->account;
        $pool->account = 0;
        $pool->save();
        $main->save();


        $gr = Groups::where('league_details_id', '=', $pool->league_details_id)->where('state', '=', 2)->first();
        GamesController::recalculateGroup($gr->id);
        return Redirect::back()->with("message", "Pool reset recalc needed");

    }
}
