<?php // This file contains functions relating to the fantasy application

require_once('dbConnect.php');

// Helper Function: Checks if the competition is locked (1 day prior to start date)
function isCompetitionLocked($season, $competitionId) {
    	$db = dbConnect();
   	$stmt = $db->prepare("SELECT startDate FROM competitions WHERE competitionId = ? AND season = ?");
    	$stmt->bind_param("ii", $competitionId, $season);
    	$stmt->execute();
    	$stmt->bind_result($startDate);
    	$stmt->fetch();
    	$stmt->close();
    	$db->close();
    	return strtotime($startDate) <= strtotime('+1 day'); // Locked 1 day before the start date
}

// Self-explantory, use in leaderboard.php
function getFantasyLeaderboard($season, $weapon, $gender, $ageCategory) {
    	$db = dbConnect();

    	$query = "
        	SELECT u.id, u.username, u.nationality, ftp.totalPoints 
        	FROM fantasyTotalPoints ftp
        	JOIN users u ON ftp.userId = u.id
        	WHERE ftp.season = ? AND ftp.weapon = ? AND ftp.gender = ? AND ftp.ageCategory = ?
        	ORDER BY ftp.totalPoints DESC, u.username ASC
    		";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("isss", $season, $weapon, $gender, $ageCategory);
    	$stmt->execute();
    	$result = $stmt->get_result();

    	$leaderboard = [];
    	while ($row = $result->fetch_assoc()) {
        	$leaderboard[] = $row;
    	}
    	$stmt->close();
    	$db->close();
    	return $leaderboard;
}

// Retrieves user current selections for a given competition
function getUserSelections($userId, $season, $competitionId) {
    	$db = dbConnect();
    
    	$query = "
        	SELECT us.athleteId, a.name, a.nationality 
        	FROM userSelections us
        	JOIN athletes a ON us.athleteId = a.id
        	WHERE us.userId = ? AND us.season = ? AND us.competitionId = ?
    	";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param("iii", $userId, $season, $competitionId);
    	$stmt->execute();
    	$result = $stmt->get_result();
    
    	$selections = [];
    	while ($row = $result->fetch_assoc()) {
        	$selections[] = $row;
    	}
    	$stmt->close();
    	$db->close();
    	return $selections;
}

// Function to calculate a total fantasy points for all users of a given season
// TODO: Either include this in the automated script or write a separate script
function calculateFantasyPointsForSeason($season) {
    	$db = dbConnect();

    	// Sum total points for all users based on athlete results
    	$query = "
        	INSERT INTO fantasyTotalPoints (userId, season, weapon, gender, ageCategory, totalPoints)
        	SELECT us.userId, c.season, c.weapon, c.gender, c.ageCategory, COALESCE(SUM(cr.points), 0)
        	FROM userSelections us
        	JOIN competitionResults cr ON us.athleteId = cr.athleteId 
            		AND us.competitionId = cr.competitionId 
            		AND us.season = cr.season
        	JOIN competitions c ON cr.competitionId = c.competitionId 
            		AND cr.season = c.season
        	WHERE us.season = ?
        	GROUP BY us.userId, c.season, c.weapon, c.gender, c.ageCategory
        	ON DUPLICATE KEY UPDATE totalPoints = VALUES(totalPoints)
    	";

    	$stmt = $db->prepare($query);
    	$stmt->bind_param("i", $season);
    	$stmt->execute();
    	$stmt->close();
    	$db->close();
}

