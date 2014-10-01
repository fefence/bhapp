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
                $message->to(['wpopowa@gmail.com' => 'Vesela Popova'])
                    ->subject($subj);
            });
        }
        return $text;
        //, 'fefence@gmail.com' => 'Deniz Murat', 'stoykostoykov1913@gmail.com' => 'Stoyko Stoykov'
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
                $next_gr = Groups::where('league_details_id', '=', $match->league_details_id)->where('state', '=', 3)->first();
                Parser::parseMatchesForGroup(Groups::find($match->groups_id), $next_gr);
                Checker::checkPPM($match);
                $send = true;
                $count = $count + 1;
                $league = LeagueDetails::find($match->league_details_id);
                if (!str_contains($leagues_str, $league->alias)){
                    $leagues_str = $leagues_str . ", " . $league->country_alias;
                }
                $body = "<p>" . ucwords(str_replace('-', ' ', $league->country)) . " " . $league->displayName . "<br>" .
                    $match->home . " - " . $match->away . "<br>";
                if ($date != $match->matchDate){
                    $darr = explode('-', $date);
                    $dd = $darr[2];
                    $dm = $darr[1];
                    $dy = $darr[0];
                    $mdarr = explode('-', $match->matchDate);
                    $md = $mdarr[2];
                    $mm = $mdarr[1];
                    $my = $mdarr[0];
                    if ($md != $dd) {
                        $body = $body."<font color='red'><strong>".date('d', strtotime($match->matchDate)) ."</strong></font></span> ";
                    } else {
                        $body = $body.date('d', strtotime($match->matchDate))." ";
                    }
                    if ($mm != $dm) {
                        $body = $body."<font color='red'><strong>".date('M', strtotime($match->matchDate)) ."</strong></font></span> ";
                    } else {
                        $body = $body.date('M', strtotime($match->matchDate))." ";
                    }
                    if($my != $dy) {
                        $body = $body."<font color='red'><strong>".date('Y', strtotime($match->matchDate)) ."</strong></font></span>";
                    } else {
                        $body = $body.date('Y', strtotime($match->matchDate));
                    }
                    $body = $body.", ";
                } else {
                    $body = $body.date('d M Y', strtotime($match->matchDate)) . ", ";
                }
                if ($time != $match->matchTime){
                    $body = $body."<font color='red'><strong>".substr($match->matchTime, 0, strlen($match->matchTime) - 3)."</strong></font><span>";
                } else {
                    $body = $body.substr($match->matchTime, 0, strlen($match->matchTime) - 3);
                }
                $body = $body. " (was " . date('d M Y', strtotime($date)) . " " . substr($time, 0, strlen($time) - 3) . ") <br>[" . $match->id . "]</p>";
                $text = $text . $body;
            }
        }
        return array($send, $count, $leagues_str, $text);
    }

    public static function checkPPM($match) {
        $ppms = PPM::where('match_id', '=', $match->id)->get();
        if (count($ppms) > 0) {
            $first = Match::where('league_details_id', '=', $match->league_details_id)
                ->where('resultShort', '=', '-')
                ->orderBy('matchDate')
                ->orderBy('matchTime')
                ->first();
            $all = Match::where('league_details_id', '=', $match->league_details_id)
                ->where('resultShort', '=', '-')
                ->where('matchDate', '=', $first->matchDate)
                ->where('matchTime', '=', $first->matchTime)
                ->lists('id');
            if (!in_array($match->id, $all)) {
                foreach($ppms as $ppm) {
                    if($ppm->confirmed == 0){
                        $ppm->delete();
                    } else {
                        $pool = Pools::where('league_details_id', '=', $match->league_details_id)
                            ->where('user_id', '=', $ppm->user_id)
                            ->where('game_type_id', '=', $ppm->game_type_id)
                            ->first();
                        $pool->account = $pool->account + $ppm->bet;
                        $pool->save();
                        $ppm->delete();
                    }
                }
                $settings = Settings::where('league_details_id', '=', $match->league_details_id)
                    ->where('game_type_id', '>', 4)
                    ->where('game_type_id', '<', 9)
                    ->get();
                foreach($settings as $sett) {
                    $pool = Pools::where('league_details_id', '=', $sett->league_details_id)
                        ->where('user_id', '=', $sett->user_id)
                        ->where('game_type_id', '=', $sett->game_type_id)
                        ->first();
                    $series = Series::where('active', '=', 1)
                        ->where('league_details_id', '=', $sett->league_details_id)
                        ->where('game_type_id', '=', $sett->game_type_id)
                        ->first();
                    foreach($all as $id) {
                        $p = PPM::firstOrCreate(['user_id' => $sett->user_id, 'game_type_id' => $sett->game_type_id, 'match_id' => $id]);
                        $p->bsf = $pool->bsf/count($all);
                        $p->series_id = $series->id;
                        $p->current_length = $series->current_length;
                        $p->save();
                        $series->end_match_id = $id;
                        $series->save();
                    }
                }
            }
        }
    }

    public static function updateMissedGroups() {
        $gr = Groups::where('state', '=', 2)->get();
        foreach($gr as $group) {
            $m = $group->matches()->where('resultShort', '=', '-')->count();
            if ($m == 0) {
                echo $group->league_details_id." ";
                Updater::updateGroup($group->id);
            }
        }
    }
} 