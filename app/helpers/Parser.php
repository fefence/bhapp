<?php

use Illuminate\Database\Eloquent;

class Parser
{

    public static function parseTimeDate($match)
    {
        $baseUrl = "http://www.betexplorer.com/soccer/poland/ekstraklasa/";
        $url = $baseUrl . "matchdetails.php?matchid=" . $match->id;
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;
        $tables = $dom->getElementsByTagName('table');

        if ($tables->length > 0) {
            $date = $tables->item(0)->getElementsByTagName('tr')->item(0)->getElementsByTagName('th')->item(1)->nodeValue;
            $nums = explode('.', $date);
            $date = $nums[2] . "-" . $nums[1] . "-" . $nums[0];
//            $match->matchDate = $strdate;
        } else {
            return $url;
        }
        // echo "$matchId ";

        $timestamp = strtotime(Match::parseTime($match->id));
        $time = date('H:i:s', $timestamp);
        $timestamp = strtotime($date . " " . $time) + 60 * 60;
        $match->matchTime = date('H:i:s', $timestamp);
        $match->matchDate = date('Y-m-d', $timestamp);
        $match->save();
        return $match;
    }

    public static function parseMatchOddsForGames($games)
    {
        // return "boo";
        $warn = false;
        $oddsarr = array();
        foreach ($games as $game) {
            if ($game->game_type_id > 5) {
                if (!array_key_exists($game->match_id, $oddsarr)) {
                    $oddsarr[$game->match_id] = Parser::parseOdds(Match::find($game->match_id));
                }
                if (count($oddsarr[$game->match_id]) == 0) {
                    continue;
                }
                $oddsX = $oddsarr[$game->match_id][$game->game_type_id];
                if ($oddsX == 0 || $oddsX == -1) {
                    $warn = true;
                    $game->odds = $oddsX;
                    $game->save();
                } else {
                    $game->odds = $oddsX;
                    $game->income = $oddsX * $game->bet;
                    $game->save();
                }
                continue 1;
            }
            $game->odds = -1;
            $game->save();
            $matchId = $game->match_id;
            // return $matchId;
            $bookmaker = Bookmaker::find($game->bookmaker_id);

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
                        $oddsX = $cols->item(2)->getAttribute("data-odd");
//                         return $oddsX;
                        // $odds3 = $cols->item(3)->getAttribute("data-odd");
                        $h = $row->getElementsByTagName('th');
                        foreach ($h as $h1) {
                            if (strpos($h1->nodeValue, $bookmaker->bookmakerName)) {
                                $game->odds = $oddsX;
                                $game->income = $oddsX * $game->bet;
                                $game->save();
                                break 1;
                            }
                        }
                    }
                }
            }
            if ($game->odds == 0 || $game->odds == -1) {
                $warn = true;
            }
        }
        return $warn;
    }

    public static function parseLeagueSeries($league_details_id)
    {
//        return Parser::parseLeagueStandings($league_details_id);
//        $league = LeagueDetails::find($league_details_id);
//        if ($league->pps == 0) {
//            return Parser::parseLeagueStandings($league_details_id);
//        }
//        $baseUrl = "http://www.betexplorer.com/soccer/";
//        $url = $baseUrl . $league->country . "/" . $league->fullName . "/standings/?table=table&table_sub=overall";
//
//        if (Parser::get_http_response_code($url) != "200") {
//            return "Wrong league stats url! --> $url";
//        }
//        $data = file_get_contents($url);
//
//        $dom = new domDocument;
//
//        @$dom->loadHTML($data);
//        $dom->preserveWhiteSpace = false;
//
//        $finder = new DomXPath($dom);
//        $classname = "stats-table";
//        $nodes = $finder->query("//*[contains(@class, '$classname')]");
//        $table = $nodes->item(4);
//        if ($table != null) {
//            $rows = $table->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//        } else {
//            $table = $nodes->item(0);
//            $rows = $table->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//            foreach ($rows as $row) {
//                $cols = $row->getElementsByTagName('td');
//                $place = trim($cols->item(0)->nodeValue);
//                $team = trim($cols->item(1)->nodeValue);
//                $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
//                $stand->streak = 0;
//                $stand->place = explode(".", $place)[0];
//                $stand->save();
//            }
//            $baseUrl = "http://www.betexplorer.com/soccer/";
//            $league = LeagueDetails::find($league_details_id);
//            $url = $baseUrl . $league->country . "/" . $league->fullName . "-2013-2014/";
//
//            if (Parser::get_http_response_code($url) != "200") {
//                return "Wrong league stats url! --> $url";
//            }
//            $data = file_get_contents($url);
//
//            $dom = new domDocument;
//
//            @$dom->loadHTML($data);
//            $dom->preserveWhiteSpace = false;
//
//            $finder = new DomXPath($dom);
//            $classname = "stats-table result-table";
//            $nodes = $finder->query("//*[contains(@class, '$classname')]");
//            $table = $nodes->item(4);
//            $rows = $table->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//        }
//
//        foreach ($rows as $row) {
//            $cols = $row->getElementsByTagName('td');
//            $place = trim($cols->item(0)->nodeValue);
//            $team = trim($cols->item(1)->nodeValue);
//            $streak = trim($cols->item(6)->nodeValue);
//            $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
//            if ($stand != null) {
//                $stand->streak = $streak;
//                $stand->place = explode(".", $place)[0];
//                $stand->save();
//            }
//        }

    }

    public static function parseLeagueStandings($league_details_id)
    {
        return;
        $league = LeagueDetails::find($league_details_id);
//        if ($league->pps == 0) {
//            return Parser::parseLeagueStandings($league_details_id);
//        }
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/standings/?table=table";
//        return $url;
        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong league stats url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $table = $dom->getElementById("table-type-1");
        $rows = $table->getElementsByTagName("tr");
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName("td");
            if ($cols->length > 1) {
                $place = explode(".", $cols->item(0)->nodeValue)[0];
                $team = $cols->item(1)->nodeValue;
                $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
                $stand->place = $place;
                $stand->save();
//                echo "$place $team <br>";
            }
//            echo "<br>";
        }
