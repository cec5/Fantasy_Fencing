<?php
// Fetch user ID and check admin status if logged in
$userIsAdmin = false;
require_once(dirname(__DIR__).'/../backend/php/userFunctions.php');
if (isset($_COOKIE['auth_token'])) {
    	$validationResult = validateToken($_COOKIE['auth_token']);
    	if ($validationResult['success']) {
        	$userId = $validationResult['userId'];
        	$userIsAdmin = isAdmin($userId); // Check if the user is an admin
    	}
}
?>
<!-- header.php -->
<html lang="en">
<head>
    	<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<title>The Fantasy Fencing Project</title>
    	<base href="/">
    	<!-- Bootstrap CSS -->
    	<link href="common/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    	<!-- Navbar -->
    	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        	<div class="container">
            		<a class="navbar-brand" href="index.php">Fantasy Fencing</a>
            		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                		<span class="navbar-toggler-icon"></span>
            		</button>
            	<div class="collapse navbar-collapse" id="navbarNav">
                	<!-- Main navigation items on the left -->
                	<ul class="navbar-nav me-auto">
                    		<!-- Athlete Dropdown -->
                    		<li class="nav-item dropdown">
                        		<a class="nav-link dropdown-toggle" href="#" id="athleteDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">View Athletes</a>
                        		<ul class="dropdown-menu" aria-labelledby="athleteDropdown">
                            			<li><a class="dropdown-item" href="a/search.php">Search Athletes</a></li>
                            			<li><a class="dropdown-item" href="a/leaders.php">Points Leaders</a></li>
                        		</ul>
                    		</li>
                    		<!-- Competitions Link -->
                    		<li class="nav-item"><a class="nav-link" href="c/search.php">Competitions</a></li>
                    		<!-- Fantasy Dropdown -->
                    		<li class="nav-item dropdown">
                    			<a class="nav-link dropdown-toggle" href="#" id="fantasyDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Fantasy</a>
                    			<ul class="dropdown-menu" aria-labelledby="fantasyDropdown">
                            			<li><a class="dropdown-item" href="fantasy/leaderboard.php">Leaderboard</a></li>
                           			 <li><a class="dropdown-item" href="fantasy/competitions.php">Draft Athletes</a></li>
                           			 <?php if (isset($_COOKIE['auth_token'])): ?><li><a class="dropdown-item" href="fantasy/manage.php">Manage Athletes</a></li><?php endif; ?>
                        		</ul>
                    		</li>
                	</ul>

                	<!-- Admin Dropdown (visible to admins only) and Profile section on the right -->
                	<ul class="navbar-nav ms-auto">
                    		<?php if ($userIsAdmin): ?>
                        		<li class="nav-item dropdown">
                            			<a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                            			<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                			<li><a class="dropdown-item" href="admin/adminAthletes.php">Manage Athletes</a></li>
                                			<li><a class="dropdown-item" href="admin/adminCompetitions.php">Manage Competitions</a></li>
                            			</ul>
                        		</li>
                    		<?php endif; ?>

                    		<!-- Profile Dropdown -->
                    		<li class="nav-item dropdown">
                        		<a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Account</a>
                        		<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            			<?php if (isset($_COOKIE['auth_token'])): ?>
                                			<!-- User is logged in, show My Profile and Logout options -->
                                			<li><a class="dropdown-item" href="account/profile.php">My Profile</a></li>
                                			<li><a class="dropdown-item" href="#" onclick="logout()">Logout</a></li>
                            			<?php else: ?>
		                        		<!-- User is not logged in, show Register and Login options -->
		                        		<li><a class="dropdown-item" href="account/login.php">Login</a></li>
		                        		<li><a class="dropdown-item" href="account/register.php">Register</a></li>
                           			 <?php endif; ?>
                        		</ul>
                   		</li>
                	</ul>
            	</div>
        	</div>
	</nav>
	<!-- Footer
	<footer class="fixed fixed-bottom container">
    		<div class="bg-dark text-white text-center py-2 mt-2">
        		<small>
            			&copy; <?php echo date("Y"); ?> Fantasy Fencing Project | 
            			<a href="/common/about.php" class="text-white text-decoration-underline">About</a> |
            			<a href="/common/tutorial.php" class="text-white text-decoration-underline">Tutorial</a> |
            			<a href="/common/privacy_policy.php" class="text-white text-decoration-underline">Privacy Policy</a>
       			 </small>
    		</div>
	</footer> -->

    	<script src="common/js/bootstrap.bundle.min.js"></script>
    	<script>
        	// Logout function to clear the JWT cookie and redirect to the homepage
        	function logout() {
            		document.cookie = "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            		window.location.href = 'index.php';
        	}
    	</script>
</body>
</html>
