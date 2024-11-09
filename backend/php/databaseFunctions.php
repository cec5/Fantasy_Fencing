<?php
require_once('dbConnect.php');

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
        	INSERT INTO competitionResults (competitionId, season, athleteId, finished, points)
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
            		if (!athleteExists($result['athleteId'])) {
                		echo "Skipping result for Athlete ID: [" . $result['athleteId'] . "] as they do not exist in the database.\n";
                		continue;
            		}

            		// Bind parameters and execute the insertion
            		$stmt->bind_param(
                		"iiidd",
               		 	$result['competitionId'],
                		$result['season'],
                		$result['athleteId'],
                		$result['finished'],
                		$result['points']
            		);

            		if (!$stmt->execute()) {
                		throw new mysqli_sql_exception("Error inserting/updating result for athlete ID " . $result['athleteId'] . ": " . $stmt->error);
            		} else {
                		echo "Inserted/updated result for Athlete ID: [" . $result['athleteId'] . "]\n";
            		}
        	} catch (mysqli_sql_exception $e) {
            		// Catch and log the error without stopping the script
            		echo "Exception caught: " . $e->getMessage() . "\n";
        	}
    	}
    		$stmt->close();
    		$db->close();
}

// Helper Function for updateCompetitionResults() to prevent an integrity failure
function athleteExists($athleteId) {
    	$db = dbConnect();
    
    	$stmt = $db->prepare("SELECT 1 FROM athletes WHERE id = ?");
    	$stmt->bind_param("i", $athleteId);
    	$stmt->execute();
    	$stmt->store_result();
    
    	$exists = $stmt->num_rows > 0;
    
    	$stmt->close();
    	$db->close();
    	return $exists;
}

// Searches/Filters Athletes from the db that meet the filters, used in searchAthletes.php
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

// Calculates total points for all Athlete in the given Season
function updateAthleteSeasonPoints($season) {
    	$db = dbConnect();

    	// Calculate total points per athlete and weapon for the given season by joining with competitions
    	$query = "
        	SELECT cr.athleteId, c.weapon, SUM(cr.points) as totalPoints
        	FROM competitionResults cr
        	JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
        	WHERE cr.season = ?
        	GROUP BY cr.athleteId, c.weapon
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("i", $season);
    	$stmt->execute();
    	$result = $stmt->get_result();

    	// Insert or update each athlete's total points by weapon in athleteSeasonPoints
    	$updateQuery = "
        	INSERT INTO athleteSeasonPoints (athleteId, season, weapon, points)
        	VALUES (?, ?, ?, ?)
        	ON DUPLICATE KEY UPDATE points = VALUES(points)
    	";
    	$updateStmt = $db->prepare($updateQuery);

    	// Loop through each athlete's total points per weapon and update athleteSeasonPoints
    	while ($row = $result->fetch_assoc()) {
        	$athleteId = $row['athleteId'];
        	$weapon = $row['weapon'];
        	$totalPoints = $row['totalPoints'];

        	$updateStmt->bind_param("iisd", $athleteId, $season, $weapon, $totalPoints);
        	if ($updateStmt->execute()) {
            		echo "Updated total points for athlete ID: [$athleteId] in Season [$season] in [$weapon] to [$totalPoints] points\n";
        	} else {
            		echo "Error updating points for athlete ID [$athleteId]: " . $updateStmt->error . "\n";
        	}
    	}
    	$stmt->close();
    	$updateStmt->close();
    	$db->close();
}

// Gets Athlete Info, used in Athlete.php
function getAthleteInfo($athleteId) {
    	$db = dbConnect();
    	$stmt = $db->prepare("SELECT firstName, lastName, gender, nationality FROM athletes WHERE id = ?");
    	$stmt->bind_param("i", $athleteId);
    	$stmt->execute();
    	$result = $stmt->get_result();
    	$athlete = $result->fetch_assoc();
    	$stmt->close();
    	$db->close();
    	return $athlete;
}

