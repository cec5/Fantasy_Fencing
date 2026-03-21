<?php
require_once(dirname(__DIR__) . '/src/FIEScraperService.php');
require_once(dirname(__DIR__) . '/src/AthleteService.php');
require_once(dirname(__DIR__) . '/src/CompetitionService.php');
require_once(dirname(__DIR__) . '/src/ResultService.php');

class AutomationLogger {
    public static function logUnaddedAthletes($season, $competitionId, $skippedCount, $unaddedAthletes) {
        $logDir = __DIR__ . "/logs";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = "$logDir/{$season}_{$competitionId}_log.txt";
        $content = "Competition Season: [$season]\nCompetition ID: [$competitionId]\n";
        $content .= "Skipped Athletes (Already in DB): $skippedCount\n";
        $content .= "Unadded Athletes:\n" . implode("\n", $unaddedAthletes) . "\n";
        file_put_contents($logFile, $content);
    }
}

function processCompletedCompetitions() {
    $recentCompetitions = CompetitionService::getRecentlyCompletedCompetitions(7);

    if (empty($recentCompetitions)) {
        echo "No competition was found within the past 7 days\n";
        return;
    }

    foreach ($recentCompetitions as $comp) {
        $competitionId = $comp['competitionId'];
        $season = $comp['season'];

        /*
        if (ResultService::competitionResultsExist($season, $competitionId)) {
            echo "Results for Competition ID: [$competitionId] already exist. Skipping...\n";
            continue;
        }
        */

        echo "Processing Competition: [$competitionId] for Season: [$season]\n";

        // Fetch Results
        $competitionResults = FIEScraperService::scrapeCompetitionResults($season, $competitionId);

        if (!$competitionResults) {
            echo "Results not yet available for Competition: [$competitionId]; Will check again later.\n";
            continue; 
        }

        echo "Results found for Competition ID: [$competitionId]\n";

        $athleteIds = array_column($competitionResults, 'athleteId');

        // Process Athletes
		$skippedCount = 0;
        $unaddedAthletes = [];

        foreach ($athleteIds as $athleteId) {
            if (AthleteService::athleteExists($athleteId)) {
                $skippedCount++;
                continue;
            }

            // If they don't exist, scrape their specific profile and add them
            $athleteData = FIEScraperService::scrapeAthleteData($athleteId);
            
            if ($athleteData && $athleteData['weapon']) {
                try {
                    AthleteService::updateAthlete($athleteData);
                } catch (Exception $e) {
                    echo "Error inserting Athlete ID: [$athleteId] - " . $e->getMessage() . "\n";
                    $unaddedAthletes[] = $athleteId;
                }
            } else {
                $unaddedAthletes[] = $athleteId;
            }
        }

        // Log and Update Results
        AutomationLogger::logUnaddedAthletes($season, $competitionId, $skippedCount, $unaddedAthletes);

        ResultService::updateCompetitionResults($competitionResults);
        ResultService::updateAthleteSeasonPoints($season);

        echo "Finished Processing Competition ID: [$competitionId]\n";
    }
}

processCompletedCompetitions();
?>