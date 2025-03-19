<?php // Updates fantasy points for all users for a given season; will automate in the future
require_once('fantasyFunctions.php');

print_r("Enter Season: ");
$season = readline();

calculateFantasyPointsForSeason($season);
