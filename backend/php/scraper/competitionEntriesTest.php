<?php // Test script for the retrieval of Athlete's IDs who are entered in a particular competition
require_once('scraperFunctions.php');

print_r("Enter Season: ");
$season = readline();
print_r("Enter Competition ID: ");
$competitionId = readline();
$result = scrapeCompetitionEntries($season, $competitionId);

if (!$result){
	print_r("Competition: [$competitionId] of Season: [$season] doesn't exist or there are no current entries\n");
} else {
	print_r($result);
}
?>
