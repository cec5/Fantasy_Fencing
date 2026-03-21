<?php
require_once(dirname(__DIR__) . '/../src/AuthService.php');
require_once(dirname(__DIR__) . '/../vendor/autoload.php');

if (!isset($_COOKIE['auth_token'])) {
	// Redirect to login if token is not present
	echo "<script> alert('Please Login to Access this Page'); window.location.href = '/account/login.php';</script>";
	exit();
}

// Validate the token
$token = $_COOKIE['auth_token'];
$validationResult = AuthService::validateToken($token);

if (!$validationResult['success']) {
	// If token is invalid or expired, redirect to login
	echo "<script> alert('Invalid or expired token, please log in again'); window.location.href = '/account/login.php';</script>";
	exit();
}

// Extract user ID from the valid token for use in the restricted page
$userId = $validationResult['userId'];
?>