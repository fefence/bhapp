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
                $game->odds = $oddsX;
                $game->income = $oddsX * $game->bet;
                $game->save();
                continue 1;
            }
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
        }
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
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $tail = "fixtures/";

//        return $current;
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
        $group = $current;
        foreach ($rows as $row) {

            $headings = $row->getElementsByTagName('th');
            if ($headings->length > 0) {
                if ($headings->item(0)->nodeValue == ($current->round + 1) . '. Round') {
                    $group = $next;
                }
                if ($headings->item(0)->nodeValue == ($next->round + 1) . '. Round') {
                    break 1;
                }
            }
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
                //$attrs = $col->getAttribute("data-odd");
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

                // return $match;
            }
        }
        $curr = $current->matches()->orderBy('matchDate')->get();
//        return $current;
        $firstOfNext = $next->matches()->orderBy('matchDate')->first();
        foreach ($curr as $m) {
            if ($m->matchDate > $firstOfNext->matchDate) {
                $m->groups_id = 0;
                $m->save();
            }
        }
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

    public static function parseMatchesFromSummary($current)
    {
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $tail = "";

        $league = LeagueDetails::findOrFail($current->league_details_id);
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
            $cols = $row->getElementsByTagName('td');
            if ($cols->length > 0) {
                $a = $cols->item(1)->getElementsByTagName('a');
                foreach ($a as $link) {
                    $href = $link->getAttribute("href");
                    $arr = explode("/", $href);
                    $id = $arr[count($arr) - 2];
                    array_push($ids, $id);
                }
                // return $match;
            }
        }
        return $ids;
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
                    Parser::parseTimeDate($m);
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
        $headers = get_headers($url);
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
                                $parsed = $parsed.$attr . " ";
                            }
                            $parsed = $parsed.$col->nodeValue . " ";
                        }
                    }
                    $parsed = $parsed."<br>";
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
        try {
            $odds00 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-1']['odds'][16][0];
            $odds11 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-3']['odds'][16][0];
            $odds22 = $odds_arr['d']['oddsdata']["back"]['E-8-2-0-0-7']['odds'][16][0];
        } catch (ErrorException $e) {
            $odds00 = 3;
            $odds11 = 3;
            $odds22 = 3;
        }
        return array(6 => $odds00, 7 => $odds11, 8 => $odds22);
    }

}
