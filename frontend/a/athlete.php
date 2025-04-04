<?php 
include(dirname(__DIR__).'/common/header.php'); 
require_once(dirname(__DIR__).'/../backend/php/dataArrays.php');
require_once(dirname(__DIR__).'/../backend/php/databaseFunctions.php');
require_once(dirname(__DIR__).'/../backend/php/otherFunctions.php');

// Get info from URL
$athleteId = $_GET['id'];
$season = $_GET['season'] ?? '2025';
$ageCategory = $_GET['ageCategory'] ?? 'S';

// Fetch Athlete Basic Information
$athlete = getAthleteInfo($athleteId);

// Fetch available weapons and set default weapon if none is specified
$availableWeapons = getAthleteWeapons($athleteId);
$weapon = $_GET['weapon'] ?? $availableWeapons[0]; // Set to primary weapon if not set

// Fetch total points for the selected season, weapon, and age category
$totalPoints = getTotalPoints($athleteId, $season, $weapon, $ageCategory);

// Fetch competition results for the selected season, weapon, and age category
$results = getCompetitionResults($athleteId, $season, $weapon, $ageCategory);

// Get the two-letter country code for the flag emoji
$twoLetterCountryCode = $countryCodeMap[$athlete['nationality']] ?? '';
$flagEmoji = $twoLetterCountryCode ? getFlagEmoji($twoLetterCountryCode) : '';
?>

<!DOCTYPE html>
<html lang="en">
<body>
    	<div class="container mt-5">
        	<!-- Athlete Name as Main Title with Flag Emoji -->
        	<h1>
            		<?= htmlspecialchars($athlete['firstName'] . ' ' . $athlete['lastName'])?> 
            		<?= $flagEmoji?>
        	</h1>

        	<!-- Basic Information Row -->
        	<div class="row mt-3 text-center">
            		<div class="col-md-4">
                		<div class="border p-3" style="font-size: 1.25rem;">
                    			<strong>Gender</strong><br>
                    			<?= ucfirst($athlete['gender']) ?>
                		</div>
            		</div>
            	<div class="col-md-4">
                	<div class="border p-3" style="font-size: 1.25rem;">
                    		<strong>Weapon(s)</strong><br>
                    		<?= htmlspecialchars(implode('/', array_map('ucfirst', $availableWeapons))) ?>
                	</div>
            	</div>
            	<div class="col-md-4">
                	<div class="border p-3" style="font-size: 1.25rem;">
                    		<strong>Country</strong><br>
                    		<?= htmlspecialchars($validCountryCodes[$athlete['nationality']] ?? $athlete['nationality']) ?>
                	</div>
            	</div>
        </div>

        <!-- Season Tabs -->
        <ul class="nav nav-tabs mt-4" id="seasonTab" role="tablist">
            	<?php foreach ($seasons as $seasonCode => $seasonName):?>
                	<li class="nav-item" role="presentation">
                    		<a class="nav-link <?= $seasonCode == $season ? 'active' : '' ?>" href="a/athlete.php?id=<?= $athleteId ?>&season=<?= $seasonCode?>&weapon=<?= $weapon?>"><?= $seasonName?></a>
                	</li>
            	<?php endforeach;?>
        </ul>

        <!-- Weapon Dropdown (if multiple weapons) -->
        <?php if (count($availableWeapons) > 1):?>
            	<div class="mt-3">
                	<label for="weapon" class="form-label">Select Weapon:</label>
                	<select class="form-select" id="weapon" onchange="location = this.value;">
                    		<?php foreach ($availableWeapons as $w):?>
                        		<option value="a/athlete.php?id=<?= $athleteId?>&season=<?= $season?>&weapon=<?= $w?>" <?= $w == $weapon ? 'selected' : '' ?>><?= ucfirst($w)?></option>
                    		<?php endforeach;?>
                	</select>
            	</div>
        <?php endif; ?>

        <!-- Age Category Dropdown (TODO: Hide filter if only senior) -->
        <div class="mt-3">
            	<label for="ageCategory" class="form-label">Select Age Category:</label>
            	<select class="form-select" id="ageCategory" onchange="location = this.value;">
                	<?php foreach ($ageCategories as $code => $category): ?>
                    		<option value="a/athlete.php?id=<?= $athleteId ?>&season=<?= $season ?>&weapon=<?= $weapon ?>&ageCategory=<?= $code ?>" <?= $ageCategory == $code ? 'selected' : '' ?>>
                        	<?= htmlspecialchars($category) ?>
                    		</option>
                	<?php endforeach; ?>
            	</select>
        </div>

        <!-- Total Points -->
        <h4 class="mt-4">Total <?= ucfirst($ageCategories[$ageCategory] ?? 'Senior') ?> Points Earned: <?= htmlspecialchars($totalPoints) ?></h4>

        <!-- Competition Results Table -->
        <h5 class="mt-4"><?= ucfirst($ageCategories[$ageCategory] ?? 'Senior') ?> Competition Results</h5>
        <table class="table table-striped table-bordered">
            	<thead>
                	<tr>
                    		<th>Date</th>
                    		<th>Competition</th>
                    		<th>Type</th>
                    		<th>Location</th>
                    		<th>Placement</th>
                    		<th>Points</th>
                	</tr>
            	</thead>
         	<tbody>
                	<?php foreach ($results as $result):?>
                    		<tr>
                        		<td><?= htmlspecialchars($result['startDate'])?></td>
                        		<td><a href="c/competition.php?season=<?= $season?>&id=<?= $result['competitionId'] ?>"><?= htmlspecialchars($result['name']) ?></a></td>
                        		<td><?= htmlspecialchars($competitionCategories[$result['category']] ?? $result['category']) ?></td>
                        		<td><?= htmlspecialchars($result['location'] . ', ' . $result['country']) ?></td>
                        		<td><?= $result['finished'] == 1 ? '🥇' : ($result['finished'] == 2 ? '🥈' : ($result['finished'] == 3 ? '🥉' : htmlspecialchars($result['finished'])))?></td>
                        		<td><?= htmlspecialchars($result['points'])?></td>
                    		</tr>
                	<?php endforeach;?>
            	</tbody>
        	</table>
    	</div>
</body>
</html>
