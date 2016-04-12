<?php
	
	if(isset($_POST["match_id"]) && isset($_POST["account_id"]) && isset($_POST["apikey"])){
		$account_id = $_POST["account_id"];
		$match_id = $_POST["match_id"];
		$apikey = $_POST["apikey"];
		while(!isset($matchresult)){
			$matchurl = "http://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/v1/?key=".$apikey."&match_id=".$match_id;

			$matchjson = json_decode(file_get_contents($matchurl));
			$matchresult = $matchjson->result;
		}

		$players = $matchresult->players;
		$user = array();
		$matchkillsradiant = 0;
		$matchkillsdire = 0;
		$win = 0;
		foreach($players as $player){
			if($player->account_id == $account_id){
				$user = $player;
			}
			if($player->player_slot <5){
				$matchkillsradiant += $player->kills;
			}elseif($player->player_slot > 100){
				$matchkillsdire += $player->kills;
			}

		}
		$onteam = "";
		if($user->player_slot < 5){
			$onteam = "radiant";
		}else if($user->player_slot > 100){
			$onteam = "dire";
		}

		if(($onteam == "radiant" && $matchresult->radiant_win)||($onteam == "dire" && !$matchresult->radiant_win)){
			$win = 1;
		}
		$xpm = $user->xp_per_min;
		$gpm = $user->gold_per_min;
		$kills = $user->kills;
		$assists = $user->assists;
		$deaths = $user->deaths;

		$herodmg = $user->hero_damage;
		$towerdmg = $user->tower_damage;
		$healing = $user->hero_healing;
		$lh = $user->last_hits;
		$denies = $user->denies;
		$killpart = 0;
		$hero_id = $user->hero_id;
		if($onteam == "radiant"){
			if($matchkillsradiant > 0){
				$killpart = ($user->kills + $user->assists)/$matchkillsradiant;
			}
		}else if ($onteam == "dire"){
			if($matchkillsdire > 0){	
				$killpart = ($user->kills + $user->assists)/$matchkillsdire;
			}
		}

		$arrayresponse = array(
			'xpm' => $xpm,
			'gpm' => $gpm,
			'herodmg' => $herodmg,
			'towerdmg' => $towerdmg,
			'healing' => $healing,
			'last_hits' => $lh,
			'denies' => $denies,
			'killpart' => $killpart,
			'onteam' => $onteam,
			'kills' => $kills,
			'assists' => $assists,
			'deaths' => $deaths,
			'win' => $win,
			'hero_id' => $hero_id
		);
		print json_encode($arrayresponse);
	}

?>