//        return $table;
//        $baseUrl = "http://www.betexplorer.com/soccer/";
//        $league = LeagueDetails::find($league_details_id);
//        $url = $baseUrl . $league->country . "/" . $league->fullName . "/";
//
//        if (Parser::get_http_response_code($url) != "200") {
//            return "Wrong league stats url! --> $url";
//        }
//        $data = file_get_contents($url);
//
//        $dom = new domDocument;
//
//        @$dom->loadHTML($data);
//        $dom->preserveWhiteSpace = false;
//
//        $finder = new DomXPath($dom);
//        $classname = "stats-table result-table";
//        $nodes = $finder->query("//*[contains(@class, '$classname')]");
//        $table = $nodes->item(4);
//        if ($table != null) {
//            $rows = $table->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//        } else {
//            $table = $nodes->item(0);
//            $rows = $table->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//        }
//            foreach ($rows as $row) {
//                $cols = $row->getElementsByTagName('td');
//                $place = trim($cols->item(0)->nodeValue);
//                $team = trim($cols->item(1)->nodeValue);
//                $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
//                $stand->place = explode(".", $place)[0];
//                $stand->save();
//            }
    }

    public static function parseLeagueSeriesUSA($league_details_id)
    {
//        $baseUrl = "http://www.betexplorer.com/soccer/";
//        $league = LeagueDetails::find($league_details_id);
//        $url = $baseUrl . $league->country . "/" . $league->fullName . "/";
//
//        if (Parser::get_http_response_code($url) != "200") {
//            return "Wrong league stats url! --> $url";
//        }
//        $data = file_get_contents($url);
//
//        $dom = new domDocument;
//
//        @$dom->loadHTML($data);
//        $dom->preserveWhiteSpace = false;
//
//        $finder = new DomXPath($dom);
//        $classname = "stats-table result-table";
//        $nodes = $finder->query("//*[contains(@class, '$classname')]");
//        for ($a = 8; $a <= 9; $a++) {
//            $rows = $nodes->item($a)->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//            foreach ($rows as $row) {
//                $cols = $row->getElementsByTagName('td');
//                $place = trim($cols->item(0)->nodeValue);
//                $team = trim($cols->item(1)->nodeValue);
//                $streak = trim($cols->item(6)->nodeValue);
//                $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
//                $stand->streak = $streak;
//                $stand->place = explode(".", $place)[0];
//                $stand->save();
//            }
//        }
    }

    public static function parseLeagueStandingsUSA($league_details_id)
    {
//        $baseUrl = "http://www.betexplorer.com/soccer/";
//        $league = LeagueDetails::find($league_details_id);
//        $url = $baseUrl . $league->country . "/" . $league->fullName . "/";
//
//        if (Parser::get_http_response_code($url) != "200") {
//            return "Wrong league stats url! --> $url";
//        }
//        $data = file_get_contents($url);
//
//        $dom = new domDocument;
//
//        @$dom->loadHTML($data);
//        $dom->preserveWhiteSpace = false;
//
//        $finder = new DomXPath($dom);
//        $classname = "stats-table result-table";
//        $nodes = $finder->query("//*[contains(@class, '$classname')]");
//        for ($a = 8; $a <= 9; $a++) {
//            $rows = $nodes->item($a)->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
//            foreach ($rows as $row) {
//                $cols = $row->getElementsByTagName('td');
//                $place = trim($cols->item(0)->nodeValue);
//                $team = trim($cols->item(1)->nodeValue);
//                $streak = trim($cols->item(6)->nodeValue);
//                $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
//                $stand->streak = $streak;
//                $stand->place = explode(".", $place)[0];
//                $stand->save();
//            }
//        }
    }

    public static function parseMatchesForGroup($current, $next)
    {
        $start = time();
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $tail = "fixtures/";

//        return $current;
        $league = LeagueDetails::findOrFail($current->league_details_id);
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/" . $tail;

//        return $url;
        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong fixtures url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $finder = new DomXPath($dom);
        $classname = "result-table";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        $rows = $nodes->item(0)->getElementsByTagName("tr");
        $time = "";
        $date = "";
        $home = "";
        $away = "";
        $id = "";
        $group = $current;
        $first = true;
        foreach ($rows as $row) {
            $headings = $row->getElementsByTagName('th');
            if ($headings->length > 0) {
                if (str_contains($headings->item(0)->nodeValue, '. Round') && $group->id == $next->id && !$first) {
                    break 1;
                }
                if (str_contains($headings->item(0)->nodeValue, '. Round') && $group->id != $next->id && !$first) {
                    $group = $next;
                } else {
                    $first = false;
                }

            }
            $cols = $row->getElementsByTagName('td');
            if ($cols->length > 1) {
                $a = $cols->item(1)->getElementsByTagName('a');
                foreach ($a as $link) {
                    $href = $link->getAttribute("href");
                    $arr = explode("/", $href);
                    $id = $arr[count($arr) - 2];
                }
                if (strlen($cols->item(0)->nodeValue) > 3) {
                    $tmp = explode(" ", $cols->item(0)->nodeValue);
                    $time = $tmp[1] . ":00";
                    $datetmp = explode(".", $tmp[0]);
                    $date = $datetmp[2] . "-" . $datetmp[1] . "-" . $datetmp[0];
                }
                if (strlen($cols->item(1)->nodeValue) > 0) {
                    $home = explode(' - ', $cols->item(1)->nodeValue)[0];
                    $away = explode(' - ', $cols->item(1)->nodeValue)[1];
                }
                $match = Match::firstOrNew(array('id' => $id));
                $match->home = $home;
                $match->away = $away;
                $timestamp = strtotime($date . " " . $time) + 60 * 60;
                $match->matchTime = date('H:i:s', $timestamp);
                $match->matchDate = date('Y-m-d', $timestamp);
                $match->groups_id = $group->id;
                $match->resultShort = '-';
                $match->round = $group->round;
                $match->season = '2014-2015';
                $match->league_details_id = $current->league_details_id;
                $match->save();
            }
        }
        $curr = $current->matches()->orderBy('matchDate')->get();
        $firstOfNext = $next->matches()->orderBy('matchDate')->first();
        foreach ($curr as $m) {
            if ($firstOfNext!= null && $m->matchDate > $firstOfNext->matchDate) {
                $m->groups_id = 0;
                $m->save();
            }
        }

        $teams = Standings::where('league_details_id', '=', $current->league_details_id)->lists('team');
        $tmp = array();
        foreach ($teams as $team) {
            $matches = $current->matches()->orderBy('matchDate')->where(function ($q) use ($team) {
                $q->where('home', '=', $team)
                    ->orWhere('away', '=', $team);
            })
                ->orderBy('matchDate', 'asc')
                ->lists('id');
            if (count($matches) > 1) {
//                return $matches;
                for($i = 1; $i < count($matches); $i ++) {
                    array_push($tmp, $matches[$i]);
                }
            }
        }
        if(count($tmp) > 0) {
            $next_matches = $next->matches()->get();
            foreach($next_matches as $n) {
                $n->groups_id = 0;
                $n->save();
            }
            foreach($tmp as $id) {
                $match = Match::find($id);
                $match->groups_id = $next->id;
                $match->save();
                $games = Games::where('match_id', '=', $id)->get();
                foreach($games as $game) {
                    if ($game->confirmed == 1) {
                        $pool = Pools::where('user_id', '=', $game->user_id)->where('league_details_id', '=', $match->league_details_id)->where('game_type_id', '=', 1)->first();
                        $pool->account = $pool->account + $game->bet;
                        $pool->save();
                    }
                    $game->delete();
                }
                $next->save();
            }
        }


        return (time() - $start)."sec.";
    }

    public static function parseMatchesForUSA($current, $next)
    {
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $tail = "fixtures/";

        $league = LeagueDetails::findOrFail($current->league_details_id);
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/" . $tail;

        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong fixtures url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $finder = new DomXPath($dom);
        $classname = "result-table";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        $rows = $nodes->item(0)->getElementsByTagName("tr");
        $time = "";
        $date = "";
        $home = "";
        $away = "";
        $id = "";
        $first = true;
        $cfrom = '';
        $cto = '';
        $nfrom = '';
        $nto = '';
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            if ($cols->length > 0) {
                $a = $cols->item(1)->getElementsByTagName('a');
                foreach ($a as $link) {
                    $href = $link->getAttribute("href");
                    $arr = explode("/", $href);
                    $id = $arr[count($arr) - 2];
                }
                if (strlen($cols->item(0)->nodeValue) > 3) {
                    $tmp = explode(" ", $cols->item(0)->nodeValue);
                    $time = $tmp[1] . ":00";
                    $datetmp = explode(".", $tmp[0]);
                    $date = $datetmp[2] . "-" . $datetmp[1] . "-" . $datetmp[0];
                }
                if (strlen($cols->item(1)->nodeValue) > 0) {
                    $home = explode(' - ', $cols->item(1)->nodeValue)[0];
                    $away = explode(' - ', $cols->item(1)->nodeValue)[1];
                }
                if ($first) {
                    $dw = date("w", strtotime($date));
                    $week_num = date("W", strtotime($date));
                    if ($dw >= 2 && $dw <= 5) {
                        $cfrom = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 2));
                        $cto = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 5));
                        $nfrom = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 6));
                        $nto = date('Y-m-d', strtotime(date('Y') . '-W' . ($week_num + 1) . '-' . 1));
                    } else {
                        $cfrom = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 6));
                        $cto = date('Y-m-d', strtotime(date('Y') . '-W' . ($week_num + 1) . '-' . 1));
                        $nfrom = date('Y-m-d', strtotime(date('Y') . '-W' . ($week_num + 1) . '-' . 2));
                        $nto = date('Y-m-d', strtotime(date('Y') . '-W' . ($week_num + 1) . '-' . 5));
                    }
                    $first = false;
                } else {

                }
                if ($date > $nto) {
                    if ($next->matches()->count() == 0) {
                        $dw = date("w", strtotime($date));
                        $week_num = date("W", strtotime($date));
                        if ($dw >= 2 && $dw <= 5) {
                            $nfrom = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 2));
                            $nto = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 5));
                        } else {
                            $nfrom = date('Y-m-d', strtotime(date('Y') . '-W' . $week_num . '-' . 6));
                            $nto = date('Y-m-d', strtotime(date('Y') . '-W' . ($week_num + 1) . '-' . 1));
                        }
                    }
                }
                $match = Match::firstOrNew(array('id' => $id));
                if ($date >= $cfrom && $date <= $cto) {
//                    echo "$date $cfrom $cto<br>";
                    $match->groups_id = $current->id;
                } else if ($date >= $nfrom && $date <= $nto) {
//                    echo "$date $nfrom $nto<br>";
                    $match->groups_id = $next->id;
                }

                $match->home = $home;
                $match->away = $away;
                $timestamp = strtotime($time) + 60 * 60;
                $match->matchTime = date('H:i:s', $timestamp);
                $match->matchDate = $date;
                $match->resultShort = '-';
                $match->league_details_id = $current->league_details_id;

                $match->save();

            }
        }
    }

    public static function parseMatchesFromSummary($league_details_id)
    {
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $tail = "";

        $league = LeagueDetails::findOrFail($league_details_id);
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/" . $tail;
        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong fixtures url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $table = $dom->getElementById("league-summary-next");
        $rows = $table->getElementsByTagName("tr");

        $ids = array();
        foreach ($rows as $row) {
//            $match = new Match;
            $cols = $row->getElementsByTagName('td');
            if ($cols->length > 0) {
                $a = $cols->item(1)->getElementsByTagName('a');
                foreach ($a as $link) {
                    $href = $link->getAttribute("href");
                    $arr = explode("/", $href);
                    if (count($arr) > 2){
                        $id = $arr[count($arr) - 2];
                        $match = Match::firstOrCreate(['id' => $id]);

                        $dt = $cols->item(8)->nodeValue;
                        $dtarr = explode(' ', $dt);
                        $date = $dtarr[0];
                        $time = $dtarr[1];
                        $ha = $cols->item(1)->nodeValue;
                        $tarr = explode(' - ', $ha);

                        $match->home = $tarr[0];
                        $match->away = $tarr[1];
                        $match->resultShort = '-';
                        $match->season = '2014-2015';
                        $match->league_details_id = $league_details_id;
                        $datearr = explode('.', $date);
                        $match->matchDate = $datearr[2].'-'.$datearr[1].'-'.$datearr[0];
                        $match->matchTime = $time;
//                        array_push($ids, $match);
                        $match->save();
                    }
                }


                // return $match;
            }
        }
//        return $ids;
    }

    public static function parseTeamMatches($url, $league_details_id)
    {
//        $baseUrl = "http://www.betexplorer.com/soccer/";
//
//        $league = LeagueDetails::findOrFail($league_details_id);
//        $url = $baseUrl . $league->country . "/" . $league->fullName . "/teaminfo.php?team_id=" . $team_id;
        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong fixtures url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $finder = new DomXPath($dom);
        $classname = "result-table team-matches";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        $rows = $nodes->item(0)->getElementsByTagName("tr");

        $id = '';
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
//            $i = 0;
//         foreach($cols as $col) {
//             echo $i." ".$col->nodeValue." ";
//             $i++;
//         }
//            echo "<br>";
            if ($cols->length > 8) {
                $a = $cols->item(7)->getElementsByTagName('a');
                foreach ($a as $link) {
                    $href = $link->getAttribute("href");
                    $arr = explode("/", $href);
                    $id = $arr[count($arr) - 2];
                }
                $home = $cols->item(2)->nodeValue;
                $away = $cols->item(3)->nodeValue;
                $datetmp = explode(".", $cols->item(8)->nodeValue);
                $date = $datetmp[2] . "-" . $datetmp[1] . "-" . $datetmp[0];

                if ($id != '') {
                    $m = Match::firstOrNew(['id' => $id, 'home' => $home, 'away' => $away]);
                    $m->league_details_id = $league_details_id;
                    $m->matchDate = $date;
                    $m->resultShort = '-';

//                    $m->league_details_id = $league_details_id;
                    $m->save();

                }
            }
        }

        $finder1 = new DomXPath($dom);
        $classname1 = "bg-white";
        $nodes1 = $finder1->query("//*[contains(@class, '$classname1')]");
        $team_arr = explode(": ", $nodes1->item(0)->nodeValue);
