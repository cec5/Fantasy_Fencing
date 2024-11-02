<?php
require_once('../scraperFunctions.php');
require_once('../databaseFunctions.php');

// Adds/Updates a singular Athlete to the Database
$fencerID = readline();
$result = scrapeFencerData($fencerID);

if (!$result){
	print_r("Unable to add to database as there's no athlete associated with ID: $fencerID\n");
} else {
	updateAthlete($result);
}
?>
