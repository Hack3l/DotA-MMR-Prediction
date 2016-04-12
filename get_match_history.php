<?php
	if(isset($_POST["account_id"]) && isset($_POST["apikey"])){
		$account_id = $_POST["account_id"];
		$apikey = $_POST["apikey"];
		
		$url = "http://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/v1/?account_id=".$account_id."&key=".$apikey."&matches_requested=100";
		$html = file_get_contents($url);
		$result = json_decode($html)->result;
		$allxpm = 0;
		$allgpm = 0;
		$wins = 0;
		$allkda = 0;
		$allherodmg = 0;
		$alltowerdmg = 0;
		$allhealing = 0;
		$alllh = 0;
		$alldenies = 0;
		$allkillpart = 0;
		if($result->status == 1){
			$matches = (array)$result->matches;
			print '{"result":1,"matches":[';
			print array_pop($matches)->match_id;
			foreach((array)$matches as $match){
				print ",".$match->match_id;
			}	
			print ']}';
		}else{
			print '{"result":0,"errormsg":'.$result->status.'}';
		}
		
	}else{
		print '{"result":0,"errormsg":"Please set account_id and apikey!"}';
	}


?>