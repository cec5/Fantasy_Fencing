<?php // This file contains function relating to user registration, login, and authentication

require_once('dbConnect.php');
require_once('dataArrays.php');
require_once('../../backend/vendor/autoload.php');
use \Firebase\JWT\JWT;

function registerUser($username, $email, $nationality, $password) {
    	$db = dbConnect();
    
    	// Check if username or email already exists
    	$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    	$stmt->bind_param("s", $username);
    	$stmt->execute();
    	$stmt->store_result();

    	if ($stmt->num_rows > 0) {
        	$stmt->close();
        	$db->close();
        	return "Username is already taken.";
    	}

    	// Check for valid nationality code
    	global $validCountryCodes;
    	if (!array_key_exists($nationality, $validCountryCodes)) {
        	return "Invalid Country";
    	}

    	// Check password length
    	if (strlen($password) < 8) {
        	return "Password must be at least 8 characters long.";
    	}

    	// Hash the password for storage
    	$passwordHash = password_hash($password, PASSWORD_BCRYPT);

    	// Insert new user into the database
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

function loginUser($username, $password) {
    	$db = dbConnect();

    	// Prepare the SQL statement
    	$stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");
    	$stmt->bind_param("s", $username);
    	$stmt->execute();
    	$result = $stmt->get_result();
    
    	// Check if the username exists
    	if ($result->num_rows === 1) {
        	$user = $result->fetch_assoc();
        	$userId = $user['id'];
        	$hashedPassword = $user['password'];
        
        	// Verify the password
        	if (password_verify($password, $hashedPassword)) {
            		$stmt->close();
            		$db->close();
            		return $userId;  // Return the user ID if authentication is successful
        	}
    	}
    	$stmt->close();
    	$db->close();
    	return false;  // Return false if authentication fails
}

// Session Validation and Authentication (JWT Tokens)
function generateJWT($userId) {
    	$key = 'Fantasy_Fencing';
    	$payload = [
        	'iss' => 'Fantasy_Fencing',
        	'sub' => $userId,
        	'iat' => time(),
        	'exp' => time() + (60 * 60) // 1-hour expiration
    	];
    	return JWT::encode($payload, $key, 'HS256');
}

function validateToken($token) {
    	try {
        	$decoded = JWT::decode($token, new Key('Fantasy_Fencing', 'HS256'));
        	return ["success" => true, "message" => "Token is valid.", "userId" => $decoded->sub];
    	} catch (Exception $e) {
        	return ["success" => false, "message" => "Invalid or expired token."];
    	}
}
?>
