<?php
require_once('../scraperFunctions.php');

// Test script for the retrieval of an athlete from the FIE website
print_r("Enter Athlete ID: ");
$fencerID = readline();
$result = scrapeFencerData($fencerID);

if (!$result){
	print_r("There's no athlete associated with ID: [$fencerID]\n");
} else {
	print_r($result);
}
?>
