<?php
require_once('../scraperFunctions.php');
require_once('../databaseFunctions.php');

// Adds/Updates a singular Athlete to the Database based on data from the FIE website
print_r("Database Update, Enter Athlete ID: ");
$fencerID = readline();
$result = scrapeAthleteData($fencerID);

if (!$result){
	print_r("Unable to add to database as there's no athlete associated with ID: $fencerID\n");
} else {
	updateAthlete($result);
}
?>
