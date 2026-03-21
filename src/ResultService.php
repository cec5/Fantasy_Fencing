<?php
require_once('dbConnect.php');
require_once(dirname(__DIR__) . '/src/AthleteService.php'); // Needed for athleteExists helper

class ResultService {
    public static function updateCompetitionResults($results) {
        $db = dbConnect();
        $stmt = $db->prepare("
            INSERT INTO competitionResults (competitionId, season, athleteId, finished, points)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE finished = VALUES(finished), points = VALUES(points)
        ");

        if (!$stmt) return;

        foreach ($results as $result) {
            try {
                if (!AthleteService::athleteExists($result['athleteId'])) continue;

                $stmt->bind_param("iiidd", $result['competitionId'], $result['season'], $result['athleteId'], $result['finished'], $result['points']);
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                echo "Exception caught: " . $e->getMessage() . "\n";
            }
        }
        $stmt->close();
        $db->close();
    }

    public static function updateAthleteSeasonPoints($season) {
        $db = dbConnect();
        $query = "
            SELECT cr.athleteId, c.weapon, c.ageCategory, SUM(cr.points) as totalPoints
            FROM competitionResults cr JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE cr.season = ? GROUP BY cr.athleteId, c.weapon, c.ageCategory
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $season);
        $stmt->execute();
        $result = $stmt->get_result();

        $updateStmt = $db->prepare("
            INSERT INTO athleteSeasonPoints (athleteId, season, weapon, ageCategory, points)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE points = VALUES(points)
        ");

        while ($row = $result->fetch_assoc()) {
            $updateStmt->bind_param("iissd", $row['athleteId'], $season, $row['weapon'], $row['ageCategory'], $row['totalPoints']);
            $updateStmt->execute();
        }
        $stmt->close();
        $updateStmt->close();
        $db->close();
    }

    public static function getTotalPoints($athleteId, $season, $weapon, $ageCategory) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT points FROM athleteSeasonPoints WHERE athleteId = ? AND season = ? AND weapon = ? AND ageCategory = ?");
        $stmt->bind_param("iiss", $athleteId, $season, $weapon, $ageCategory);
        $stmt->execute();
        $stmt->bind_result($points);
        $stmt->fetch();
        $stmt->close();
        $db->close();
        return $points ?: 0;
    }

    public static function getCompetitionResults($athleteId, $season, $weapon, $ageCategory = 'S') {
        $db = dbConnect();
        $query = "
            SELECT cr.points, cr.finished, c.season, c.competitionId, c.name, c.category, c.location, c.country, c.startDate, c.ageCategory
            FROM competitionResults cr JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE cr.athleteId = ? AND cr.season = ? AND c.weapon = ? AND c.ageCategory = ? ORDER BY c.startDate ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("iiss", $athleteId, $season, $weapon, $ageCategory);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $results = [];
        while ($row = $result->fetch_assoc()) $results[] = $row;
        
        $stmt->close();
        $db->close();
        return $results;
    }

    public static function getTopEarners($season, $weapon, $gender, $ageCategory) {
        $db = dbConnect();
        $query = "
            SELECT a.id, a.name, a.nationality, asp.points, asp.ageCategory
            FROM athleteSeasonPoints asp JOIN athletes a ON asp.athleteId = a.id
            WHERE asp.season = ? AND asp.weapon = ? AND a.gender = ? AND asp.ageCategory = ? ORDER BY asp.points DESC, a.name ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("isss", $season, $weapon, $gender, $ageCategory);
        $stmt->execute();
        $result = $stmt->get_result();

        $athletes = [];
        while ($row = $result->fetch_assoc()) $athletes[] = $row;

        $stmt->close();
        $db->close();
        return $athletes;
    }

    public static function getSpecificCompetitionResult($competitionId, $season) {
        $db = dbConnect();
        $query = "
            SELECT cr.athleteId, cr.finished as place, a.name, a.nationality, cr.points 
            FROM competitionResults cr JOIN athletes a ON cr.athleteId = a.id 
            WHERE cr.competitionId = ? AND cr.season = ? ORDER BY cr.finished ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ii", $competitionId, $season);
        $stmt->execute();
        $result = $stmt->get_result();

        $results = [];
        while ($row = $result->fetch_assoc()) $results[] = $row;
        
        $stmt->close();
        $db->close();
        return $results;
    }

    public static function competitionResultsExist($season, $competitionId) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT 1 FROM competitionResults WHERE season = ? AND competitionId = ? LIMIT 1");
        $stmt->bind_param("ii", $season, $competitionId);
        $stmt->execute();
        $stmt->store_result();
        
        $exists = $stmt->num_rows > 0;
        
        $stmt->close();
        $db->close();
        return $exists;
    }
}
?>