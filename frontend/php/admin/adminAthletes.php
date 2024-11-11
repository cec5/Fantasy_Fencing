<?php
require_once('adminHeader.php');
require_once('adminValidation.php');
require_once('../../../backend/php/scraper/scraperFunctions.php');
require_once('../../../backend/php/databaseFunctions.php');

$message = '';
$success = false;

// Handle automatic athlete addition by scraping data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['autoSubmit'])) {
    	$fencerID = $_POST['athleteID'];
    	$result = scrapeAthleteData($fencerID);

    	if (!$result) {
        	$message = "Unable to add to database. No athlete associated with ID: [$fencerID]";
        	$success = false;
    	} else {
        	updateAthlete($result);
        	$message = "Athlete data for ID: [$fencerID] successfully added/updated in the database";
        	$success = true;
    	}
}

// Handle manual athlete addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manualSubmit'])) {
    	$athleteData = [
		'id' => (int)$_POST['id'],
		'name' => $_POST['name'],
		'firstName' => $_POST['firstName'],
		'lastName' => $_POST['lastName'],
		'gender' => $_POST['gender'],
		'nationality' => strtoupper($_POST['nationality']),
		'weapon' => $_POST['weapon'],
		'weapon2' => !empty($_POST['weapon2']) ? $_POST['weapon2'] : null
    	];

    	try {
        	updateAthlete($athleteData);
        	$message = "Athlete data for ID: [{$athleteData['id']}] successfully added/updated in the database.";
        	$success = true;
    	} catch (Exception $e) {
        	$message = "Failed to add/update athlete: " . $e->getMessage();
        	$success = false;
    	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    	<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<title>Admin - Manage Athletes</title>
</head>
<body>
<div class="container mt-5">
    	<h2>Admin - Manage Athletes</h2>

    	<!-- Display result message at the top -->
    	<?php if ($message): ?>
        	<div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
            		<?= htmlspecialchars($message) ?>
        	</div>
    	<?php endif; ?>

    	<!-- Automatic Athlete Addition -->
    	<h3 class="mt-4">Add Athlete Automatically</h3>
    	<form method="POST" action="adminAthletes.php" class="mb-4">
        	<div class="mb-3">
            		<label for="athleteID" class="form-label">Athlete ID:</label>
            		<input type="number" class="form-control" id="athleteID" name="athleteID" required>
        	</div>
        	<button type="submit" class="btn btn-primary" name="autoSubmit">Add Athlete Automatically</button>
    	</form>

    	<!-- Manual Athlete Addition -->
    	<h3>Add Athlete Manually</h3>
    	<form method="POST" action="adminAthletes.php">
        	<div class="mb-3">
            		<label for="id" class="form-label">Athlete ID:</label>
            		<input type="number" class="form-control" id="id" name="id" required>
        	</div>
        	<div class="mb-3">
            		<label for="name" class="form-label">Full Name:</label>
            		<input type="text" class="form-control" id="name" name="name" required>
        	</div>
        	<div class="mb-3">
            		<label for="firstName" class="form-label">First Name:</label>
            		<input type="text" class="form-control" id="firstName" name="firstName" required>
        	</div>
        	<div class="mb-3">
            		<label for="lastName" class="form-label">Last Name:</label>
            		<input type="text" class="form-control" id="lastName" name="lastName" required>
        	</div>
        	<div class="mb-3">
            		<label for="gender" class="form-label">Gender:</label>
            		<select class="form-select" id="gender" name="gender" required>
                		<option value="male">Male</option>
                		<option value="female">Female</option>
            		</select>
        	</div>
        	<div class="mb-3">
            		<label for="nationality" class="form-label">Nationality (3-letter code):</label>
            		<input type="text" class="form-control" id="nationality" name="nationality" maxlength="3" required>
        	</div>
        	<div class="mb-3">
            		<label for="weapon" class="form-label">Primary Weapon:</label>
            		<select class="form-select" id="weapon" name="weapon" required>
                		<option value="sabre">Sabre</option>
                		<option value="epee">Epee</option>
                		<option value="foil">Foil</option>
            		</select>
        	</div>
        	<div class="mb-3">
            		<label for="weapon2" class="form-label">Secondary Weapon (optional):</label>
            		<select class="form-select" id="weapon2" name="weapon2">
                		<option value="">None</option>
               		 	<option value="sabre">Sabre</option>
                		<option value="epee">Epee</option>
                		<option value="foil">Foil</option>
            		</select>
        	</div>
        	<button type="submit" class="btn btn-primary" name="manualSubmit">Add Athlete Manually</button>
    	</form>
</div>
</body>
</html>
