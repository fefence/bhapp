<?php

class PoolsController extends \BaseController
{

    public function managePools()
    {
        $user_id = Auth::user()->id;
        $ppspoolsq = Pools::getPPMPoolsQForUser($user_id);
        $ppspools = $ppspoolsq->get();
        $ppstotal = $ppspoolsq->select(DB::raw("sum(profit) as profit, sum(account) as account, sum(amount) as amount"))->first();
        $ppmpoolsq = Pools::getPPSPoolsQForUser($user_id);
        $ppmpools = $ppmpoolsq->get();
        $ppmtotal = $ppmpoolsq->select(DB::raw("sum(profit) as profit, sum(account) as account, sum(amount) as amount"))->first();

        $free = array();
        return View::make('poolmanagement')->with(['ppmpools' => $ppmpools, 'ppspools' => $ppspools, 'free' => $free, 'ppstotal' => $ppstotal, 'ppmtotal' => $ppmtotal]);
    }

    public function poolsGet()
    {
        $id = Input::get('id');
        $amount = Input::get('amount');
        $pool = Pools::find($id);
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $pool->amount = $pool->amount - $amount;
        $pool->current = $pool->amount;
        $main->in_transit = $main->in_transit + $amount;
        $pool->save();
        $main->save();

        if ($pool->ppm == 0) {
            $groups_id = Groups::where('league_details_id', '=', $pool->league_details_id)->where('state', '=', 2)->first(['id'])->id;
            Updater::recalculateGroup($groups_id, Auth::user()->id);
        } else {
        }
        return Redirect::back();
    }

    public function poolsInsert()
    {
        $id = Input::get('id');
        $amount = Input::get('amount');
        $pool = Pools::find($id);
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $pool->amount = $pool->amount + $amount;
        $pool->current = $pool->amount;
        $main->in_transit = $main->in_transit - $amount;
        $pool->save();
        $main->save();
        if ($pool->ppm == 0) {
            $groups_id = Groups::where('league_details_id', '=', $pool->league_details_id)->where('state', '=', 2)->first(['id'])->id;
            Updater::recalculateGroup($groups_id, Auth::user()->id);
        }
        return Redirect::back();
    }
}
