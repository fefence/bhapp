<?php

class StringsUtil
{

    public static function calculateHeading($fromdate, $todate, $league_details_id)
    {
        $league = "";
        if ($league_details_id != '') {
            $l = LeagueDetails::find($league_details_id);
            $league = " for ".$l->country."/".$l->fullName;
        }
        if ($fromdate == $todate && $fromdate == date('Y-m-d', time())) {
            $big = "Today's matches".$league;
            $small = date('d-M-y (D)', time());
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() + 86400)) {
            $big = "Tomorow's matches".$league;
            $small = date('d-M-y (D)', time() + 86400);
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() - 86400)) {
            $big = "Yesterdays's matches".$league;
            $small = date('d-M-y (D)', time() - 86400);
            return array($big, $small);
        } else if ($fromdate == $todate) {
            $big = "Matches".$league;
            $small = date('d-M-y (D)', strtotime($fromdate));
            return array($big, $small);
        } else {
            $big = "Matches".$league;
            $small = date('d-M-y (D)', strtotime($fromdate)) . " to " . date('d-M-y (D)', strtotime($todate));
            return array($big, $small);
        }
    }

    /**
     * @param $fromdate
     * @param $todate
     * @return array
     */
    public static function calculateDates($fromdate, $todate)
    {
        if ($fromdate == "") {
            $fromdate = date("Y-m-d", time());
        }
        if ($todate == "") {
            $todate = date("Y-m-d", time());
            return array($fromdate, $todate);
        }
        return array($fromdate, $todate);
    }
}