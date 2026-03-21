<?php
require_once('dbConnect.php');
require_once('dataArrays.php');
require_once(dirname(__DIR__) . '/vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthService {
    public static function registerUser($username, $email, $nationality, $password) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $db->close();
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
        $stmt = $db->prepare("INSERT INTO users (username, email, nationality, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $nationality, $passwordHash);

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return true;
        } else {
            $error = "Error registering user: " . $stmt->error;
            $stmt->close();
            $db->close();
            return $error;
        }
    }

    public static function loginUser($username, $password) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $stmt->close();
                $db->close();
                return $user['id'];
            }
        }
        $stmt->close();
        $db->close();
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
        $db = dbConnect();
        $stmt = $db->prepare("SELECT username, email, nationality FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userInfo = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        return $userInfo;
    }

    public static function updateUsername($userId, $newUsername) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $newUsername, $userId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $db->close();
            return "Username is already taken.";
        }
        $stmt->close();

        $stmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $newUsername, $userId);
        $success = $stmt->execute();
        $stmt->close();
        $db->close();
        return $success ? true : "Error updating username.";
    }

    public static function updateEmail($userId, $newEmail) {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) return "Invalid email format.";

        $db = dbConnect();
        $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $newEmail, $userId);
        $success = $stmt->execute();
        $stmt->close();
        $db->close();
        return $success ? true : "Error updating email.";
    }

    public static function updateNationality($userId, $newNationality) {
        global $validCountryCodes;
        if (!array_key_exists($newNationality, $validCountryCodes)) return "Invalid nationality code.";

        $db = dbConnect();
        $stmt = $db->prepare("UPDATE users SET nationality = ? WHERE id = ?");
        $stmt->bind_param("si", $newNationality, $userId);
        $success = $stmt->execute();
        $stmt->close();
        $db->close();
        return $success ? true : "Error updating nationality.";
    }

    public static function updateUserPassword($userId, $currentPassword, $newPassword) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!password_verify($currentPassword, $user['password'])) {
            $stmt->close();
            $db->close();
            return "Current password is incorrect.";
        }
        $stmt->close();

        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newPasswordHash, $userId);
        $success = $stmt->execute();
        $stmt->close();
        $db->close();
        return $success ? true : "Error updating password.";
    }

    public static function isAdmin($userId) {
        $db = dbConnect();
        $stmt = $db->prepare("SELECT isAdmin FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($isAdmin);
        $stmt->fetch();
        $stmt->close();
        $db->close();
        return $isAdmin == 1;
    }
}
?>