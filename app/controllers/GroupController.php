<?php

class GroupController extends \BaseController
{

    public function getGroups($fromdate = "", $todate = "")
    {
        $data = LeagueDetails::getLeaguesWithMatches($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate);
        return View::make('games')->with(['data' => $data, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }

}
