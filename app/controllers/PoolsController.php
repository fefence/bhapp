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
        $aLog->action = "-";
        $aLog->amount = $amount;
        $aLog->element_id = $pool->id;
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
        $aLog->action = "+";
        $aLog->amount = $amount;
        $aLog->element_id = $pool->id;
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
}
