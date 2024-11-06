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
            </div>
        </div>
    </nav>
    <!-- Bootstrap JS -->
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
