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

function updateCompetitionResults($results) {
    	$db = dbConnect();

    	// Prepare the insertion statement
    	$stmt = $db->prepare("
        	INSERT INTO competitionResults (competitionId, season, fencerId, finished, points)
        	VALUES (?, ?, ?, ?, ?)
        	ON DUPLICATE KEY UPDATE
            		finished = VALUES(finished),
            		points = VALUES(points)
    	");

    	if (!$stmt) {
        	echo "Preparation failed: (" . $db->errno . ") " . $db->error;
        	return;
    	}

    	foreach ($results as $result) {
        	try {
            		// Check if the athlete exists before inserting the result
            		if (!athleteExists($result['fencerId'])) {
                		echo "Skipping result for fencer ID " . $result['fencerId'] . " as they do not exist in the database.\n";
                		continue;
            		}

            		// Bind parameters and execute the insertion
            		$stmt->bind_param(
                		"iiidd",
               		 	$result['competitionId'],
                		$result['season'],
                		$result['fencerId'],
                		$result['finished'],
                		$result['points']
            		);

            		if (!$stmt->execute()) {
                		throw new mysqli_sql_exception("Error inserting/updating result for fencer ID " . $result['fencerId'] . ": " . $stmt->error);
            		} else {
                		echo "Inserted/updated result for fencer ID " . $result['fencerId'] . "\n";
            		}
        	} catch (mysqli_sql_exception $e) {
            		// Catch and log the error without stopping the script
            		echo "Exception caught: " . $e->getMessage() . "\n";
        	}
    	}
    		$stmt->close();
    		$db->close();
}

function athleteExists($fencerId) {
    	$db = dbConnect();
    
    	$stmt = $db->prepare("SELECT 1 FROM athletes WHERE id = ?");
    	$stmt->bind_param("i", $fencerId);
    	$stmt->execute();
    	$stmt->store_result();
    
    	$exists = $stmt->num_rows > 0;
    
    	$stmt->close();
    	$db->close();
    	return $exists;
}

function searchAthletes($name = '', $gender = '', $weapon = '', $country = '') {
    	$db = dbConnect();
    	$query = "SELECT id, name, gender, weapon, weapon2, nationality FROM athletes WHERE 1=1";
    	$params = [];
    	$types = '';

    	if ($name) {
        	$query .= " AND name LIKE ?";
        	$params[] = '%' . $name . '%';
        	$types .= 's';
    	}
    	if ($gender) {
        	$query .= " AND gender = ?";
        	$params[] = $gender;
        	$types .= 's';
    	}
    	if ($weapon) {
        	$query .= " AND (weapon = ? OR weapon2 = ?)";
        	$params[] = $weapon;
        	$params[] = $weapon;
        	$types .= 'ss';
    	}
   	if ($country) {
        	$query .= " AND nationality = ?";
        	$params[] = strtoupper($country);
        	$types .= 's';
    	}

    	$stmt = $db->prepare($query);
    	if ($types) {
        	$stmt->bind_param($types, ...$params);
    	}
    	$stmt->execute();
    	$result = $stmt->get_result();
    
    	$athletes = [];
    	while ($row = $result->fetch_assoc()) {
        	$athletes[] = $row;
    	}
    	$stmt->close();
    	$db->close();
    	return $athletes;
}
?>
