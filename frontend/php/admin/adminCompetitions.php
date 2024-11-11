<?php
require_once('adminHeader.php');
require_once('adminValidation.php');
require_once('../../../backend/php/scraper/scraperFunctions.php');
require_once('../../../backend/php/databaseFunctions.php');

$message = '';
$success = false;

// Handle update athlete points
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatePointsSubmit'])) {
    	$season = $_POST['season'];
    	try {
        	updateAthleteSeasonPoints($season);
        	$message = "Successfully updated athlete points for Season: [$season]";
        	$success = true;
    	} catch (Exception $e) {
        	$message = "Error updating athlete points: " . $e->getMessage();
        	$success = false;
    	}
}

// Handle add competition data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addCompetitionDataSubmit'])) {
    	$season = $_POST['compSeason'];
    	$compID = $_POST['compID'];
    	$result = scrapeCompetitionData($season, $compID);

    	if (!$result) {
        	$message = "Unable to add competition data. No competition found in Season: [$season] with ID: [$compID]";
        	$success = false;
    	} else {
        	updateCompetition($result);
        	$message = "Competition data for ID: [$compID] in Season: [$season] successfully added/updated";
        	$success = true;
    	}
}

// Handle update competition results
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateCompetitionResultsSubmit'])) {
    	$season = $_POST['resultSeason'];
    	$compID = $_POST['resultCompID'];
    	$result = scrapeCompetitionResults($season, $compID);

    	if (!$result) {
        	$message = "Unable to add competition results. No competition found in Season: [$season] with ID: [$compID]";
        	$success = false;
    	} else {
        	updateCompetitionResults($result);
        	$message = "Competition results for ID: [$compID] in Season: [$season] successfully updated";
        	$success = true;
    	}
}

// Handle add competition athletes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addCompetitionAthletesSubmit'])) {
    	$season = $_POST['athletesSeason'];
    	$compID = $_POST['athletesCompID'];
    	$athletes = scrapeAthletesCompetition($season, $compID);

    	if (!$athletes) {
        	$message = "No athletes found or competition not finished for Season: [$season] with ID: [$compID].";
        	$success = false;
    	} else {
        	foreach ($athletes as $athleteID) {
            		$athleteData = scrapeAthleteData($athleteID);
            		if ($athleteData) {
                		try {
                    			updateAthlete($athleteData);
                		} catch (Exception $e) {
                    			$message .= "Failed to insert/update Athlete ID: [$athleteID]: " . $e->getMessage() . "\n";
                }
            		} else {
                		$message .= "Skipping athlete ID $athleteID due to missing or incomplete data.\n";
            		}
        	}
        	$message .= "Athletes from competition ID: [$compID] in Season: [$season] successfully added/updated.";
        	$success = true;
    	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    	<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<title>Admin - Manage Competitions</title>
</head>
<body>
<div class="container mt-5">
    	<h2>Admin - Manage Competitions</h2>

    	<!-- Display result message -->
    	<?php if ($message): ?>
        	<div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
            		<?= nl2br(htmlspecialchars($message)) ?>
        	</div>
    	<?php endif; ?>

    	<!-- Update Athlete Points -->
    	<h3 class="mt-4">Update Athlete Points</h3>
    	<form method="POST" action="adminCompetitions.php" class="mb-4">
		<div class="mb-3">
		    	<label for="season" class="form-label">Season:</label>
		    	<input type="number" class="form-control" id="season" name="season" required>
		</div>
		<button type="submit" class="btn btn-primary" name="updatePointsSubmit">Update Points</button>
    	</form>

    	<!-- Add Competition Data -->
    	<h3>Add Competition Data</h3>
    	<form method="POST" action="adminCompetitions.php" class="mb-4">
        	<div class="mb-3">
            		<label for="compSeason" class="form-label">Season:</label>
            		<input type="number" class="form-control" id="compSeason" name="compSeason" required>
        	</div>
        	<div class="mb-3">
            		<label for="compID" class="form-label">Competition ID:</label>
            		<input type="number" class="form-control" id="compID" name="compID" required>
        	</div>
        	<button type="submit" class="btn btn-primary" name="addCompetitionDataSubmit">Add Competition Data</button>
    	</form>

    	<!-- Update Competition Results -->
    	<h3>Update Competition Results</h3>
    	<form method="POST" action="adminCompetitions.php" class="mb-4">
        	<div class="mb-3">
            		<label for="resultSeason" class="form-label">Season:</label>
            		<input type="number" class="form-control" id="resultSeason" name="resultSeason" required>
        	</div>
        	<div class="mb-3">
            		<label for="resultCompID" class="form-label">Competition ID:</label>
            		<input type="number" class="form-control" id="resultCompID" name="resultCompID" required>
        	</div>
        	<button type="submit" class="btn btn-primary" name="updateCompetitionResultsSubmit">Update Competition Results</button>
    	</form>

    	<!-- Add Competition Athletes -->
    	<h3>Add Competition Athletes</h3>
    	<form method="POST" action="adminCompetitions.php">
        	<div class="mb-3">
            		<label for="athletesSeason" class="form-label">Season:</label>
            		<input type="number" class="form-control" id="athletesSeason" name="athletesSeason" required>
        	</div>
       		<div class="mb-3">
            		<label for="athletesCompID" class="form-label">Competition ID:</label>
            		<input type="number" class="form-control" id="athletesCompID" name="athletesCompID" required>
        	</div>
        	<button type="submit" class="btn btn-primary" name="addCompetitionAthletesSubmit">Add Competition Athletes</button>
    	</form>
</div>
</body>
</html>