function getAthleteWeapons($athleteId) {
    	$db = dbConnect();
    	$stmt = $db->prepare("SELECT weapon, weapon2 FROM athletes WHERE id = ?");
    	$stmt->bind_param("i", $athleteId);
    	$stmt->execute();
    	$result = $stmt->get_result()->fetch_assoc();
    	$weapons = array_filter([$result['weapon'], $result['weapon2']]);
    	$stmt->close();
    	$db->close();
    	return $weapons;
}

function getTotalPoints($athleteId, $season, $weapon) {
    	$db = dbConnect();
    	$stmt = $db->prepare("SELECT points FROM athleteSeasonPoints WHERE athleteId = ? AND season = ? AND weapon = ?");
    	$stmt->bind_param("iis", $athleteId, $season, $weapon);
    	$stmt->execute();
    	$stmt->bind_result($points);
    	$stmt->fetch();
    	$stmt->close();
    	$db->close();
    	return $points ?: 0;
}

// Get Competition Results for a given Athlete in a Particular Season
function getCompetitionResults($athleteId, $season, $weapon) {
    	$db = dbConnect();
    	$query = "
        	SELECT cr.points, cr.finished, c.season, c.competitionId, c.name, c.category, c.location, c.country, c.startDate
        	FROM competitionResults cr
        	JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
        	WHERE cr.athleteId = ? AND cr.season = ? AND c.weapon = ?
        	ORDER BY c.startDate ASC
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("iis", $athleteId, $season, $weapon);
    	$stmt->execute();
    	$result = $stmt->get_result();
    	$results = [];
    	while ($row = $result->fetch_assoc()) {
        	$results[] = $row;
    	}
    	$stmt->close();
    	$db->close();
    	return $results;
}

// Get top point earners for a given season, weapon, and gender
function getTopEarners($season, $weapon, $gender) {
    	$db = dbConnect();
    
    	$query = "
        	SELECT a.id, a.name, a.nationality, asp.points
        	FROM athleteSeasonPoints asp
        	JOIN athletes a ON asp.athleteId = a.id
        	WHERE asp.season = ? AND asp.weapon = ? AND a.gender = ?
        	ORDER BY asp.points DESC, a.name ASC
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("iss", $season, $weapon, $gender);
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

// Get list of competitions filtered by season, weapon, and gender, ordered by date
function getCompetitions($season, $weapon, $gender) {
    	$db = dbConnect();
    
    	$query = "
        	SELECT competitionId, season, name, category, location, country, startDate 
        	FROM competitions 
        	WHERE season = ? AND weapon = ? AND gender = ?
        	ORDER BY startDate ASC
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("iss", $season, $weapon, $gender);
    	$stmt->execute();
    	$result = $stmt->get_result();
    
    	$competitions = [];
    	while ($row = $result->fetch_assoc()) {
        	$competitions[] = $row;
    	}
    	$stmt->close();
    	$db->close();
   	return $competitions;
}

// Get details of a specific competition
function getCompetitionDetails($competitionId, $season) {
    	$db = dbConnect();

    	$query = "
        	SELECT name, startDate, endDate, location, country, weapon, gender, category
        	FROM competitions 
        	WHERE competitionId = ? AND season = ?
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("ii", $competitionId, $season);
    	$stmt->execute();
    	$result = $stmt->get_result();
    	$competition = $result->fetch_assoc();
    	$stmt->close();
    	$db->close();    
    	return $competition;
}

// Get results for a specific Competition
function getSpecificCompetitionResult($competitionId, $season) {
    	$db = dbConnect();

    	$query = "
        	SELECT cr.athleteId, cr.finished as place, a.name, a.nationality, cr.points 
        	FROM competitionResults cr 
        	JOIN athletes a ON cr.athleteId = a.id 
        	WHERE cr.competitionId = ? AND cr.season = ?
        	ORDER BY cr.finished ASC
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("ii", $competitionId, $season);
    	$stmt->execute();
    	$result = $stmt->get_result();

    	$results = [];
    	while ($row = $result->fetch_assoc()) {
        	$results[] = $row;
    	}
    	$stmt->close();
    	$db->close();
    	return $results;
}
?>
