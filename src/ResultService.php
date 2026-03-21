<?php
require_once(dirname(__DIR__) . '/src/database/Database.php');
require_once(dirname(__DIR__) . '/src/AthleteService.php');

class ResultService {
    public static function updateCompetitionResults($results) {
        $db = Database::getInstance();
        $query = "
            INSERT INTO competitionResults (competitionId, season, athleteId, finished, points)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE finished = VALUES(finished), points = VALUES(points)
        ";

        foreach ($results as $result) {
            try {
                if (!AthleteService::athleteExists($result['athleteId'])) continue;
                $db->execute($query, "iiidd", [
                    $result['competitionId'], $result['season'], $result['athleteId'], 
                    $result['finished'], $result['points']
                ]);
            } catch (Exception $e) {
                echo "Exception caught: " . $e->getMessage() . "\n";
            }
        }
    }

    public static function updateAthleteSeasonPoints($season) {
        $db = Database::getInstance();
        $query = "
            SELECT cr.athleteId, c.weapon, c.ageCategory, SUM(cr.points) as totalPoints
            FROM competitionResults cr JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE cr.season = ? GROUP BY cr.athleteId, c.weapon, c.ageCategory
        ";
        
        $results = $db->fetchAll($query, "i", [$season]);
        $updateQuery = "
            INSERT INTO athleteSeasonPoints (athleteId, season, weapon, ageCategory, points)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE points = VALUES(points)
        ";

        foreach ($results as $row) {
            $db->execute($updateQuery, "iissd", [
                $row['athleteId'], $season, $row['weapon'], $row['ageCategory'], $row['totalPoints']
            ]);
        }
    }

    public static function getTotalPoints($athleteId, $season, $weapon, $ageCategory) {
        $db = Database::getInstance();
        $query = "SELECT points FROM athleteSeasonPoints WHERE athleteId = ? AND season = ? AND weapon = ? AND ageCategory = ?";
        $points = $db->fetchColumn($query, "iiss", [$athleteId, $season, $weapon, $ageCategory]);
        return $points ?: 0;
    }

    public static function getCompetitionResults($athleteId, $season, $weapon, $ageCategory = 'S') {
        $db = Database::getInstance();
        $query = "
            SELECT cr.points, cr.finished, c.season, c.competitionId, c.name, c.category, c.location, c.country, c.startDate, c.ageCategory
            FROM competitionResults cr JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE cr.athleteId = ? AND cr.season = ? AND c.weapon = ? AND c.ageCategory = ? ORDER BY c.startDate ASC
        ";
        return $db->fetchAll($query, "iiss", [$athleteId, $season, $weapon, $ageCategory]);
    }

    public static function getTopEarners($season, $weapon, $gender, $ageCategory) {
        $db = Database::getInstance();
        $query = "
            SELECT a.id, a.name, a.nationality, asp.points, asp.ageCategory
            FROM athleteSeasonPoints asp JOIN athletes a ON asp.athleteId = a.id
            WHERE asp.season = ? AND asp.weapon = ? AND a.gender = ? AND asp.ageCategory = ? ORDER BY asp.points DESC, a.name ASC
        ";
        return $db->fetchAll($query, "isss", [$season, $weapon, $gender, $ageCategory]);
    }

    public static function getSpecificCompetitionResult($competitionId, $season) {
        $db = Database::getInstance();
        $query = "
            SELECT cr.athleteId, cr.finished as place, a.name, a.nationality, cr.points 
            FROM competitionResults cr JOIN athletes a ON cr.athleteId = a.id 
            WHERE cr.competitionId = ? AND cr.season = ? ORDER BY cr.finished ASC
        ";
        return $db->fetchAll($query, "ii", [$competitionId, $season]);
    }

    public static function competitionResultsExist($season, $competitionId) {
        $db = Database::getInstance();
        return (bool) $db->fetchColumn(
            "SELECT 1 FROM competitionResults WHERE season = ? AND competitionId = ? LIMIT 1", 
            "ii", 
            [$season, $competitionId]
        );
    }
}
?>