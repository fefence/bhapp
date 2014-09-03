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
                $body = $match->id." ".$match->home." - ".$match->away." old value: $date $time new value: " . $match->matchDate . " " . $match->matchTime . "<br>";
                $text = $text . $body;
            }
        }
        foreach ($all_free as $m) {
            $time = $m->matchTime;
            $date = $m->matchDate;
            $match = Parser::parseTimeDate($m);
            if ($time != $match->matchTime || $date != $match->matchDate) {
                $send = true;
                $body = $match->id." ".$match->home." - ".$match->away." old value: $date $time new value: " . $match->matchDate . " " . $match->matchTime . "<br>";
                $text = $text . $body;
            }
        }
        $text = $text . " execution time: " . (time() - $start) . " sec for " . count($all_matches) . " matches.";
        if ($send) {
            Mail::send('emails.email', ['data' => $text], function ($message) {
                $message->to(['wpopowa@gmail.com' => 'Vesela Popova', 'fefence@gmail.com' => 'Deniz Murat'])
                    ->subject('Changed match date or time');
            });
        }
        return $text;
    }

    public static function getWrongGroups() {
        $send = false;
        $text = "Wrong groups for:<br>";
        $groups = Groups::where('state', '=', 2)->groupBy('league_details_id')->select(DB::raw("count(*) as c, league_details_id"))->get();
        foreach($groups as $gr) {
            if ($gr->c > 1) {
                $l = LeagueDetails::find($gr->league_details_id);
                $text = $text."id: ".$l->id."  ".$l->country."  ".$l->displayName."<br>";
            }
        }
        if ($send) {
            Mail::send('emails.email', ['data' => $text], function ($message) {
                $message->to(['wpopowa@gmail.com' => 'Vesela Popova'])
                    ->subject('Wrong groups');
            });
        }
        return $text;
    }
    
} 