<?php

class HeadingsUtil
{

    public static function calculateHeading($fromdate, $todate)
    {
        if ($fromdate == $todate && $fromdate == date('Y-m-d', time())) {
            $big = "Today's matches";
            $small = date('d-M-y (D)', time());
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() + 86400)) {
            $big = "Tomorow's matches";
            $small = date('d-M-y (D)', time() + 86400);
            return array($big, $small);
        } else if ($fromdate == $todate && $fromdate == date('Y-m-d', time() - 86400)) {
            $big = "Yesterdays's matches";
            $small = date('d-M-y (D)', time() - 86400);
            return array($big, $small);
        } else if ($fromdate == $todate) {
            $big = "Matches";
            $small = date('d-M-y (D)', strtotime($fromdate));
            return array($big, $small);
        } else {
            $big = "Matches";
            $small = date('d-M-y (D)', strtotime($fromdate)) . " to " . date('d-M-y (D)', strtotime($todate));
            return array($big, $small);
        }
    }
}