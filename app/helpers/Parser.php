<?php

class Parser {

	

    public static function parseLeagueSeries($group){
        $baseUrl = "http://www.betexplorer.com/soccer/";
        $league = LeagueDetails::findOrFail($group->league_details_id);
        $url = $baseUrl.$league->country."/".$league->fullName."/";

        if(Parser::get_http_response_code($url) != "200"){
            return "Wrong league stats url! --> $url";
        }
        $data = file_get_contents($url);

        $dom = new domDocument;

        @$dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;

        $finder = new DomXPath($dom);
        $classname="stats-table result-table";
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        $rows = $nodes->item(4)->getElementsByTagName('tbody')->item(0)->getElementsByTagName("tr");
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            $place = $cols->item(0)->nodeValue;
            $team = $cols->item(1)->nodeValue;
            $streak = $cols->item(6)->nodeValue;
            $stand = Standings::firstOrNew(['league_details_id' => $group->league_details_id, 'team' => $team]);
            $stand->streak = $streak;
            $stand->place = explode(".", $place)[0];
            $stand->save();
        }

    }

    public static function parseMatchesForGroup($current, $next) {
		$baseUrl = "http://www.betexplorer.com/soccer/";
		$tail = "fixtures/";

		$league = LeagueDetails::findOrFail($current->league_details_id);
		$url = $baseUrl.$league->country."/".$league->fullName."/".$tail;

		if(Parser::get_http_response_code($url) != "200"){
			return "Wrong fixtures url! --> $url";
		}
		$data = file_get_contents($url);

		$dom = new domDocument;

		@$dom->loadHTML($data);
		$dom->preserveWhiteSpace = false;

		$finder = new DomXPath($dom);
        $classname="result-table";
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
                if ($headings->item(0)->nodeValue == ($current->round + 1).'. Round') {
                	$group = $next;
                }
                if ($headings->item(0)->nodeValue == ($next->round + 1).'. Round') {
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
                	$time = $tmp[1].":00";
                	$datetmp = explode(".", $tmp[0]);
                	$date = $datetmp[2]."-".$datetmp[1]."-".$datetmp[0];
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
        $datetime = $group->matches()->orderBy('matchDate', 'desc')->orderBy('matchTime', 'desc')->take(1)->get(['matchDate', 'matchTime'])[0];
        // return $datetime;
        $group->update_time = date('Y-M-d H:i:s', strtotime("$datetime->matchDate.' '.$datetime->matchTime + 2 hours"));
        // return $group->update_time;
        $group->save();
    }

	private static function get_http_response_code($url) {
	    $headers = get_headers($url);
	    return substr($headers[0], 9, 3);
	}

}

?>