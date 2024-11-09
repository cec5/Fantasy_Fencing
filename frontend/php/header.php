<!-- header.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Fantasy Fencing Project</title>
    <!-- Bootstrap CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
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
                <ul class="navbar-nav me-auto">
                    <!-- Dropdown for Athletes -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="athleteDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">View Athletes</a>
                        <ul class="dropdown-menu" aria-labelledby="athleteDropdown">
                            <li><a class="dropdown-item" href="searchAthletes.php">Search Athletes</a></li>
                            <li><a class="dropdown-item" href="leaders.php">Points Leaders</a></li>
                        </ul>
                    </li>
                    <!-- Link to Competitions -->
                    <li class="nav-item">
                        <a class="nav-link" href="competitions.php">Competitions</a>
                    </li>
                </ul>
                <!-- Profile dropdown on the right side -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Account</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <?php if (isset($_COOKIE['auth_token'])): ?>
                                <!-- User is logged in, show My Profile and Logout options -->
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="#" onclick="logout()">Logout</a></li>
                            <?php else: ?>
                                <!-- User is not logged in, show Register and Login options -->
                                <li><a class="dropdown-item" href="register.php">Register</a></li>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Logout function to clear the JWT cookie and redirect to the homepage
        function logout() {
        	// Set the cookie's expiration date to a past date
        	document.cookie = "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        	window.location.href = 'index.php'; // Redirect to the homepage
    	}
    </script>
</body>
</html>
