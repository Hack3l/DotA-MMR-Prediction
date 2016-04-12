<?php
	include "include/openid.php";

	$api = "steam api key";
	$openid = new LightOpenID("host"); //host := your domain

	session_start();
	
	if(!$openid->mode){
		
		if(isset($_GET["login"])){
		
			$openid->identity = "http://steamcommunity.com/openid";
			header("Location: {$openid->authUrl()}"); 
			
		}
		
		if(!isset($_SESSION["T2SteamAuth"])){
		
			$login = '<div id="login"><a href="?login"><img src="http://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_large_border.png"></a></div>';
		}
		
	}elseif($openid->mode == "cancel"){
		echo "Login canceled";
	}else{
		if(!isset($_SESSION["T2SteamAuth"])){
			$_SESSION["T2SteamAuth"] = $openid->validate() ? $openid->identity : null;
			$_SESSION["T2Steam64ID"] = str_replace("http://steamcommunity.com/openid/id/","",$_SESSION["T2SteamAuth"]);
			header("Location: predictmmr.php");
		}
	}

	if(isset($_SESSION["T2SteamAuth"])){
		$login = '<div id="login"><a href="?logout"><button>Logout</button></a></div>';
		$steam32id = (int)$_SESSION["T2Steam64ID"] - 76561197960265728;

		date_default_timezone_set("Europe/Berlin");
	}

	if(isset($_GET["logout"])){
		unset($_SESSION["T2SteamAuth"]);
		unset($_SESSION["T2Steam64ID"]);
		header("Location: predictmmr.php");
	}
	
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>DotA 2 MMR Predict</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="css/mmrstyle.css">
</head>
<body>
	<h1>MMR Prediction</h1>
    <div id="progressbar"><div class="progress-label"><?php if(isset($_SESSION["T2SteamAuth"])){print "Click the button to start the prediction";}else{print "Insert your Id and start the Prediction";}?></div></div>
    <div id="warning"></div>
    <div class="input">
		<?php
		if(isset($steam32id)){
			print '<input id="userid" name="userid" type="hidden" value="'.$steam32id.'"/><input id="apikey" name="apikey" type="hidden" value="'.$api.'" /><button onclick="predictmmr();">Predict MMR</button>';
		}else{
			print '<input id="userid" name="userid" type="number" placeholder="Steam32 ID"/><br><button onclick="predictmmr();">Predict MMR</button>';
		} 
		?>

		<div id="steamlogin">
			<div>OR</div>
			<?php
				print $login;
			?>

		</div>
    </div>
    <div id="predictedmmr"></div>
	<div id="information">
		<h2>Information on how to use this site</h2>
		<ol>
			<li>Manual Input<ul>
				<li>Input your Steam32ID then start the prediction process</li>
				<li>Get your Steam32ID <a href="https://steamid.io/lookup">here</a> or use your Profile id from Dotabuff or YASP</li>
                <li>The Steam32ID is also available ingame on every players profile top right as friend id</li>
			</ul></li>
			<li>Automatic Input<ul>
				<li>Login to Steam and automatically get your data</li>
			</ul></li>
			<li>General Information<ul>
				<li>The Prediction is not that great at very low and very high mmr due too limited data.</li>
				<li>This is not meant to guess your current MMR its meant to display your "real" skill.</li>
				<li>The main purpose of this system is to help people that are not calibrated, have not played rank for a long time or think they should have higher(or lower) MMR than they have.</li>
				<li>Make sure you have your DotA 2 matchhistory public(if dotabuff or yasp work its public), otherwise this will not work</li>
			</ul></li>
		</ol>
		<div id="paypal">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="V9TRH9LYRH75G">
				<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>

	</div>

    <script>
        var progressbar = $("#progressbar");
        var progressLabel = $(".progress-label");
        var resulttext = $("#predictedmmr");
        var userid = $("#userid");
        var apikey = null;
        var account_id = null;
        var matchcnt = 0;
        var finished = 0;
        var allxpm = 0;
        var allgpm = 0;
        var allherodmg = 0;
        var alltowerdmg = 0;
        var allhealing = 0;
        var alllh = 0;
        var alldenies = 0;
        var allkillpart = 0;
        var onteam = [];
        var allkills = 0;
        var allassists = 0;
        var alldeaths = 0;
        var wins = 0;
        var heroes = [];
        var running = false;

        progressbar.progressbar({
            change: function () {
                progressLabel.text(progressbar.progressbar("value") + "%");
            },
        });

        userid.keyup(function (event) {
            if (event.keyCode == 13) {
                predictmmr();
            }
        });

        function predictmmr() {
            if (!running) {
                running = true;
                matchcnt = 0;
                finished = 0;
                allxpm = 0;
                allgpm = 0;
                allherodmg = 0;
                alltowerdmg = 0;
                allhealing = 0;
                alllh = 0;
                alldenies = 0;
                allkillpart = 0;
                onteam = [];
                allkills = 0;
                allassists = 0;
                alldeaths = 0;
                wins = 0;
                heroes = [];
                finished = 0;
				apikey = "<?php print $api; ?>";
                progressbar.progressbar("option", "value", finished);
                account_id = $("#userid").val();
                
				if(apikey != "" && account_id != ""){
					progressLabel.text("Fetching Match History...");
                    resulttext.text("");
                    $("#warning").text("");
					getMatchHistory(account_id);
				}else{
					console.log(apikey+"  "+account_id);
					$(".input").prepend("<p>Insert your Id and Apikey first!</p>");
				}
            }
        }

        function makeCall(currentmatch_id) {
            return $.ajax({ // It's necessary to return ajax call 
                url: 'loadgame.php',
                type: 'POST',
                // Events handlers
                success: completeHandler,
                data: { match_id: currentmatch_id, account_id: account_id,apikey:apikey },
                dataType: 'json'
            });
        }

        function getMatchHistory(user_id) {
            $.ajax({  
                url: "get_match_history.php",
                method: 'POST',
                data: {account_id:user_id,apikey:apikey},
                success: startProcessingMatches,
                dataType: 'json'
            });
        }

        function startProcessingMatches(msg) {
            if (msg.result == 1) {
                var result = msg.matches;
                matchcnt = result.length;
                if(matchcnt < 100){
                    $("#warning").text("You have played less than 100 matches, because of this the prediction will be not as accurate.");
                }
                progressbar.progressbar("option", "max", matchcnt);
                result.forEach(function (elem, index, array) {
                    makeCall(elem);
                });
            }else if(msg.result == 0){
                progressLabel.text("ERROR on loading Match History!");
                running = false;
            }
        }

        function completeHandler(msg) {
            var result = msg;
            allxpm += result.xpm;
            allgpm += result.gpm;
            allherodmg += result.herodmg;
            alltowerdmg += result.towerdmg;
            allhealing += result.healing;
            alllh += result.last_hits;
            alldenies += result.denies;
            allkillpart += result.killpart;
            allkills += result.kills;
            allassists += result.assists;
            alldeaths += result.deaths;
            onteam.push(result.onteam);
            heroes.push(result.hero_id);
            wins += result.win;
            finished++;
            $("#progressbar").progressbar("option", "value", finished);
            if (finished == matchcnt) {
                allloaded();
            }
        }

        function allloaded() {
            progressLabel.text("Fetching MMR...");
            var avgxpm = allxpm/matchcnt;
            var avggpm = allgpm/matchcnt;
            var avgherodmg = allherodmg/matchcnt;
            var avghealing = allhealing/matchcnt;
            var avgtowerdmg = alltowerdmg/matchcnt;
            var avglh = alllh/matchcnt;
            var avgdenies = alldenies/matchcnt;
            var avgkillpart = allkillpart/matchcnt;
            var winpercent = wins/matchcnt;
            var avgkills = allkills/matchcnt;
            var avgassists = allassists/matchcnt;
            var avgdeaths = alldeaths/matchcnt;
            if(avgdeaths > 0){
                var avgkda = (avgkills + avgassists)/avgdeaths;
            }else{
                var avgkda = avgkills + avgassists;
            }
            console.log("XPM: "+avgxpm);
            console.log("GPM: "+avggpm);
            console.log("Herodamage: "+avgherodmg);
            console.log("Healing: "+avghealing);
            console.log("Towerdmg: "+avgtowerdmg);
            console.log("Lasthits: "+avglh);
            console.log("Denies: "+avgdenies);
            console.log("Killparticipation: "+avgkillpart);
            console.log("Winpercentage: "+winpercent);
            console.log("Kills: "+avgkills);
            console.log("Assists: "+avgassists);
            console.log("Deaths: "+avgdeaths);
            console.log("KDA: "+avgkda);
            console.log("Heroes: "+heroes);
            var k = 9;
            $.ajax({  
                url: 'checkmmr.php',
                type: 'POST',
                success: printresult,
                data: {"account_id":account_id,"xpm":avgxpm,"gpm":avggpm,"herodmg":avgherodmg,"towerdmg":avgtowerdmg,"healing":avghealing,"last_hits":avglh,"denies":avgdenies,"killpart":avgkillpart,"winpercent":winpercent,"kda":avgkda,"k":k, "heroes":JSON.stringify(heroes)},
                dataType: 'json'
            });
        }

        function printresult(msg) {
			if(msg.result == 1){
				var predictedmmr = msg.predictmmr;
				var variance = msg.variance;
				progressLabel.text("MMR fetched!");
				resulttext.text("Predicted MMR: " + predictedmmr);
				console.log("Variance: " + variance);
			}else{
				progressLabel.text("Error on fetching MMR try again later.");	
			}
			running = false;
        }
    </script>
</body>
</html>
