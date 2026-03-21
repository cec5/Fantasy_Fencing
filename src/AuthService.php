<?php
require_once(dirname(__DIR__) . '/src/database/Database.php');
require_once(dirname(__DIR__) . '/src/dataArrays.php');
require_once(dirname(__DIR__) . '/vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthService {
    public static function registerUser($username, $email, $nationality, $password) {
        $db = Database::getInstance();
        
        if ($db->fetchColumn("SELECT id FROM users WHERE username = ?", "s", [$username])) {
            return "Username is already taken.";
        }

        global $validCountryCodes;
        if (!array_key_exists($nationality, $validCountryCodes)) {
            return "Invalid Country";
        }

        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $db->execute("INSERT INTO users (username, email, nationality, password) VALUES (?, ?, ?, ?)", "ssss", [$username, $email, $nationality, $passwordHash]);
            return true;
        } catch (Exception $e) {
            return "Error registering user: " . $e->getMessage();
        }
    }

    public static function loginUser($username, $password) {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT id, password FROM users WHERE username = ?", "s", [$username]);

        if ($user && password_verify($password, $user['password'])) {
            return $user['id'];
        }
        return false;
    }

    public static function generateJWT($userId) {
        $key = 'Fantasy_Fencing';
        $payload = [
            'iss' => 'Fantasy_Fencing',
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + (60 * 60)
        ];
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key('Fantasy_Fencing', 'HS256'));
            return ["success" => true, "message" => "Token is valid.", "userId" => $decoded->sub];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Invalid or expired token."];
        }
    }

    public static function getUserInfo($userId) {
        $db = Database::getInstance();
        return $db->fetchOne("SELECT username, email, nationality FROM users WHERE id = ?", "i", [$userId]);
    }

    public static function updateUsername($userId, $newUsername) {
        $db = Database::getInstance();
        if ($db->fetchColumn("SELECT id FROM users WHERE username = ? AND id != ?", "si", [$newUsername, $userId])) {
            return "Username is already taken.";
        }

        $success = $db->execute("UPDATE users SET username = ? WHERE id = ?", "si", [$newUsername, $userId]);
        return $success ? true : "Error updating username.";
    }

    public static function updateEmail($userId, $newEmail) {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) return "Invalid email format.";

        $db = Database::getInstance();
        $success = $db->execute("UPDATE users SET email = ? WHERE id = ?", "si", [$newEmail, $userId]);
        return $success ? true : "Error updating email.";
    }

    public static function updateNationality($userId, $newNationality) {
        global $validCountryCodes;
        if (!array_key_exists($newNationality, $validCountryCodes)) return "Invalid nationality code.";

        $db = Database::getInstance();
        $success = $db->execute("UPDATE users SET nationality = ? WHERE id = ?", "si", [$newNationality, $userId]);
        return $success ? true : "Error updating nationality.";
    }

    public static function updateUserPassword($userId, $currentPassword, $newPassword) {
        $db = Database::getInstance();
        $currentHash = $db->fetchColumn("SELECT password FROM users WHERE id = ?", "i", [$userId]);

        if (!$currentHash || !password_verify($currentPassword, $currentHash)) {
            return "Current password is incorrect.";
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $success = $db->execute("UPDATE users SET password = ? WHERE id = ?", "si", [$newPasswordHash, $userId]);
        return $success ? true : "Error updating password.";
    }

    public static function isAdmin($userId) {
        $db = Database::getInstance();
        return (bool) $db->fetchColumn("SELECT isAdmin FROM users WHERE id = ?", "i", [$userId]);
    }
}
?>