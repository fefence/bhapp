<?php
use Illuminate\Database\Eloquent;

class Checker
{
    public static function getAllMatches()
    {
        $start = time();
        $send = false;
        $all_groups = Groups::where('state', '=', 2)->lists('id');
        $all_matches = Match::whereIn('groups_id', $all_groups)->get();
        $all_free = Match::join('freeplay', 'freeplay.match_id', '=', 'match.id')
            ->where('resultShort', '=', '-')
            ->select(DB::raw('`match`.*'))
            ->get();
        $text = "";
        foreach ($all_matches as $m) {
            $time = $m->matchTime;
            $date = $m->matchDate;
            $match = Parser::parseTimeDate($m);
            if ($time != $match->matchTime || $date != $match->matchDate) {
                $send = true;
                $body = "Updated date and time for " . $match->id . " \nold value: $date $time \nnewvalue: " . $match->matchDate . " " . $match->matchTime . "<br>";
                $text = $text . $body;
            }
        }
        foreach ($all_free as $m) {
            $time = $m->matchTime;
            $date = $m->matchDate;
            $match = Parser::parseTimeDate($m);
            if ($time != $match->matchTime || $date != $match->matchDate) {
                $send = true;
                $body = $match->home." - ".$match->away." old value: $date $time new value: " . $match->matchDate . " " . $match->matchTime . "<br>";
                $text = $text . $body;
            }
        }
        $text = $text . " execution time: " . (time() - $start) . " sec for " . count($all_matches) . " matches.";
        if ($send) {
            Mail::send('emails.email', ['data' => $text], function ($message) {
                $message->to(['wpopowa@gmail.com' => 'Vesela Popova', 'ludataaa@mail.bg' => 'Deniz Murat'])
                    ->subject('Changed match date or time');
            });
        }
        return $text;
    }
} 