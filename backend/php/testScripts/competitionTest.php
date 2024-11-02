<?php
require_once('../scraperFunctions.php');

// Test script for the retrieval of basic competition data from the FIE website
print_r("Enter Season: ");
$season = readline();
print_r("Enter Competition ID: ");
$id = readline();
$result = scrapeCompetitionData($season, $id);

if (!$result){
	print_r("No competition was found in Season: [$season] with ID: [$id]\n");
} else {
	print_r($result);
}
?>
