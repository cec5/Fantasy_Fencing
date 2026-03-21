<?php
require_once(dirname(__DIR__) . '/src/database/Database.php');

class AthleteService {
    public static function updateAthlete($data) {
        $db = Database::getInstance();
        $query = "
            INSERT INTO athletes (id, name, firstName, lastName, gender, nationality, weapon, weapon2)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name), firstName = VALUES(firstName), lastName = VALUES(lastName),
                gender = VALUES(gender), nationality = VALUES(nationality), weapon = VALUES(weapon), weapon2 = VALUES(weapon2)
        ";
        
        try {
            $db->execute($query, "isssssss", [
                $data['id'], $data['name'], $data['firstName'], $data['lastName'], 
                $data['gender'], $data['nationality'], $data['weapon'], $data['weapon2']
            ]);
            echo "Data stored successfully for Athlete ID: [{$data['id']}]\n";
        } catch (Exception $e) {
            echo "Error storing athlete data: " . $e->getMessage();
        }
    }

    public static function athleteExists($athleteId) {
        $db = Database::getInstance();
        return (bool) $db->fetchColumn("SELECT 1 FROM athletes WHERE id = ?", "i", [$athleteId]);
    }

    public static function searchAthletes($name = '', $gender = '', $weapon = '', $country = '') {
        $db = Database::getInstance();
        $query = "SELECT id, name, gender, weapon, weapon2, nationality FROM athletes WHERE 1=1";
        $params = [];
        $types = '';

        if ($name) {
            $query .= " AND (name LIKE ? OR CONCAT(firstName, ' ', lastName) LIKE ? OR firstName LIKE ? OR lastName LIKE ?)";
            $searchTerm = '%' . $name . '%';
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $types .= 'ssss';
        }
        if ($gender) {
            $query .= " AND gender = ?";
            $params[] = $gender; $types .= 's';
        }
        if ($weapon) {
            $query .= " AND (weapon = ? OR weapon2 = ?)";
            array_push($params, $weapon, $weapon); $types .= 'ss';
        }
        if ($country) {
            $query .= " AND nationality = ?";
            $params[] = strtoupper($country); $types .= 's';
        }

        return $db->fetchAll($query, $types, $params);
    }

    public static function getAthleteInfo($athleteId) {
        $db = Database::getInstance();
        return $db->fetchOne("SELECT firstName, lastName, gender, nationality FROM athletes WHERE id = ?", "i", [$athleteId]);
    }

    public static function getAthleteWeapons($athleteId) {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT weapon, weapon2 FROM athletes WHERE id = ?", "i", [$athleteId]);
        return $result ? array_values(array_filter([$result['weapon'], $result['weapon2']])) : [];
    }

    public static function getAthleteAgeCategories($athleteId, $season, $weapon) {
        $db = Database::getInstance();
        $query = "
            SELECT DISTINCT c.ageCategory FROM competitionResults cr
            JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE cr.athleteId = ? AND cr.season = ? AND c.weapon = ?
        ";
        $results = $db->fetchAll($query, "iis", [$athleteId, $season, $weapon]);
        return array_column($results, 'ageCategory');
    }
}
?>