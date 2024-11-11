<?php
require_once('../../backend/php/userFunctions.php');
require_once('../../backend/vendor/autoload.php');

if (!isset($_COOKIE['auth_token'])) {
    	// Redirect to login if token is not present
    	echo "<script> alert('You must be logged in to access this page'); window.location.href = 'login.php';</script>";
    	exit();
}

// Validate the token
$token = $_COOKIE['auth_token'];
$validationResult = validateToken($token);

if (!$validationResult['success']) {
    	// If token is invalid or expired, redirect to login
    	echo "<script> alert('Invalid or expired token, please log in again'); window.location.href = 'login.php';</script>";
    	exit();
}

// Extract user ID from the valid token for use in the restricted page
$userId = $validationResult['userId'];
?>
