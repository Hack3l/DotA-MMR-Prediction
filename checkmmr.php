<?php
	

	if(isset($_POST["account_id"])&&isset($_POST["xpm"])&&isset($_POST["gpm"])&&isset($_POST["herodmg"])&& isset($_POST["towerdmg"])&&isset($_POST["healing"])&&isset($_POST["last_hits"])&&isset($_POST["denies"])&&isset($_POST["killpart"])&&isset($_POST["winpercent"])&&isset($_POST["kda"])&&isset($_POST["k"])&&isset($_POST["heroes"])){
		
		$conn = new mysqli("host","user","password","database"); //change to connect to your mysql database
		if ($conn->connect_errno) {
			echo "Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
		}

		$stmt = $conn->prepare("Select account_id,solo_mmr,party_mmr,xpm,gpm,herodmg,towerdmg,healing,last_hits,denies,kill_participation,win_percentage,kda,heroes from bettermmrstats where solo_mmr != 0 Limit 25000");
		
		if($stmt){
			$stmt->bind_result($account_id,$solo_mmr,$party_mmr,$xpm,$gpm,$herodmg,$towerdmg,$healing,$last_hits,$denies,$kill_participation,$win_percentage,$kda,$heroes);
			$xpmmin = 9999999999;
			$xpmmax = 0;
			$gpmmin = 9999999999;
			$gpmmax = 0;
			$heromin = 9999999999;
			$heromax = 0;
			$towermin = 9999999999;
			$towermax = 0;
			$healingmin = 9999999999;
			$healingmax = 0;
			$lhmin = 9999999999;
			$lhmax = 0;
			$deniesmin = 9999999999;
			$deniesmax = 0;
			$killpartmin = 9999999999;
			$killpartmax = 0;
			$winpercmin = 9999999999;
			$winpercmax = 0;
			$kdamin = 9999999999;
			$kdamax = 0;

			$soloarr = array();
			$partyarr = array();
			$xpmarr = array();
			$gpmarr = array();
			$heroarr = array();
			$towerarr = array();
			$healingarr = array();
			$lharr = array();
			$deniearr = array();
			$killpartarr = array();
			$winpercarr = array();
			$kdaarr = array();

			$xpmdist = array();
			$gpmdist = array();
			$herodist = array();
			$towerdist = array();
			$healingdist = array();
			$lhdist = array();
			$deniesdist = array();
			$killpartdist = array();
			$winpercdist = array();
			$kdadist = array();
			$heroesdist = array();
			$stmt->execute();
			while($stmt->fetch()){
				if($xpm < $xpmmin){
					$xpmmin = $xpm;
				}
				if($xpm > $xpmmax){
					$xpmmax = $xpm;
				}	
				if($gpm < $gpmmin){
					$gpmmin = $gpm;
				}
				if($gpm > $gpmmax){
					$gpmmax = $gpm;
				}
				if($herodmg < $heromin){
					$heromin = $herodmg;
				}
				if($herodmg > $heromax){
					$heromax = $herodmg;
				}
				if($towerdmg < $towermin){
					$towermin = $towerdmg;
				}
				if($towerdmg > $towermax){
					$towermax = $towerdmg;
				}
				if($healing < $healingmin){
					$healingmin = $healing;
				}
				if($healing > $healingmax){
					$healingmax = $healing;
				}
				if($last_hits < $lhmin){
					$lhmin = $last_hits;
				}
				if($last_hits > $lhmax){
					$lhmax = $last_hits;
				}
				if($denies < $deniesmin){
					$deniesmin = $denies;
				}
				if($denies > $deniesmax){
					$deniesmax = $denies;
				}
				if($kill_participation < $killpartmin){
					$killpartmin = $kill_participation;
				}
				if($kill_participation > $killpartmax){
					$killpartmax = $kill_participation;
				}
				if($win_percentage < $winpercmin){
					$winpercmin = $win_percentage;
				}
				if($win_percentage > $winpercmax){
					$winpercmax = $win_percentage;
				}
				if($kda < $kdamin){
					$kdamin = $kda;
				}
				if($kda > $kdamax){
					$kdamax = $kda;
				}
				

				$soloarr[$account_id] = $solo_mmr;
				$partarr[$account_id] = $party_mmr;
				$xpmarr[$account_id] = $xpm;
				$gpmarr[$account_id] = $gpm;
				$heroarr[$account_id] = $herodmg;
				$towerarr[$account_id] = $towerdmg;
				$healingarr[$account_id] = $healing;
				$lharr[$account_id] = $last_hits;
				$deniearr[$account_id] = $denies;
				$killpartarr[$account_id] = $kill_participation;
				$winpercarr[$account_id] = $win_percentage;
				$kdaarr[$account_id] = $kda;
				$heroesarr[$account_id] = unserialize($heroes);


				

			}

			$getheroes = json_decode($_POST["heroes"]);
			$userheroes = array();
			foreach($getheroes as $hero){
				if(isset($userheroes[$hero])){
					$userheroes[$hero]++;
				}else{
					$userheroes[$hero] = 1;
				}
			}
			$userheroes = array_filter($userheroes, function ($k){ return $k > 3; }); 
			foreach($xpmarr as $i=>$value){
				$xpmdist[$i] = abs($xpmarr[$i] - $_POST["xpm"])/($xpmmax-$xpmmin);
				$gpmdist[$i] = abs($gpmarr[$i] - $_POST["gpm"])/($gpmmax-$gpmmin);
				$herodist[$i] = abs($heroarr[$i] - $_POST["herodmg"])/($heromax-$heromin);
				$towerdist[$i] = abs($towerarr[$i] - $_POST["towerdmg"])/($towermax-$towermin);
				$healingdist[$i] = abs($healingarr[$i] - $_POST["healing"])/($healingmax-$healingmin);
				$lhdist[$i] = abs($lharr[$i] - $_POST["last_hits"])/($lhmax-$lhmin);
				$deniesdist[$i] = abs($deniearr[$i] - $_POST["denies"])/($deniesmax-$deniesmin);
				$killpartdist[$i] = abs($killpartarr[$i] - $_POST["killpart"])/($killpartmax-$killpartmin);
				$winpercdist[$i] = abs($winpercarr[$i] - $_POST["winpercent"])/($winpercmax-$winpercmin);
				$kdadist[$i] = abs($kdaarr[$i] - $_POST["kda"])/($kdamax-$kdamin);
				$heroesdist[$i] = count(array_diff_key($heroesarr[$i],$userheroes));
				$heroesdist[$i] += count(array_diff_key($userheroes,$heroesarr[$i]));
				$samecount = 0;					
				foreach($heroesarr[$i] as $hero => $played){

					if(isset($userheroes[$hero])){
						$samecount++;
						$hdist = $userheroes[$hero] - $played;
						if($hdist <= 0){
							$heroesdist[$i] += $userheroes[$hero]/$played;
						}else{
							$heroesdist[$i] += $played/$userheroes[$hero];
						}
					}
				}
				$heroesdist[$i] = $heroesdist[$i] / (count($heroesarr[$i])+count($userheroes)-$samecount);
				$first = false;
			}

			
			$distance = array();


			foreach($xpmdist as $i => $value){
				$distance[$i] = ($xpmdist[$i]+$gpmdist[$i]+$herodist[$i]+$towerdist[$i]+($lhdist[$i]*2)+($deniesdist[$i]*2)+($killpartdist[$i]*2)+$kdadist[$i]+($heroesdist[$i]*3))/14; // removed winpercentage and healing adjusted weights
			}
			asort($distance);

			$i = 0;
			$predictmmr = 0;
			$lowestmmr = 9999999;
			$highestmmr = 0;
			$vararr = array();
			foreach($distance as $key => $value){
				if($i == $_POST["k"]){
					break;
				}
				if($soloarr[$key] == 0){
					continue;
				}
				if($soloarr[$key] > $highestmmr){
					$highestmmr = $soloarr[$key];
				}
				if($soloarr[$key] < $lowestmmr){
					$lowestmmr = $soloarr[$key];
				}
				$vararr[] = $soloarr[$key];
				$predictmmr += $soloarr[$key];
				$i++;

			}

			$predictmmr = $predictmmr/$_POST["k"];

			$uppervar = 0;

			foreach($vararr as $var){
				$dist = $var - $predictmmr;
				$uppervar += ($dist * $dist);
			}

			$variance = sqrt($uppervar/$_POST["k"]);
			print '{"result":1,"predictmmr":'.round($predictmmr,2).",";
			print '"variance":'.$variance."}";
			$conn->close();	
		}else{
			print '{"result":0}';
		}

		
	}
?>