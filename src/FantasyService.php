<?php
require_once('dbConnect.php');

class FantasyService {
    public static function isCompetitionLocked($season, $competitionId) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT startDate FROM competitions WHERE competitionId = ? AND season = ?");
        $stmt->bind_param("ii", $competitionId, $season);
        $stmt->execute();
        $stmt->bind_result($startDate);
        $stmt->fetch();
        $stmt->close();
        $db->close();
        return strtotime($startDate) <= strtotime('+1 day');
    }

    public static function getFantasyLeaderboard($season, $weapon, $gender, $ageCategory) {
        $db = dbConnect();
        $query = "
            SELECT u.id, u.username, u.nationality, ftp.totalPoints 
            FROM fantasyTotalPoints ftp JOIN users u ON ftp.userId = u.id
            WHERE ftp.season = ? AND ftp.weapon = ? AND ftp.gender = ? AND ftp.ageCategory = ? ORDER BY ftp.totalPoints DESC, u.username ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("isss", $season, $weapon, $gender, $ageCategory);
        $stmt->execute();
        $result = $stmt->get_result();

        $leaderboard = [];
        while ($row = $result->fetch_assoc()) $leaderboard[] = $row;
        
        $stmt->close();
        $db->close();
        return $leaderboard;
    }

    public static function getUserSelections($userId, $season, $competitionId) {
        $db = dbConnect();
        $query = "
            SELECT us.athleteId, a.name, a.nationality 
            FROM userSelections us JOIN athletes a ON us.athleteId = a.id
            WHERE us.userId = ? AND us.season = ? AND us.competitionId = ?
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("iii", $userId, $season, $competitionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $selections = [];
        while ($row = $result->fetch_assoc()) $selections[] = $row;
        
        $stmt->close();
        $db->close();
        return $selections;
    }

    public static function calculateFantasyPointsForSeason($season) {
        $db = dbConnect();
        $query = "
            INSERT INTO fantasyTotalPoints (userId, season, weapon, gender, ageCategory, totalPoints)
            SELECT us.userId, c.season, c.weapon, c.gender, c.ageCategory, COALESCE(SUM(cr.points), 0)
            FROM userSelections us
            JOIN competitionResults cr ON us.athleteId = cr.athleteId AND us.competitionId = cr.competitionId AND us.season = cr.season
            JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE us.season = ? GROUP BY us.userId, c.season, c.weapon, c.gender, c.ageCategory
            ON DUPLICATE KEY UPDATE totalPoints = VALUES(totalPoints)
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $season);
        $stmt->execute();
        $stmt->close();
        $db->close();
    }

    public static function updateUserSelection($userId, $season, $competitionId, $athleteId, $action) {
        if (self::isCompetitionLocked($season, $competitionId)) {
            return ["success" => false, "message" => "Athlete selections for this competition is locked"];
        }

        $db = dbConnect();
        if ($action === 'add') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM userSelections WHERE userId = ? AND season = ? AND competitionId = ? AND athleteId = ?");
            $stmt->bind_param("iiii", $userId, $season, $competitionId, $athleteId);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) { $db->close(); return ["success" => false, "message" => "Athlete is already selected"]; }

            $stmt = $db->prepare("SELECT COUNT(*) FROM userSelections WHERE userId = ? AND season = ? AND competitionId = ?");
            $stmt->bind_param("iii", $userId, $season, $competitionId);
            $stmt->execute();
            $stmt->bind_result($selectionCount);
            $stmt->fetch();
            $stmt->close();

            if ($selectionCount >= 5) { $db->close(); return ["success" => false, "message" => "You can only select up to 5 Athletes"]; }

            $stmt = $db->prepare("INSERT INTO userSelections (userId, season, competitionId, athleteId) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $userId, $season, $competitionId, $athleteId);
            $stmt->execute();
            $stmt->close();
            $db->close();
            return ["success" => true, "message" => "Athlete added!"];
        } elseif ($action === 'remove') {
            $stmt = $db->prepare("DELETE FROM userSelections WHERE userId = ? AND season = ? AND competitionId = ? AND athleteId = ?");
            $stmt->bind_param("iiii", $userId, $season, $competitionId, $athleteId);
            $stmt->execute();
            $stmt->close();
            $db->close();
            return ["success" => true, "message" => "Athlete dropped!"];
        }
        $db->close();
        return ["success" => false, "message" => "ERROR; Invalid Action"];
    }

    public static function getUserCompetitions($userId, $season, $isPast, $weapon = '', $gender = '', $ageCategory = '') {
        $db = dbConnect();
        $query = "SELECT DISTINCT c.* FROM competitions c JOIN userSelections us ON c.competitionId = us.competitionId AND c.season = us.season WHERE us.userId = ? AND c.season = ?";
        $params = [$userId, $season];
        $types = "ii";

        if ($weapon) { $query .= " AND c.weapon = ?"; $params[] = $weapon; $types .= "s"; }
        if ($gender) { $query .= " AND c.gender = ?"; $params[] = $gender; $types .= "s"; }
        if ($ageCategory) { $query .= " AND c.ageCategory = ?"; $params[] = $ageCategory; $types .= "s"; }

        $query .= $isPast ? " AND c.startDate <= CURDATE()" : " AND c.startDate > CURDATE()";
        $query .= " ORDER BY c.startDate ASC";

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
}
?>