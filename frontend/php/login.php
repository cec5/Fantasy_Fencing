<?php
include 'header.php';
include '../../backend/php/userFunctions.php';
require_once '../../backend/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    	$username = $_POST['username'];
    	$password = $_POST['password'];

    	$userId = loginUser($username, $password);

    	if ($userId) {
        	// Generate JWT if authentication is successful
        	$token = generateJWT($userId);
        
        	// Set JWT as a cookie
        	setcookie("auth_token", $token, time() + (60 * 60), "/", "", false, false);
        
        	echo "<script> alert('Login Successful!'); window.location.href = 'index.php'; </script>";
    	} else {
        	echo "<div class='alert alert-danger'>Invalid Username or Password</div>";
    	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    	<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<title>Login</title>
</head>
<body>
    	<div class="container mt-5">
        	<h2>Login</h2>
        	<form action="login.php" method="POST">
            		<div class="mb-3">
                		<label for="username" class="form-label">Username</label>
                		<input type="text" class="form-control" id="username" name="username" required>
            		</div>
            		<div class="mb-3">
                		<label for="password" class="form-label">Password</label>
                		<input type="password" class="form-control" id="password" name="password" required>
            		</div>
            		<button type="submit" class="btn btn-primary">Login</button>
        	</form>
    	</div>
</body>
</html>
