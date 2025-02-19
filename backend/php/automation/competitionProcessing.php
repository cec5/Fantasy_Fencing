<?php
require_once('../scraper/scraperFunctions.php');
require_once('../databaseFunctions.php');

function logUnaddedAthletes($season, $competitionId, $skippedCount, $unaddedAthletes) {
    	$logDir = __DIR__ . "/logs";
    	if (!is_dir($logDir)) {
        	mkdir($logDir, 0777, true);
    	}

    	$logFile = "$logDir/{$season}_{$competitionId}_log.txt";
    	$content = "Competition Season: [$season]\nCompetition ID: [$competitionId]\n";
    	$content .= "Skipped Athletes (Already in DB): $skippedCount\n";
    	$content .= "Unadded Athletes:\n" . implode("\n", $unaddedAthletes) . "\n";
    
    	file_put_contents($logFile, $content, FILE_APPEND);
}

function competitionResultsExist($season, $competitionId) {
    	$db = dbConnect();
    	$stmt = $db->prepare("SELECT 1 FROM competitionResults WHERE season = ? AND competitionId = ? LIMIT 1");
    	$stmt->bind_param("ii", $season, $competitionId);
    	$stmt->execute();
    	$stmt->store_result();
    
    	$exists = $stmt->num_rows > 0;
    
    	$stmt->close();
    	$db->close();
    	return $exists;
}

function processCompletedCompetitions() {
    	$db = dbConnect();
    
    	// Find competitions that ended within the past week
    	$query = "SELECT competitionId, season, endDate FROM competitions WHERE endDate BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    	$result = $db->query($query);
    
    	if ($result->num_rows === 0) {
        	echo "No competition was found within the past 7 days\n";
        	return;
    	}

    	while ($row = $result->fetch_assoc()) {
        	$competitionId = $row['competitionId'];
        	$season = $row['season'];

        	// Skip if competition results already exist
        	if (competitionResultsExist($season, $competitionId)) {
            		echo "Results for Competition ID: [$competitionId] already exist. Skipping...\n";
            		continue;
        	}

        	echo "Processing Competition: [$competitionId] for Season: [$season]\n";

        	// Try to fetch results immediately
        	$competitionResults = scrapeCompetitionResults($season, $competitionId);
        
        	// If results are not available, schedule a retry
        	if (!$competitionResults) {
            		echo "Results not yet available for Competition: [$competitionId]; Will check again in 12 hours.\n";
            		continue; // Moves on to check the next competition without waiting
        	}

        	echo "Results found for Competition ID: [$competitionId]\n";

        	// Add Athletes from the Competition
        	$athletes = scrapeAthletesCompetition($season, $competitionId);
        	if (!$athletes) {
            		echo "ERROR; No Athletes found for Competition ID: [$competitionId]\n";
            		continue;
        	}

        	$skippedCount = 0;
        	$unaddedAthletes = [];

        	foreach ($athletes as $athleteId) {
            		if (athleteExists($athleteId)) {
                		$skippedCount++;
                		continue;
            		}

            		$athleteData = scrapeAthleteData($athleteId);
            		if ($athleteData && $athleteData['weapon']) {
                		try {
                    			updateAthlete($athleteData);
                		} catch (Exception $e) {
                    			echo "Error inserting Athlete ID: [$athleteId] - " . $e->getMessage() . "\n";
                    			$unaddedAthletes[] = $athleteId;
                		}
            		} else {
                		$unaddedAthletes[] = $athleteId;
            		}
        	}

        	// Log unadded athletes
        	logUnaddedAthletes($season, $competitionId, $skippedCount, $unaddedAthletes);
        
        	updateCompetitionResults($competitionResults);
        	updateAthleteSeasonPoints($season);

        	echo "Finished Processing Competition ID: [$competitionId]\n";
    	}

    	$db->close();
}

processCompletedCompetitions();
?>