// Handles Drafting Logic (Add/Drop)
function updateUserSelection($userId, $season, $competitionId, $athleteId, $action) {
    	$db = dbConnect();
    	
    	if (isCompetitionLocked($season, $competitionId)){
    		$db->close();
    		return ["success" => false, "message" => "Athlete selections for this competition is locked"];
    	}

    	if ($action === 'add') {
        	// Check if athlete is already selected
        	$stmt = $db->prepare("
            		SELECT COUNT(*) FROM userSelections 
            		WHERE userId = ? AND season = ? AND competitionId = ? AND athleteId = ?
        	");
        	$stmt->bind_param("iiii", $userId, $season, $competitionId, $athleteId);
        	$stmt->execute();
        	$stmt->bind_result($count);
        	$stmt->fetch();
        	$stmt->close();

        	if ($count > 0) {
        		// TODO: Design frontend to prevent this happening to begin with, write modified version of searchAthletes
        		$db->close();
            		return ["success" => false, "message" => "Athlete is already selected"];
        	}

       		// Check if user already has 5 selections
        	$stmt = $db->prepare("
            		SELECT COUNT(*) FROM userSelections 
            		WHERE userId = ? AND season = ? AND competitionId = ?
        	");
        	$stmt->bind_param("iii", $userId, $season, $competitionId);
        	$stmt->execute();
        	$stmt->bind_result($selectionCount);
        	$stmt->fetch();
        	$stmt->close();

       	 	if ($selectionCount >= 5) {
       	 		$db->close();
       	 		// TODO: Design frontend where this normally shouldn't be triggered
            		return ["success" => false, "message" => "You can only select up to 5 Athletes"];
        	}

        	// Add selection into database
        	$stmt = $db->prepare("
            		INSERT INTO userSelections (userId, season, competitionId, athleteId)
            		VALUES (?, ?, ?, ?)
        	");
        	$stmt->bind_param("iiii", $userId, $season, $competitionId, $athleteId);
        	$stmt->execute();
        	$stmt->close();
        	$db->close();
        	return ["success" => true, "message" => "Athlete added!"];
    	} elseif ($action === 'remove') {
        	// Remove the athlete from selection
        	$stmt = $db->prepare("
            		DELETE FROM userSelections 
            		WHERE userId = ? AND season = ? AND competitionId = ? AND athleteId = ?
        	");
        	$stmt->bind_param("iiii", $userId, $season, $competitionId, $athleteId);
        	$stmt->execute();
        	$stmt->close();
        	$db->close();
        	return ["success" => true, "message" => "Athlete dropped!"];
    	}
    	$db->close();
    	// Normally should never happen
    	return ["success" => false, "message" => "ERROR; Invalid Action "];
}

// Modified version of getCompetitions(); used in fantasy.php to retrieve and filter for upcoming competitions
function getFilteredUpcomingCompetitions($season, $weapon = '', $gender = '', $ageCategory = '') {
    	$db = dbConnect();
    
    	$query = "
        	SELECT competitionId, name, startDate, location, country, category, weapon, gender, ageCategory 
        	FROM competitions 
        	WHERE season = ? AND startDate > CURDATE()
    	";
    	$params = [$season];
    	$types = "i";

    	if ($weapon) {
        	$query .= " AND weapon = ?";
        	$params[] = $weapon;
        	$types .= "s";
    	}
    	if ($gender) {
        $query .= " AND gender = ?";
        $params[] = $gender;
        $types .= "s";
    	}
    	if ($ageCategory) {
        	$query .= " AND ageCategory = ?";
        	$params[] = $ageCategory;
        	$types .= "s";
    	}

    	$query .= " ORDER BY startDate ASC";
    	$stmt = $db->prepare($query);
    	$stmt->bind_param($types, ...$params);
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

// Get all competitions user has made selections for
function getUserCompetitions($userId, $season, $isPast, $weapon = '', $gender = '', $ageCategory = '') {
    	$db = dbConnect();

    	$query = "
        	SELECT DISTINCT c.*
        	FROM competitions c
        	JOIN userSelections us ON c.competitionId = us.competitionId AND c.season = us.season
        	WHERE us.userId = ? AND c.season = ?
    	";
    	$params = [$userId, $season];
    	$types = "ii";

    	if ($weapon) { $query .= " AND c.weapon = ?"; $params[] = $weapon; $types .= "s"; }
    	if ($gender) { $query .= " AND c.gender = ?"; $params[] = $gender; $types .= "s"; }
    	if ($ageCategory) { $query .= " AND c.ageCategory = ?"; $params[] = $ageCategory; $types .= "s"; }

    	$query .= $isPast ? " AND c.startDate <= CURDATE()" : " AND c.startDate > CURDATE()";
    	$query .= " ORDER BY c.startDate ASC";

    	$stmt = $db->prepare($query);
    	$stmt->bind_param($types, ...$params);
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
