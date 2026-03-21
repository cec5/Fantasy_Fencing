<?php
require_once(dirname(__DIR__) . '/src/FIEScraperService.php');
require_once(dirname(__DIR__) . '/src/AthleteService.php');

// Adds/Updates a singular Athlete to the Database based on data from the FIE website
print_r("Database Update, Enter Athlete ID: ");
$fencerID = readline();
$result = FIEScraperService::scrapeAthleteData($fencerID);

if (!$result){
	print_r("Unable to add to database as there's no Athlete associated with ID: [$fencerID]\n");
} else {
	AthleteService::updateAthlete($result);
}
?>