//        return $team_arr;
        return array($team_arr[1], $id);
//        if ($league_details_id == 112) {
//            Parser::parseLeagueSeriesUSA($league_details_id);
//        } else {
//            Parser::parseLeagueSeries($league_details_id);
//        }
        return $id;
    }

    private static function get_http_response_code($url)
    {
        try {
            $headers = get_headers($url);
        } catch (ErrorException $e) {
            echo $url;
            return 404;
        }
        return substr($headers[0], 9, 3);
    }

    public static function parseLivescoreForMatch($dom)
    {
        $parsed = '';
        $yc = array();
        $parts = $dom->getElementById('parts');
        if ($parts != null) {
            $rows = $parts->getElementsByTagName('tr');
            if ($rows->length > 0) {
                foreach ($rows as $row) {
                    $cols = $row->getElementsByTagName('td');
                    if ($cols->length > 0) {
                        foreach ($cols as $col) {
                            $spans = $col->getElementsByTagName('span');
                            foreach ($spans as $span) {
                                $attr = $span->getAttribute('class');
                                if ($attr == "icon y-card participant-name") {
//                                    array_push([''])
                                }
                                $parsed = $parsed . $attr . " ";
                            }
                            $parsed = $parsed . $col->nodeValue . " ";
                        }
                    }
                    $parsed = $parsed . "<br>";
                }
            }
        }
        return $parsed;
    }

    public static function parseOdds($match)
    {
        $league = LeagueDetails::find($match->league_details_id);
        $baseUrl = "http://www.oddsportal.com/soccer/";
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/" . $match->id;
//
//        if (Parser::get_http_response_code($url) != "200") {
//            return "Wrong league stats url! --> $url";
//        }
        $data = file_get_contents($url);
        $matches = array();
        preg_match('/xhash":"(?P<hash>[a-z0-9-A-Z]+)","/', $data, $matches);
        $hash = $matches['hash'];

        $parse_url = "http://fb.oddsportal.com/feed/match/1-1-" . $match->id . "-8-2-" . $hash . ".dat";
        $json_data = file_get_contents($parse_url);
        $matches2 = array();
        preg_match('/.dat\', (?P<json>.*)\)/', $json_data, $matches2);

        $odds_arr = json_decode($matches2['json'], true);
//        return $odds_arr;
//        $odds00 = 3;
//        $odds11 = 3;
//        $odds22 = 3;
        try {
//            $oddsx = $odds_arr['d']['oddsdata']['back']
            $odds00 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-1']['odds'][16][0];
            $odds11 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-3']['odds'][16][0];
            $odds22 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-7']['odds'][16][0];
            $odds01 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-4']['odds'][16][0];
            $odds02 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-9']['odds'][16][0];
            $odds10 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-2']['odds'][16][0];
            $odds20 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-5']['odds'][16][0];
            $odds12 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-8']['odds'][16][0];
            $odds21 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-6']['odds'][16][0];
        } catch (ErrorException $e) {
            $odds00 = -1;
            $odds11 = -1;
            $odds22 = -1;
            $odds10 = -1;
            $odds12 = -1;
            $odds20 = -1;
            $odds21 = -1;
            $odds01 = -1;
            $odds02 = -1;
        }

        return array(6 => $odds00, 7 => $odds11, 8 => $odds22, 9 => $odds01, 10 => $odds02, 11 => $odds10, 12 => $odds20, 13 => $odds12, 14 => $odds21);
    }

    public static function parseMatchesForLeagueAndSeason($league_details_id, $season) {
        //www.betexplorer.com/soccer/india/i-league-2003-2004/results/
        //leagueresults_tbody
        $league = LeagueDetails::find($league_details_id);
        $url = "http://www.betexplorer.com/soccer/".$league->country."/".$league->fullName."-".$season."/results/";
        if (Parser::get_http_response_code($url) != "200") {
            $url = "http://www.betexplorer.com/soccer/".$league->country."/".$league->fullName."/results/";
            if (Parser::get_http_response_code($url)) {
//                return "Wrong fixtures url! --> $url";
            }
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $table = $dom->getElementById("leagueresults_tbody");
        $rows = $table->getElementsByTagName("tr");
        foreach($rows as $row) {
            $cols = $row->getElementsByTagName("td");
            if ($cols->length > 0) {
                $attrs = $cols->item(0)->getElementsByTagName("a");
                $urlarr = explode("=", $attrs->item(0)->getAttribute('href'));
                $id = $urlarr[1];
                $ha = explode(' - ', $cols->item(0)->nodeValue);
                $match = Match::firstOrCreate(['id' => $id]);
                $match->home = $ha[0];
                $match->away = $ha[1];
                $ra = explode(':', $cols->item(1)->nodeValue);
                if (count($ra) > 1) {
                    $match->homeGoals = $ra[0];
                    $match->awayGoals = $ra[1];
                    if ($ra[0] > $ra[1]) {
                        $match->resultShort = 'H';
                    } else if ($ra[0] < $ra[1]) {
                        $match->resultShort = 'A';
                    } else {
                        $match->resultShort = 'D';
                    }
                } else {
                    echo $match->id."<br>";
//                    return $match;
//                    $match->resultShort = '';
                }
                $match->league_details_id = $league_details_id;
                $match->season = $season;
                $match->save();
//                Parser::parseTimeDate($match);
            }

        }
        return "finished";
    }

}
