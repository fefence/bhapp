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
        $main->in_transit = $main->in_transit + $amount;
        $pool->save();
        $main->save();
        return Redirect::back()->with("message", $amount. "€ removed from pool");
    }

    public function poolsInsert()
    {
        $id = Input::get('id');
        $amount = Input::get('amount');
        $pool = Pools::find($id);
        $main = CommonPools::where('user_id', '=', Auth::user()->id)->first();
        $pool->amount = $pool->amount + $amount;
        $main->in_transit = $main->in_transit - $amount;
        $pool->save();
        $main->save();
        return Redirect::back()->with("message", $amount. "€ added to pool");
    }
}
