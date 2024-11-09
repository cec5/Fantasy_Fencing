<?php 
include 'header.php'; 
include '../../backend/php/dataArrays.php';
include '../../backend/php/databaseFunctions.php';

$season = $_GET['season'] ?? '2025';
$weapon = $_GET['weapon'] ?? 'epee';
$gender = $_GET['gender'] ?? 'male';

// Fetch competitions based on the selected filters
$competitions = getCompetitions($season, $weapon, $gender);
?>

<!DOCTYPE html>
<html lang="en">
<body>
    	<div class="container mt-5">
        	<h2>Competitions</h2>
        
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
            		<div class="col-md-3">
                		<label for="weapon" class="form-label">Weapon</label>
                		<select class="form-select" id="weapon" name="weapon">
                    			<option value="epee" <?= $weapon == 'epee' ? 'selected' : '' ?>>Epee</option>
                    			<option value="foil" <?= $weapon == 'foil' ? 'selected' : '' ?>>Foil</option>
                    			<option value="sabre" <?= $weapon == 'sabre' ? 'selected' : '' ?>>Sabre</option>
                		</select>
            		</div>
            		<div class="col-md-3">
                		<label for="gender" class="form-label">Gender</label>
                		<select class="form-select" id="gender" name="gender">
                    			<option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male</option>
                    			<option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female</option>
                		</select>
            		</div>
            		<div class="col-md-3">
                		<button type="submit" class="btn btn-primary w-100">Filter</button>
            		</div>
        	</form>

        	<!-- Competitions Table -->
        	<h3 class="mt-5">Results</h3>
        	<table class="table table-striped">
            		<thead>
                		<tr>
                    			<th>Date</th>
                    			<th>Competition</th>
                    			<th>Type</th>
                    			<th>Location</th>
                		</tr>
            		</thead>
            		<tbody>
                		<?php foreach ($competitions as $competition):?>
                    			<tr>
                        			<td><?= htmlspecialchars($competition['startDate']) ?></td>
                        			<td><a href="competition.php?season=<?= $competition['season'] ?>&id=<?= $competition['competitionId']?>"><?= htmlspecialchars($competition['name'])?></a></td>
                        			<td><?= htmlspecialchars($competitionCategories[$competition['category']] ?? $competition['category']) ?></td>
                        			<td><?= htmlspecialchars($competition['location'] . ', ' . $competition['country'])?></td>
                    			</tr>
                		<?php endforeach;?>
            		</tbody>
        	</table>
    	</div>
</body>
</html>
