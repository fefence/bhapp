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
        $count = 0;
        $leagues_str = "";
        list($send, $count, $leagues_str, $text) = self::checkMatches($all_matches, $count, $leagues_str, $text, $send);
        list($send, $count, $leagues_str, $text) = self::checkMatches($all_free, $count, $leagues_str, $text, $send);
        $text = $text . (time() - $start) . " sec for " . count($all_matches) . " matches.";
        if ($count > 1) {
            $subj = "Rescheduled ".$count." matches (".substr(trim($leagues_str), 2).")";
        } else {
            $subj = "Rescheduled match (".substr(trim($leagues_str), 2).")";
        }

        if ($send) {
            Mail::send('emails.email', ['data' => $text], function ($message) use ($subj){
                $message->to(['wpopowa@gmail.com' => 'Vesela Popova', 'fefence@gmail.com' => 'Deniz Murat', 'stoykostoykov1913@gmail.com' => 'Stoyko Stoykov'])
                    ->subject($subj);
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

    public static function checkMatches($all_matches, $count, $leagues_str, $text, $send)
    {
        foreach ($all_matches as $m) {
            $time = $m->matchTime;
            $date = $m->matchDate;
            $match = Parser::parseTimeDate($m);
            if ($time != $match->matchTime || $date != $match->matchDate) {
                $send = true;
                $count = $count + 1;
                $league = LeagueDetails::find($match->league_details_id);
                $leagues_str = $leagues_str . ", " . $league->country_alias;
                $body = "<p>" . ucwords(str_replace('-', ' ', $league->country)) . " " . $league->displayName . "<br>" .
                    $match->home . " - " . $match->away . "<br>";
                if ($date != $match->matchDate){
                    $body = $body."<strong>".date('d M Y', strtotime($match->matchDate)) ."</strong> ";
                } else {
                    $body = $body.date('d M Y', strtotime($match->matchDate)) . " ";
                }
                if ($time != $match->matchTime){
                    $body = $body."<strong>".substr($match->matchTime, 0, strlen($match->matchTime) - 3)."</strong>";
                } else {
                    $body = $body.substr($match->matchTime, 0, strlen($match->matchTime) - 3);
                }
                $body = $body. " (was " . date('d M Y', strtotime($date)) . " " . substr($time, 0, strlen($time) - 3) . ") <br>[" . $match->id . "]</p>";
                $text = $text . $body;
            }
        }
        return array($send, $count, $leagues_str, $text);
    }

} 