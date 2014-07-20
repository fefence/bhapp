<?php

class StringsUtil
{

    public static function calculateHeading($fromdate, $todate, $league_details_id)
    {
        $img = "";
        if ($league_details_id != '') {
            $league = LeagueDetails::find($league_details_id);
            $country = strtoupper($league->country);
            $img = "<img src='/images/".$country.".png'> ";
        }
        if ($fromdate == $todate && $fromdate == date('Y-m-d', time())) {
            $big = $img."Today's matches";
            $small = date('d M (D)', time());
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() + 86400)) {
            $big = $img."Tomorow's matches";
            $small = date('d M (D)', time() + 86400);
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() - 86400)) {
            $big = $img."Yesterdays's matches";
            $small = date('d M (D)', time() - 86400);
            return array($big, $small);
        } else if ($fromdate == $todate) {
            $big = $img."Matches";
            $small = date('d M (D)', strtotime($fromdate));
            return array($big, $small);
        } else {
            $big = $img."Matches";
            $small = date('d M (D)', strtotime($fromdate)) . " to " . date('d-M-y (D)', strtotime($todate));
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