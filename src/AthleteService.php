<?php
require_once('dbConnect.php');

class AthleteService {
    public static function updateAthlete($data) {
        $db = dbConnect();
        $stmt = $db->prepare("
            INSERT INTO athletes (id, name, firstName, lastName, gender, nationality, weapon, weapon2)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name), firstName = VALUES(firstName), lastName = VALUES(lastName),
                gender = VALUES(gender), nationality = VALUES(nationality), weapon = VALUES(weapon), weapon2 = VALUES(weapon2)
        ");
        $stmt->bind_param("isssssss", $data['id'], $data['name'], $data['firstName'], $data['lastName'], $data['gender'], $data['nationality'], $data['weapon'], $data['weapon2']);
        
        if ($stmt->execute()) echo "Data stored successfully for Athlete ID: [{$data['id']}]\n";
        else echo "Error storing athlete data: " . $stmt->error;
        
        $stmt->close();
        $db->close();
    }

    public static function athleteExists($athleteId) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT 1 FROM athletes WHERE id = ?");
        $stmt->bind_param("i", $athleteId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        $db->close();
        return $exists;
    }

    public static function searchAthletes($name = '', $gender = '', $weapon = '', $country = '') {
        $db = dbConnect();
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

        $stmt = $db->prepare($query);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $athletes = [];
        while ($row = $result->fetch_assoc()) $athletes[] = $row;
        
        $stmt->close();
        $db->close();
        return $athletes;
    }

    public static function getAthleteInfo($athleteId) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT firstName, lastName, gender, nationality FROM athletes WHERE id = ?");
        $stmt->bind_param("i", $athleteId);
        $stmt->execute();
        $athlete = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();
        return $athlete;
    }

    public static function getAthleteWeapons($athleteId) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT weapon, weapon2 FROM athletes WHERE id = ?");
        $stmt->bind_param("i", $athleteId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $weapons = array_filter([$result['weapon'], $result['weapon2']]);
        $stmt->close();
        $db->close();
        return array_values($weapons);
    }

    public static function getAthleteAgeCategories($athleteId, $season, $weapon) {
        $db = dbConnect();
        $query = "
            SELECT DISTINCT c.ageCategory FROM competitionResults cr
            JOIN competitions c ON cr.competitionId = c.competitionId AND cr.season = c.season
            WHERE cr.athleteId = ? AND cr.season = ? AND c.weapon = ?
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param("iis", $athleteId, $season, $weapon);
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) $categories[] = $row['ageCategory'];
        
        $stmt->close();
        $db->close();
        return $categories;
    }
}
?>