<html>
<head>
<title>NBA Player Stats</title>
</head>
<body>
	<?php
		echo "<h1><img src='http://www.logodesignlove.com/wp-content/uploads/2011/04/nba-logo-on-wood-740x410.jpg' 
			width='30px' height='30px'>NBA Player Statistics</h1>";
		echo "<form name='searchForm' method='post' action='index.php'>";
		echo "<input name='searchName' type='text' size='60' maxlength='90' placeholder='Please Enter A Player' required>";
		echo "<input type='submit' name='sumbitName' value='Find'><br>";

		function findPlayerNames() {
			$playerList = "";
			if(!empty($_POST['searchName'])) {	
				try {
					$conn = new PDO('mysql:host=mysql.cox01xutovwc.us-west-2.rds.amazonaws.com;dbname=NBA', 'info344user', '<password>');
					$userInput = $_POST['searchName'];
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$stmt = $conn->prepare("SELECT PlayerName, GamesPlayed, FieldGoalPercentage, ThreePointPercentage, FreeThrowPercentage, PointsPerGame
						FROM TABLE_1 WHERE PlayerName Like '%$userInput%'");
					$stmt->execute();
					$playerList = $stmt->fetchAll();
					if (empty($playerList)) {
						$levenshtein = $conn->prepare("SELECT PlayerName FROM TABLE_1");
						$levenshtein->execute();
						$words = $levenshtein->fetchAll(PDO::FETCH_COLUMN);
						$shortestDistance = -1;
						foreach($words as $word) {
							$levenshteinDistance = levenshtein($userInput, $word);
							if ($levenshteinDistance <= $shortestDistance|| $shortestDistance < 0) {
								$closestPlayer = $word;
								$shortestDistance = $levenshteinDistance;
							}
						}
						if (!$shortestDistance == 0) {
							if ($shortestDistance < 6) {
								echo "You Typed <b>$userInput</b>, Did You Mean <b>$closestPlayer</b>?<br>";
								$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
								$stmt = $conn->prepare("SELECT PlayerName, GamesPlayed, FieldGoalPercentage, ThreePointPercentage, FreeThrowPercentage, PointsPerGame 
									FROM TABLE_1 WHERE PlayerName Like '%$closestPlayer%'");
								$stmt->execute();
								$playerList = $stmt->fetchAll();
							}  else {   //$shortestDistance > 6
								echo "No Results For <b>$userInput</b> Were Found, Please Try Again";
							}
						}
					}
				} catch(PDOException $e) {
					echo 'ERROR: ' . $e->getMessage();
				}
			}		
			return $playerList; 
		}

		function displayPlayerName($playerList) {
			if (!empty($playerList)) {
				if (count($playerList) == 1) {
					echo '1 Player Found';
				} else {    //(empty($playerList))
					echo count($playerList) .' Players Found';
				}
				echo '<table border="0" cellspacing="25px">';
				echo '<tr>';
				echo '<td></td>';
				echo '<td>Player Name</td>';
				echo '<td>Games Played</td>';
				echo '<td>Field Goal Percentage(%)</td>';
				echo '<td>Three Point Percentage(%)</td>';
				echo '<td>Free Throw Percentage(%)</td>';
				echo '<td>Points Per Game</td>';
				echo '</tr>';
				foreach($playerList as $row) {
					echo '<tr>';
					$photoID = explode(' ', $row['PlayerName']);
					$photoName = "";
					foreach($photoID as $name) {
						if (empty($photoName)) {
							$photoName = $name;
						} else {   //(!empty($photoName))
							$photoName = $photoName.'_'.$name;
						}
					}			
					$link = get_headers("http://i.cdn.turner.com/nba/nba/.element/img/2.0/sect/statscube/players/large/".$photoName.".png");
					if ($link[0] != 'HTTP/1.0 200 OK') {
						echo "<td><img src='http://pix.iemoji.com/sbemojix2/0497.png' width='230px' height='180px'></td>";
					} else { //$link[0] == 'HTTP/1.0 200 OK'
						echo "<td><img src='http://i.cdn.turner.com/nba/nba/.element/img/2.0/sect/statscube/players/large/".$photoName.".png'></td>";;
					}	
					echo '<td>'.$row['PlayerName'].'</td>';
					echo '<td>'.$row['GamesPlayed'].'</td>';
					echo '<td>'.$row['FieldGoalPercentage'].'</td>';
					echo '<td>'.$row['ThreePointPercentage'].'</td>';
					echo '<td>'.$row['FreeThrowPercentage'].'</td>';
					echo '<td>'.$row['PointsPerGame'].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
		} 

		$foundPlayerList = findPlayerNames();
		if  (!empty($foundPlayerList)) {
			displayPlayerName($foundPlayerList);
		}
	?>
</body>
</html>