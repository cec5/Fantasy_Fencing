<?php
require_once(dirname(__DIR__) . '/src/database/Database.php');

class CompetitionService {
    public static function updateCompetition($data) {
        $db = Database::getInstance();
        $query = "
            INSERT INTO competitions (competitionId, season, name, category, weapon, gender, country, location, startDate, endDate, ageCategory)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name), category = VALUES(category), weapon = VALUES(weapon), gender = VALUES(gender),
                country = VALUES(country), location = VALUES(location), startDate = VALUES(startDate), endDate = VALUES(endDate), ageCategory = VALUES(ageCategory)
        ";
        
        try {
            $db->execute($query, "iisssssssss", [
                $data['competitionId'], $data['season'], $data['name'], $data['category'], 
                $data['weapon'], $data['gender'], $data['country'], $data['location'], 
                $data['startDate'], $data['endDate'], $data['ageCategory']
            ]);
            echo "Data stored successfully for Competition: [{$data['competitionId']}] of Season: [{$data['season']}]\n";
        } catch (Exception $e) {
            echo "Error storing competition data: " . $e->getMessage();
        }
    }

    public static function getCompetitions($season, $weapon, $gender, $ageCategory) {
        $db = Database::getInstance();
        $query = "SELECT competitionId, season, name, category, location, country, startDate FROM competitions WHERE season = ? AND weapon = ? AND gender = ? AND ageCategory = ? ORDER BY startDate ASC";
        return $db->fetchAll($query, "isss", [$season, $weapon, $gender, $ageCategory]);
    }

    public static function getCompetitionDetails($competitionId, $season) {
        $db = Database::getInstance();
        $query = "SELECT name, startDate, endDate, location, country, weapon, gender, category, ageCategory FROM competitions WHERE competitionId = ? AND season = ?";
        return $db->fetchOne($query, "ii", [$competitionId, $season]);
    }

    public static function getFilteredUpcomingCompetitions($season, $weapon = '', $gender = '', $ageCategory = '') {
        $db = Database::getInstance();
        $query = "SELECT competitionId, name, startDate, location, country, category, weapon, gender, ageCategory FROM competitions WHERE season = ? AND startDate > CURDATE()";
        $params = [$season];
        $types = "i";

        if ($weapon) { $query .= " AND weapon = ?"; $params[] = $weapon; $types .= "s"; }
        if ($gender) { $query .= " AND gender = ?"; $params[] = $gender; $types .= "s"; }
        if ($ageCategory) { $query .= " AND ageCategory = ?"; $params[] = $ageCategory; $types .= "s"; }

        $query .= " ORDER BY startDate ASC";
        return $db->fetchAll($query, $types, $params);
    }
    
    public static function getRecentlyCompletedCompetitions($daysBack = 7) {
        $db = Database::getInstance();
        $query = "
            SELECT competitionId, season, endDate 
            FROM competitions 
            WHERE endDate BETWEEN DATE_SUB(CURDATE(), INTERVAL ? DAY) AND DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        ";
        return $db->fetchAll($query, "i", [$daysBack]);
    }
}
?>