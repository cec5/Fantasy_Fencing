<?php
require_once('../scraperFunctions.php');
require_once('../databaseFunctions.php');

// Adds/Updates a competition to the Database
print_r("Database Update, Enter Season: ");
$season = readline();
print_r("Enter Competition ID: ");
$id = readline();
$result = scrapeCompetitionData($season, $id);

if (!$result){
	print_r("Unable to add to database as no competition was found in Season: [$season] with ID: [$id]\n");
} else {
	updateCompetition($result);
}
?>
