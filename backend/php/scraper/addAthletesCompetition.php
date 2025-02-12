<?php
require_once('scraperFunctions.php');
require_once('../databaseFunctions.php');

// Script for the retrieval of Athletes from a particular competition and the addition of all of them into the db
print_r("Add Athletes from Competition, Enter Season: ");
$season = readline();
print_r("Enter Competition ID: ");
$id = readline();
$result = scrapeAthletesCompetition($season, $id);

if (!$result) {
    	print_r("No competition was found or competition did not finish, Season: [$season] with ID: [$id]\n");
} else {
    	foreach ($result as $x) {
        	// Check if the athlete already exists in the database before scraping
        	if (athleteExists($x)) {
            		echo "Skipping athlete ID $x because they already exist in the database.\n";
            		continue;
        	}

        	// Scrape the athlete data only if they do not exist in the database
        	$y = scrapeAthleteData($x);

        	if ($y) {
            		try {
                		updateAthlete($y);
                		echo "Successfully added/updated athlete ID $x.\n";
            		} catch (Exception $e) {
                		echo "Failed to insert/update Athlete ID: [$x]: " . $e->getMessage() . "\n";
            		}
        	} else {
            		// Log the skipped athlete due to missing or incomplete data
            		echo "Skipping athlete ID $x due to missing or incomplete data.\n";
        	}
    	}
}
?>
