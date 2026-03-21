<?php // Updates fantasy points for all users for a given season; will automate in the future
require_once(dirname(__DIR__) . 'FantasyService.php');

print_r("Enter Season: ");
$season = readline();

FantasyService::calculateFantasyPointsForSeason($season);
