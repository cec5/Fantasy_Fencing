<?php
require_once(dirname(__DIR__) . '/src/ResultService.php');

// Simple script that updates each Athlete's total earned points for a given seasons

print_r("Database Update, Update Points for Season: ");
$season = readline();

ResultService::updateAthleteSeasonPoints($season);
?>
