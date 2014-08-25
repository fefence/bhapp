<?php

class StringsUtil
{

    public static function calculateHeading($fromdate, $todate, $league_details_id)
    {
        if ($fromdate == $todate && $fromdate == date('Y-m-d', time())) {
            $small = "today";
            $big = date('d M (D)', time());
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() + 86400)) {
            $small = "tomorow";
            $big = date('d M (D)', time() + 86400);
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() - 86400)) {
            $small = "yesterday";
            $big = date('d M (D)', time() - 86400);
            return array($big, $small);
        } else if ($fromdate == $todate) {
            $small = "";
            $big = date('d M (D)', strtotime($fromdate));
            return array($big, $small);
        } else {
            $small = "";
            $big = date('d M (D)', strtotime($fromdate)) . " to " . date('d M (D)', strtotime($todate));
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