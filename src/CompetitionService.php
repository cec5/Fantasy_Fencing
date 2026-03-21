<?php
require_once('dbConnect.php');

class CompetitionService {
    public static function updateCompetition($data) {
        $db = dbConnect();
        $stmt = $db->prepare("
            INSERT INTO competitions (competitionId, season, name, category, weapon, gender, country, location, startDate, endDate, ageCategory)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name), category = VALUES(category), weapon = VALUES(weapon), gender = VALUES(gender),
                country = VALUES(country), location = VALUES(location), startDate = VALUES(startDate), endDate = VALUES(endDate), ageCategory = VALUES(ageCategory)
        ");
        $stmt->bind_param("iisssssssss", $data['competitionId'], $data['season'], $data['name'], $data['category'], $data['weapon'], $data['gender'], $data['country'], $data['location'], $data['startDate'], $data['endDate'], $data['ageCategory']);
        
        if ($stmt->execute()) echo "Data stored successfully for Competition: [{$data['competitionId']}] of Season: [{$data['season']}]\n";
        else echo "Error storing competition data: " . $stmt->error;
        
        $stmt->close();
        $db->close();
    }

    public static function getCompetitions($season, $weapon, $gender, $ageCategory) {
        $db = dbConnect();
        $query = "SELECT competitionId, season, name, category, location, country, startDate FROM competitions WHERE season = ? AND weapon = ? AND gender = ? AND ageCategory = ? ORDER BY startDate ASC";
        $stmt = $db->prepare($query);
        $stmt->bind_param("isss", $season, $weapon, $gender, $ageCategory);
        $stmt->execute();
        $result = $stmt->get_result();

        $competitions = [];
        while ($row = $result->fetch_assoc()) $competitions[] = $row;
        
        $stmt->close();
        $db->close();
        return $competitions;
    }

    public static function getCompetitionDetails($competitionId, $season) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT name, startDate, endDate, location, country, weapon, gender, category, ageCategory FROM competitions WHERE competitionId = ? AND season = ?");
        $stmt->bind_param("ii", $competitionId, $season);
        $stmt->execute();
        $competition = $stmt->get_result()->fetch_assoc();
        
        $stmt->close();
        $db->close();
        return $competition;
    }

    public static function getFilteredUpcomingCompetitions($season, $weapon = '', $gender = '', $ageCategory = '') {
        $db = dbConnect();
        $query = "SELECT competitionId, name, startDate, location, country, category, weapon, gender, ageCategory FROM competitions WHERE season = ? AND startDate > CURDATE()";
        $params = [$season];
        $types = "i";

        if ($weapon) { $query .= " AND weapon = ?"; $params[] = $weapon; $types .= "s"; }
        if ($gender) { $query .= " AND gender = ?"; $params[] = $gender; $types .= "s"; }
        if ($ageCategory) { $query .= " AND ageCategory = ?"; $params[] = $ageCategory; $types .= "s"; }

        $query .= " ORDER BY startDate ASC";
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $competitions = [];
        while ($row = $result->fetch_assoc()) $competitions[] = $row;
        
        $stmt->close();
        $db->close();
        return $competitions;
    }
    
    public static function getRecentlyCompletedCompetitions($daysBack = 7) {
        $db = dbConnect();
        $query = "
            SELECT competitionId, season, endDate 
            FROM competitions 
            WHERE endDate BETWEEN DATE_SUB(CURDATE(), INTERVAL ? DAY) AND DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $daysBack);
        $stmt->execute();
        $result = $stmt->get_result();

        $competitions = [];
        while ($row = $result->fetch_assoc()) $competitions[] = $row;
        
        $stmt->close();
        $db->close();
        return $competitions;
    }
}
?>