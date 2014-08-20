<?php
use Illuminate\Database\Eloquent;

class Checker {
    public static function getAllMatches() {
//        $start = time();
        $all_groups = Groups::where('state', '=', 2)->lists('id');
        $all_matches = Match::whereIn('groups_id', $all_groups)->get();
        $text = "";
        foreach ($all_matches as $match){
            $time = $match->matchTime;
            $date = $match->matchDate;
            $match = Parser::parseTimeDate($match);
            if ($time != $match->matchTime || $date != $match->matchDate) {
                $body = "Updated date and time for ".$match->id." \nold value: $date $time \nnewvalue: ".$match->matchDate." ".$match->matchTime."<br>";
                $text = $text.$body;
//                Mail::send('emails.email', ['data' => $body], function($message)
//                {
//                    $message->to('wpopowa@gmail.com', 'Vesela Popova')
//
//                        ->subject('Changed match date or time');
//                });
            }
        }
        return $text;
    }
} 