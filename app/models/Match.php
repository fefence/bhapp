<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Match extends Eloquent
{
    protected $table = 'match';

    public $timestamps = false;

    public static $unguarded = true;

    public function groups()
    {
        return $this->belongsTo('Groups');
    }

    public function odds()
    {
        return $this->hasMany('Odds1x2');
    }

    public function games()
    {
        return $this->hasMany('Games');
    }

    public function ppm()
    {
        return $this->hasMany('PPM');
    }

    /**
     * @param $match
     * @return array
     */
    public static function getMatchesForTeams($match)
    {
        $home = $match->home;
        $matchesH = Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function ($query) use ($home) {
                $query->where('home', '=', $home)
                    ->orWhere('away', '=', $home);
            })
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', 'desc')
            ->take(10)
            ->get(['home', 'away', 'homeGoals', 'awayGoals', 'matchDate', 'resultShort']);
        $away = $match->away;
        $matchesA = Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function ($query) use ($away) {
                $query->where('home', '=', $away)
                    ->orWhere('away', '=', $away);
            })
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', 'desc')
            ->take(10)
            ->get(['home', 'away', 'homeGoals', 'awayGoals', 'matchDate', 'resultShort']);

        $h2h = Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function ($query) use ($away) {
                $query->where('home', '=', $away)
                    ->orWhere('away', '=', $away);
            })
            ->where(function ($query) use ($home) {
                $query->where('home', '=', $home)
                    ->orWhere('away', '=', $home);
            })
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', 'desc')
            ->take(10)
            ->get(['home', 'away', 'homeGoals', 'awayGoals', 'matchDate', 'resultShort']);
        return array($home, $matchesH, $away, $matchesA, $h2h);
    }

    public static function matchesForSeason($leagueId, $season)
    {

        return Match::where('league_details_id', '=', $leagueId)
            ->where('season', '=', $season)
            ->orderBy('matchDate', 'ASC')
            ->orderBy('matchTime', 'ASC');

    }

    public static function matchesForSeasons($leagueId, $season)
    {

        return Match::where('league_details_id', '=', $leagueId)
            ->where('season', '>=', $season)
            ->orderBy('matchDate', 'ASC')
            ->orderBy('matchTime', 'ASC');

    }

    private static function get_http_response_code($url)
    {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    public static function updateMatchDetailsLivescore($match)
    {
//        $match = Match::find($match_id);
        $url = "http://www.livescore.in/match/" . $match->id . "/#match-summary";
        if (Match::get_http_response_code($url) != "200") {
            return "Wrong match details url! --> $url";

        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $table = $dom->getElementById("flashscore");
//        return $data;
        $rows = $table->getElementsByTagName("tr");

        $finished = trim($rows->item(2)->getElementsByTagName('td')->item(0)->nodeValue);
        if ($finished == null || trim($finished) == '') {
            $finished = trim($rows->item(3)->getElementsByTagName('td')->item(0)->nodeValue);
        }
        if ($finished == "Finished") {
            $res = explode('-', $rows->item(0)->getElementsByTagName('td')->item(2)->nodeValue);
            if (count($res) < 2) {
                $res = explode('-', $rows->item(1)->getElementsByTagName('td')->item(1)->nodeValue);
            }
            if ($res[0] > $res[1]) {
                $resultShort = 'H';
            } else if ($res[0] < $res[1]) {
                $resultShort = 'A';
            } else {
                $resultShort = 'D';
            }
            $match->homeGoals = $res[0];
            $match->awayGoals = $res[1];
            $match->resultShort = $resultShort;
        }

        $match->save();
        return $match;
    }

    public static function updateMatchDetails($match)
    {
        $baseUrl = "http://www.betexplorer.com/soccer/poland/ekstraklasa/";
        $url = $baseUrl . "matchdetails.php?matchid=" . $match->id;
        // echo "***  $matchId $url ***<br>";
        if (Match::get_http_response_code($url) != "200") {
            return "Wrong match details url! --> $url";

        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;
        $tables = $dom->getElementsByTagName('table');

        if ($tables->length > 0) {
            $date = $tables->item(0)->getElementsByTagName('tr')->item(0)->getElementsByTagName('th')->item(1)->nodeValue;
            $nums = explode('.', $date);
            $date = $nums[2] . "-" . $nums[1] . "-" . $nums[0];
//			$match->matchDate = $strdate;
        } else {
            return $url;
        }
        // echo "$matchId ";

        $scoreTable = $tables->item(1);
        $scoreRows = $scoreTable->getElementsByTagName('tr');

        $home = $scoreRows->item(0)->getElementsByTagName('th')->item(0)->nodeValue;
        $away = $scoreRows->item(0)->getElementsByTagName('th')->item(1)->nodeValue;

        $tmp = $scoreRows->item(1);
        $resultShort = '-';
        $homeGoals = 0;
        $awayGoals = 0;
        $reason = "";
        if ($tmp != null) {
            if ($tmp->getElementsByTagName('th')->length > 0) {
                $homeGoals = $tmp->getElementsByTagName('th')->item(0)->nodeValue;
                $awayGoals = $tmp->getElementsByTagName('th')->item(1)->nodeValue;
                if ($homeGoals > $awayGoals) {
                    $resultShort = 'H';
                } else if ($homeGoals < $awayGoals) {
                    $resultShort = 'A';
                } else {
                    $resultShort = 'D';
                }
            }
            if ($tmp->getElementsByTagName('td')->length > 0) {
                $reason = $tmp->getElementsByTagName('td')->item(0)->nodeValue;
                $match->state = $reason;
            }
        }
        $match->resultShort = $resultShort;
        $match->homeGoals = $homeGoals;
        $match->awayGoals = $awayGoals;
        // $match->reason = $reason;

        // $q1 = "UPDATE `match` set matchDate='$strdate', home='$home', away='$away', resultShort='$resultShort', homeGoals=$homeGoals, awayGoals=$awayGoals where id='$matchId'";

        if ($scoreRows->item(2) != null) {
            $results = $scoreRows->item(2)->getElementsByTagName('td')->item(0)->nodeValue;

            $h1 = "";
            $a1 = "";
            $h2 = "";
            $a2 = "";
            $state = '';
            if (strlen($results) > 2) {
                $results = str_replace('(', '', $results);
                $results = str_replace(')', '', $results);

                $halves = explode(', ', $results);
                if (count($halves) == 1) {
                    $state = $results;
                    $match->state = $state;
                } else {
                    $ha1 = explode(':', $halves[0]);
                    $h1 = $ha1[0];
                    $a1 = $ha1[1];
                    $ha2 = explode(':', $halves[1]);
                    $h2 = $ha2[0];
                    $a2 = $ha2[1];
                    $match->homeGoals1H = $h1;
                    $match->homeGoals2H = $h2;
                    $match->awayGoals1H = $a1;
                    $match->awayGoals2H = $a2;
                }
            }
        }
        $timestamp = strtotime(Match::parseTime($match->id));
        $time = date('H:i:s', $timestamp);
        $timestamp = strtotime($date . " " . $time) + 60 * 60;
        $match->matchTime = date('H:i:s', $timestamp);
        $match->matchDate = date('Y-m-d', $timestamp);
        $match->save();
        if ($tables->length == 3) {
            $class = $tables->item(2)->parentNode->getAttribute("class");
            if ($class == 'fr') {
                Match::processGoals($tables->item(2), "away", $match->id);
            } else if ($class = 'fl') {
                Match::processGoals($tables->item(2), "home", $match->id);
            }
        }
        if ($tables->length == 4) {
            $class = $tables->item(2)->parentNode->getAttribute("class");
            if ($class == 'fr') {
                Match::processGoals($tables->item(2), "away", $match->id);
                Match::processGoals($tables->item(3), "home", $match->id);
            } else if ($class = 'fl') {
                Match::processGoals($tables->item(2), "home", $match->id);
                Match::processGoals($tables->item(3), "away", $match->id);
            }
        }

        Match::getMatchOdds($match->id);
        return $match;
    }

    private static function processGoals($table, $team, $matchId)
    {

        $rows = $table->getElementsByTagName("tr");
        foreach ($rows as $row) {
            $minute = 0;
            $cols = $row->getElementsByTagName('td');
            $player = str_replace("'", "\'", $row->getElementsByTagName('th')->item(0)->nodeValue);
            if ($team == 'home') {
                $reason = $cols->item(0)->nodeValue;
                $minute = str_replace('.', "", $cols->item(1)->nodeValue);
                //$player = $cols->item(2)->nodeValue;
            } else {
                $reason = $cols->item(1)->nodeValue;
                $minute = str_replace('.', "", $cols->item(0)->nodeValue);
                //$player = $cols->item(1)->nodeValue;
            }
            if (trim($minute) == '') {
                $minute = 0;
            }
            $duplicate = Goals::where('match_id', '=', $matchId)->where('minute', '=', $minute)->where('player', '=', $player)->first();
            if ($duplicate) {

            } else {
                $goal = new Goals;
                $goal->match_id = $matchId;
                $goal->minute = $minute;
                $goal->player = $player;
                $goal->reason = $reason;
            }
        }
    }


    public static function getMatchOdds($matchId)
    {
        $bookies = Bookmaker::all();
        $bookmakers = array();
        foreach ($bookies as $b) {
            $bookmakers[$b->id] = $b->bookmakerName;
        }
        $url = "http://www.betexplorer.com/gres/ajax-matchodds.php?t=n&e=$matchId&b=1x2";
        $data = json_decode(file_get_contents($url))->odds;
        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;
        $table = $dom->getElementById('sortable-1');
        if ($table != null) {
            $rows = $table->getElementsByTagName('tr');
            for ($i = 0; $i < $rows->length; $i++) {
                $row = $rows->item($i);
                $cols = $row->getElementsByTagName('td');
                if ($cols->length > 3) {
                    $odds1 = $cols->item(1)->getAttribute("data-odd");
                    $oddsX = $cols->item(2)->getAttribute("data-odd");
                    $odds2 = $cols->item(3)->getAttribute("data-odd");
                    // $odds3 = $cols->item(3)->getAttribute("data-odd");
                    $h = $row->getElementsByTagName('th');
                    foreach ($h as $h1) {
                        foreach ($bookmakers as $key => $value) {
                            // echo $b[0]." ".$h1->nodeValue;
                            if (strpos($h1->nodeValue, $value)) {
                                $duplicate = Odds1x2::where('match_id', '=', $matchId)->where('bookmaker_id', '=', $key)->first();
                                if ($duplicate) {

                                } else {
                                    $odds = new Odds1x2;
                                    $odds->match_id = $matchId;
                                    $odds->bookmaker_id = $key;
                                    $odds->odds1 = $odds1;
                                    $odds->odds2 = $odds2;
                                    $odds->oddsX = $oddsX;
                                    $odds->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    public static function parseTime($matchId)
    {
        $url = "http://www.betexplorer.com/soccer/poland/ekstraklasa/piast-gliwice-slask-wroclaw/$matchId/";
        $data = file_get_contents($url);

        //echo "$url<br>";

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $dateElement = $dom->getElementById('nm-date')->getAttribute('data-dt');

        $arr = explode(',', $dateElement);
        $time = $arr[3] . ':' . $arr[4] . ":00";
        // print_r($dateElement);
        return $time;
    }

    public static function getNextMatchForTeam($team, $match)
    {

        return Match::where(function ($query) use ($team) {
            $query->where('home', '=', $team)
                ->orWhere('away', '=', $team);
        })
            ->where('matchDate', '>=', $match->matchDate)
            ->where('id', '<>', $match->id)
            ->orderBy('matchDate', 'asc')
            ->orderBy('matchTime', 'asc')
            ->first();
    }

    public static function getNextMatchForTeamLeague($team, $league_details_id)
    {

        $date = date('Y-m-d');

        return Match::where(function ($query) use ($team) {
            $query->where('home', '=', $team)
                ->orWhere('away', '=', $team);
        })
            ->where('matchDate', '>=', $date)
            ->where('league_details_id', '=', $league_details_id)
            ->orderBy('matchDate', 'asc')
            ->orderBy('matchTime', 'asc')
            ->first(['home', 'away', 'matchDate']);
    }


    public static function getAllMatchesForDates($fromdate, $todate, $todate2, $all_ids)
    {
        $matches = Match::join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->where(function ($q) use ($fromdate, $todate, $todate2) {
                $q->where(function ($q) use ($fromdate, $todate, $todate2) {
                    $q->where('matchDate', '>=', $fromdate)
                        ->where('matchDate', '<=', $todate);
                });
                $q->orWhere(function ($q) use ($fromdate, $todate, $todate2) {
                    $q->where('matchDate', '=', $todate2)
                        ->where('matchTime', '<', '11:00:00');
                });
            })
            ->whereIn('match.id', $all_ids)
            ->orderBy('matchDate', "asc")
            ->orderBy('matchTime')
            ->select(DB::raw("`match`.*, `leagueDetails`.country, `leagueDetails`.displayName, `leagueDetails`.alias, `leagueDetails`.ppm"))
            ->get();
        return $matches;
    }

    public static function last40matches($match)
    {
        $matches = Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function ($q) use ($match) {
                $q->where('matchDate', '<', $match->matchDate)
                    ->orWhere(function ($query) use ($match) {
                        $query->where('matchDate', '=', $match->matchDate)
                            ->where('matchTime', '<=', $match->matchTime);
                    });
            })
            ->where('season', '=', $match->season)
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', "desc")
            ->orderBy('matchTime', "desc")
            ->take(50)
            ->get();
        $arr = \Illuminate\Support\Collection::make($matches->all());
        return $arr->reverse();
    }
}

