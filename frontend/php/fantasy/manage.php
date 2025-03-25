<?php
require_once('fantasyHeader.php');
require_once('../../../backend/php/databaseFunctions.php');
require_once('../../../backend/php/fantasyFunctions.php');
require_once('../../../backend/php/dataArrays.php');
require_once('fantasyValidation.php');

$season = $_GET['season'] ?? '2025';
$weapon = $_GET['weapon'] ?? '';
$gender = $_GET['gender'] ?? '';
$ageCategory = $_GET['ageCategory'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<body>
<div class="container mt-5">
    	<h2>View/Manage Selections</h2>

    	<!-- Filter Form -->
        <form method="GET" class="row g-3 align-items-end">
            	<div class="col-md-3">
                	<label for="season" class="form-label">Season</label>
                	<select class="form-select" id="season" name="season">
                    		<?php foreach ($seasons as $seasonCode => $seasonName):?>
                        		<option value="<?= $seasonCode ?>" <?= $seasonCode == $season ? 'selected' : '' ?>><?= $seasonName ?></option>
                    		<?php endforeach;?>
                	</select>
           	</div>
            	<div class="col-md-2">
                	<label for="weapon" class="form-label">Weapon</label>
                	<select class="form-select" id="weapon" name="weapon">
                		<option value="">Any</option>
                    		<option value="epee" <?= $weapon == 'epee' ? 'selected' : '' ?>>Epee</option>
                    		<option value="foil" <?= $weapon == 'foil' ? 'selected' : '' ?>>Foil</option>
                    		<option value="sabre" <?= $weapon == 'sabre' ? 'selected' : '' ?>>Sabre</option>
               		</select>
            	</div>
            	<div class="col-md-2">
                	<label for="gender" class="form-label">Gender</label>
                	<select class="form-select" id="gender" name="gender">
                		<option value="">Any</option>
                    		<option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male</option>
                    		<option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female</option>
               		</select>
            	</div>
            	<div class="col-md-2">
                	<label for="ageCategory" class="form-label">Category</label>
                	<select class="form-select" id="ageCategory" name="ageCategory">
                		<option value="">Any</option>
                    		<?php foreach ($ageCategories as $code => $name): ?>
                        		<option value="<?= $code ?>" <?= $code == $ageCategory ? 'selected' : '' ?>><?= $name ?></option>
                    		<?php endforeach; ?>
                	</select>
            	</div>
            	<div class="col-md-3">
                	<button type="submit" class="btn btn-primary w-100">Filter</button>
            	</div>
        </form>

    	<!-- Tabs -->
    	<ul class="nav nav-tabs" id="manageTabs" role="tablist">
        	<li class="nav-item" role="presentation">
            		<button class="nav-link active" data-bs-toggle="tab" data-bs-target="#upcomingTab" type="button">Upcoming</button>
        	</li>
        	<li class="nav-item" role="presentation">
            		<button class="nav-link" data-bs-toggle="tab" data-bs-target="#pastTab" type="button">Past</button>
        	</li>
    	</ul>
    	<div class="tab-content mt-3">
		<!-- Upcoming Tab -->
		<div class="tab-pane fade show active" id="upcomingTab">
			<?php
			$upcoming = getUserCompetitions($userId, $season, false, $weapon, $gender, $ageCategory);
			if (empty($upcoming)) {
				echo "<p class='text-muted mt-3'>No upcoming competitions with your selections.</p>";
			} else {
				foreach ($upcoming as $comp):
				$sel = getUserSelections($userId, $season, $comp['competitionId']);
			?>
			<div class="mb-5 border p-3 rounded">
				<table class="table table-striped table-bordered text-center">
					<thead>
						<tr>
							<th>Date</th>
							<th>Name</th>
							<th>Location</th>
							<th>Type</th>
							<th>Weapon</th>
							<th>Gender</th>
							<th>Category</th>
						</tr>
					</thead>
					<tbody>
						<tr>
				            		<td><?= htmlspecialchars($comp['startDate']) ?></td>
				            		<td><a href="draft.php?season=<?= $season ?>&id=<?= $comp['competitionId']?>"><?= htmlspecialchars($comp['name'])?></a></td>
				            		<td><?= htmlspecialchars($comp['location'] . ', ' . $comp['country']) ?></td>
				            	
				            		<td><?= htmlspecialchars($competitionCategories[$comp['category']] ?? $comp['category']) ?></td>
				            		<td><?= ucfirst($comp['weapon']) ?></td>
				            		<td><?= ucfirst($comp['gender']) ?></td>
				            		<td><?= $ageCategories[$comp['ageCategory']] ?? $comp['ageCategory'] ?></td>
				        	</tr>
				        </tbody>
				</table>
				<table class="table table-striped table-sm table-bordered">
				        <thead>
				            	<tr><th>#</th><th>Name</th><th>Nationality</th></tr>
				        </thead>
				        <tbody>
				            	<?php foreach ($sel as $i => $ath): ?>
				                	<tr>
				                    		<td><?= $i + 1 ?></td>
				                    		<td><a href="../athlete.php?id=<?= $ath['athleteId']?>"><?= htmlspecialchars($ath['name']) ?></a></td>
				                    		<td><?= $validCountryCodes[$ath['nationality']] ?? $ath['nationality'] ?></td>
				                	</tr>
				           	<?php endforeach; ?>
				        </tbody>
				</table>
			</div>
		    	<?php endforeach; } ?>
		</div>

		<!-- Past Tab -->
		<div class="tab-pane fade" id="pastTab">
		    	<?php
		    	$past = getUserCompetitions($userId, $season, true, $weapon, $gender, $ageCategory);
		    	if (empty($past)) {
		        	echo "<p class='text-muted mt-3'>No past competitions with your selections.</p>";
		    	} else {
		        	foreach ($past as $comp):
		            	$sel = getUserSelections($userId, $season, $comp['competitionId']);
		            	$results = getSpecificCompetitionResult($comp['competitionId'], $season);
		            	$pointsMap = [];
		            	foreach ($results as $r) {
		                	$pointsMap[$r['athleteId']] = $r['points'];
		            	}
		    	?>
		        <div class="mb-5 border p-3 rounded">
		            	<table class="table table-striped table-bordered text-center">
					<thead>
						<tr>
							<th>Date</th>
							<th>Name</th>
							<th>Location</th>
							<th>Type</th>
							<th>Weapon</th>
							<th>Gender</th>
							<th>Category</th>
						</tr>
					</thead>
					<tbody>
						<tr>
				            		<td><?= htmlspecialchars($comp['startDate']) ?></td>
				            		<td><a href="../competition.php?season=<?= $season ?>&id=<?= $comp['competitionId']?>"><?= htmlspecialchars($comp['name'])?></a></td>
				            		<td><?= htmlspecialchars($comp['location'] . ', ' . $comp['country']) ?></td>
				            	
				            		<td><?= htmlspecialchars($competitionCategories[$comp['category']] ?? $comp['category']) ?></td>
				            		<td><?= ucfirst($comp['weapon']) ?></td>
				            		<td><?= ucfirst($comp['gender']) ?></td>
				            		<td><?= $ageCategories[$comp['ageCategory']] ?? $comp['ageCategory'] ?></td>
				        	</tr>
				        </tbody>
				</table>
		            	<table class="table table-striped table-sm table-bordered">
		                	<thead>
		                    		<tr><th>#</th><th>Name</th><th>Nationality</th><th>Points</th></tr>
		                	</thead>
		                	<tbody>
		                    		<?php foreach ($sel as $i => $ath): ?>
		                        		<tr>
		                            			<td><?= $i + 1 ?></td>
		                            			<td><a href="../athlete.php?id=<?= $ath['athleteId']?>"><?= htmlspecialchars($ath['name']) ?></a></td>
		                            			<td><?= $validCountryCodes[$ath['nationality']] ?? $ath['nationality'] ?></td>
		                            			<td><?= $pointsMap[$ath['athleteId']] ?? 0 ?></td>
		                        		</tr>
		                    		<?php endforeach; ?>
		                	</tbody>
		            	</table>
		        </div>
		    	<?php endforeach; } ?>
		</div>
    	</div>
</div>
</body>
</html>

