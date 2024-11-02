<?php
// Common Function to Initialize MySQLi Connection
function dbConnect(){
	$db = new mysqli('localhost', 'admin', 'fantasyFencing', 'fencing');
	if ($db->connect_error){
		die("Connection failed: " . $db->connect_error);
	}
	return $db;
}

// Adds/Updates Athlete in local db
function updateAthlete($data) {
    	$db = dbConnect();
    	$stmt = $db->prepare("
        	INSERT INTO athletes (id, name, firstName, lastName, gender, nationality, weapon, weapon2)
        	VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        	ON DUPLICATE KEY UPDATE
            		name = VALUES(name),
            		firstName = VALUES(firstName),
            		lastName = VALUES(lastName),
            		gender = VALUES(gender),
            		nationality = VALUES(nationality),
            		weapon = VALUES(weapon),
            		weapon2 = VALUES(weapon2)
    	");
    	$stmt->bind_param(
        	"isssssss",
		$data['id'],
		$data['name'],
		$data['firstName'],
		$data['lastName'],
		$data['gender'],
		$data['nationality'],
		$data['weapon'],
		$data['weapon2']
    	);
    	$athleteID = $data['id'];
    	if ($stmt->execute()) {
        	echo "Data stored successfully for Athlete ID: [$athleteID]\n";
    	} else {
        	echo "Error storing athlete data: " . $stmt->error;
    	}
    	$stmt->close();
    	$db->close();
}

// Adds/Updates Competition in local db
function updateCompetition($data) {
    	$db = dbConnect();
    	$stmt = $db->prepare("
        	INSERT INTO competitions (competitionId, season, name, category, weapon, gender, country, location, startDate, endDate)
        	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        	ON DUPLICATE KEY UPDATE
            		name = VALUES(name),
            		category = VALUES(category),
            		weapon = VALUES(weapon),
            		gender = VALUES(gender),
            		country = VALUES(country),
            		location = VALUES(location),
            		startDate = VALUES(startDate),
           		endDate = VALUES(endDate)
    	");
    
    	$stmt->bind_param(
        	"iissssssss",
        	$data['competitionId'],
		$data['season'],
		$data['name'],
		$data['category'],
		$data['weapon'],
		$data['gender'],
		$data['country'],
		$data['location'],
		$data['startDate'],
		$data['endDate']
   	);
	
	$season = $data['season'];
	$competitionID = $data['competitionId'];
    	if ($stmt->execute()) {
        	echo "Data stored successfully for Competition: [$competitionID] of Season: [$season]\n";
    	} else {
        	echo "Error storing competition data: " . $stmt->error;
    	}
    	$stmt->close();
    	$db->close();
}
?>
