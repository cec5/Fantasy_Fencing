<?php
require_once(dirname(__DIR__) . '/src/database/Database.php');

class FantasyService {
    public static function isCompetitionLocked($season, $competitionId) {
        $db = Database::getInstance();
        $startDate = $db->fetchColumn("SELECT startDate FROM competitions WHERE competitionId = ? AND season = ?", "ii", [$competitionId, $season]);
        return $startDate ? strtotime($startDate) <= strtotime('+1 day') : false;
    }

    public static function getFantasyLeaderboard($season, $weapon, $gender, $ageCategory) {
        $db = Database::getInstance();
        $query = "
            SELECT u.id, u.username, u.nationality, ftp.totalPoints 
            FROM fantasyTotalPoints ftp JOIN users u ON ftp.userId = u.id
            WHERE ftp.season = ? AND ftp.weapon = ? AND ftp.gender = ? AND ftp.ageCategory = ? ORDER BY ftp.totalPoints DESC, u.username ASC
        ";
        return $db->fetchAll($query, "isss", [$season, $weapon, $gender, $ageCategory]);
    }

    public static function getUserSelections($userId, $season, $competitionId) {
        $db = Database::getInstance();
        $query = "
            SELECT us.athleteId, a.name, a.nationality 
            FROM userSelections us JOIN athletes a ON us.athleteId = a.id
            WHERE us.userId = ? AND us.season = ? AND us.competitionId = ?
        ";
        return $db->fetchAll($query, "iii", [$userId, $season, $competitionId]);
    }

    public static function calculateFantasyPointsForSeason($season) {
        $db = Database::getInstance();
        $query = "
            INSERT INTO fantasyTotalPoints (userId, season, weapon, gender, ageCategory, totalPoints)
            SELECT us.userId, c.season, c.weapon, c.gender, c.ageCategory, COALESCE(SUM(cr.points), 0)
            FROM userSelections us
            JOIN competitionResults cr ON us.athleteId = cr.athleteId AND us.competitionId = cr.competitionId AND us.season = cr.season
            JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE us.season = ? GROUP BY us.userId, c.season, c.weapon, c.gender, c.ageCategory
            ON DUPLICATE KEY UPDATE totalPoints = VALUES(totalPoints)
        ";
        $db->execute($query, "i", [$season]);
    }

    public static function updateUserSelection($userId, $season, $competitionId, $athleteId, $action) {
        if (self::isCompetitionLocked($season, $competitionId)) {
            return ["success" => false, "message" => "Athlete selections for this competition is locked"];
        }

        $db = Database::getInstance();

        if ($action === 'add') {
            $isAlreadySelected = $db->fetchColumn("SELECT COUNT(*) FROM userSelections WHERE userId = ? AND season = ? AND competitionId = ? AND athleteId = ?", "iiii", [$userId, $season, $competitionId, $athleteId]);
            if ($isAlreadySelected > 0) return ["success" => false, "message" => "Athlete is already selected"];

            $selectionCount = $db->fetchColumn("SELECT COUNT(*) FROM userSelections WHERE userId = ? AND season = ? AND competitionId = ?", "iii", [$userId, $season, $competitionId]);
            if ($selectionCount >= 5) return ["success" => false, "message" => "You can only select up to 5 Athletes"];

            $success = $db->execute("INSERT INTO userSelections (userId, season, competitionId, athleteId) VALUES (?, ?, ?, ?)", "iiii", [$userId, $season, $competitionId, $athleteId]);
            return $success ? ["success" => true, "message" => "Athlete added!"] : ["success" => false, "message" => "Database error"];
            
        } elseif ($action === 'remove') {
            $success = $db->execute("DELETE FROM userSelections WHERE userId = ? AND season = ? AND competitionId = ? AND athleteId = ?", "iiii", [$userId, $season, $competitionId, $athleteId]);
            return $success ? ["success" => true, "message" => "Athlete dropped!"] : ["success" => false, "message" => "Database error"];
        }
        
        return ["success" => false, "message" => "ERROR; Invalid Action"];
    }

    public static function getUserCompetitions($userId, $season, $isPast, $weapon = '', $gender = '', $ageCategory = '') {
        $db = Database::getInstance();
        $query = "SELECT DISTINCT c.* FROM competitions c JOIN userSelections us ON c.competitionId = us.competitionId AND c.season = us.season WHERE us.userId = ? AND c.season = ?";
        $params = [$userId, $season];
        $types = "ii";

        if ($weapon) { $query .= " AND c.weapon = ?"; $params[] = $weapon; $types .= "s"; }
        if ($gender) { $query .= " AND c.gender = ?"; $params[] = $gender; $types .= "s"; }
        if ($ageCategory) { $query .= " AND c.ageCategory = ?"; $params[] = $ageCategory; $types .= "s"; }

        $query .= $isPast ? " AND c.startDate <= CURDATE()" : " AND c.startDate > CURDATE()";
        $query .= " ORDER BY c.startDate ASC";

        return $db->fetchAll($query, $types, $params);
    }
}
?>