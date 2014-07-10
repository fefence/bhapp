<?php

use Illuminate\Database\Eloquent;

class Parser
{

    public static function parseMatchOddsForGames($games)
    {
        // return "boo";
        foreach ($games as $game) {
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
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $league = LeagueDetails::find($league_details_id);
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/";

        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong league stats url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $finder = new DomXPath($dom);
        $classname = "stats-table result-table";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        $rows = $nodes->item(4)->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            $place = trim($cols->item(0)->nodeValue);
            $team = trim($cols->item(1)->nodeValue);
            $streak = trim($cols->item(6)->nodeValue);
            $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
            $stand->streak = $streak;
            $stand->place = explode(".", $place)[0];
            $stand->save();
        }
        $gr_id = LeagueDetails::find($league_details_id)->id;
        $str = Standings::where('league_details_id', '=', $league_details_id)
            ->select(DB::raw('streak, count(*) as c'))
            ->groupBy('streak')
            ->get();
        foreach($str as $s) {
            $g = new GroupToStreaks();
            $g->groups_id = $gr_id;
            $g->streak_length = $s->streak;
            $g->streak_count = $s->c;
            $g->save();
        }
    }

    public static function parseLeagueSeriesUSA($league_details_id)
    {
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $league = LeagueDetails::find($league_details_id);
        $url = $baseUrl . $league->country . "/" . $league->fullName . "/";

        if (Parser::get_http_response_code($url) != "200") {
            return "Wrong league stats url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $finder = new DomXPath($dom);
        $classname = "stats-table result-table";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        for ($a = 8; $a <= 9; $a++) {
            $rows = $nodes->item($a)->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
            foreach ($rows as $row) {
                $cols = $row->getElementsByTagName('td');
                $place = trim($cols->item(0)->nodeValue);
                $team = trim($cols->item(1)->nodeValue);
                $streak = trim($cols->item(6)->nodeValue);
                $stand = Standings::firstOrNew(['league_details_id' => $league_details_id, 'team' => $team]);
                $stand->streak = $streak;
                $stand->place = explode(".", $place)[0];
                $stand->save();
            }
        }
        $gr_id = LeagueDetails::find($league_details_id)->id;
        $str = Standings::where('league_details_id', '=', $league_details_id)
            ->select(DB::raw('streak, count(*) as c'))
            ->groupBy('streak')
            ->get();
        foreach($str as $s) {
            $g = new GroupToStreaks();
            $g->groups_id = $gr_id;
            $g->streak_length = $s->streak;
            $g->streak_count = $s->c;
            $g->save();
        }
    }

    public static function parseMatchesForGroup($current, $next)
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
                $match->matchTime = $time;
                $match->matchDate = $date;
                $match->groups_id = $group->id;
                $match->save();

                // return $match;
            }
        }
        $curr = $current->matches()->orderBy('matchDate')->get();
        $firstOfNext = $next->matches()->orderBy('matchDate')->first();
        foreach ($curr as $m) {
            if ($m->matchDate > $firstOfNext->matchDate) {
                $m->groups_id = 0;
                $m->save();
            }
        }
//        $datetime = $group->matches()->orderBy('matchDate', 'desc')->orderBy('matchTime', 'desc')->take(1)->get(['matchDate', 'matchTime'])[0];
//        // return $datetime;
//        $group->update_time = date('Y-M-d H:i:s', strtotime("$datetime->matchDate.' '.$datetime->matchTime + 2 hours"));
//        // return $group->update_time;
//        $group->save();
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
                $match->matchTime = $time;
                $match->matchDate = $date;

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

    private static function get_http_response_code($url)
    {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }


}
