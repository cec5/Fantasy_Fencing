<?php
require_once(dirname(__DIR__) . '/src/FIEScraperService.php');
require_once(dirname(__DIR__) . '/src/CompetitionService.php');

// Adds/Updates a competition to the Database
print_r("Add Competition Data, Enter Season: ");
$season = readline();
print_r("Enter Competition ID: ");
$id = readline();
$result = FIEScraperService::scrapeCompetitionData($season, $id);

if (!$result){
	print_r("Unable to add to database as no competition was found in Season: [$season] with ID: [$id]\n");
} else {
	CompetitionService::updateCompetition($result);
}
?>
