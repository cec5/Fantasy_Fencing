<?php 
require_once(dirname(__DIR__).'/../backend/php/userFunctions.php');
require_once(dirname(__DIR__).'/../backend/vendor/autoload.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    	$username = $_POST['username'];
    	$password = $_POST['password'];

    	$userId = loginUser($username, $password);

    	if ($userId) {
        	// Generate JWT if authentication is successful
        	$token = generateJWT($userId);
        
        	// Set JWT as a cookie
        	setcookie("auth_token", $token, time() + (60 * 60), "/", "", false, false);
        
        	echo "<script> alert('Login Successful!'); window.location.href = '/index.php'; </script>";
    	} else {
        	echo "<div class='alert alert-danger'>Invalid Username or Password</div>";
    	}
}
include(dirname(__DIR__).'/common/header.php');
?>

<main class="container my-5">
        <h2>Login</h2>
        <form action="account/login.php" method="POST">
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
</main>
<?php include(dirname(__DIR__).'/common/footer.php');?>
