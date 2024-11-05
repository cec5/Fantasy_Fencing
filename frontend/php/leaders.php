<?php 
include 'header.php'; 
include '../../backend/php/dataArrays.php';
include '../../backend/php/databaseFunctions.php';
include '../../backend/php/otherFunctions.php';

$season = $_GET['season'] ?? '2025';
$weapon = $_GET['weapon'] ?? 'epee';
$gender = $_GET['gender'] ?? 'male';

$topEarners = getTopEarners($season, $weapon, $gender);
?>

<body>
    	<div class="container mt-5">
        	<h2>FIE Points Leaders</h2>
        
        	<!-- Filter Form -->
        	<form method="GET" class="row g-3 align-items-end">
            		<div class="col-md-3">
                		<label for="season" class="form-label">Season</label>
                		<select class="form-select" id="season" name="season">
                    			<?php foreach ($seasons as $seasonCode => $seasonName): ?>
                        			<option value="<?= $seasonCode ?>" <?= $seasonCode == $season ? 'selected' : '' ?>><?= $seasonName ?></option>
                    			<?php endforeach; ?>
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

        	<!-- Results Table -->
        	<h3 class="mt-5">Results</h3>
        	<table class="table table-striped">
            		<thead>
                		<tr>
                    			<th>Rank</th>
                    			<th>Name</th>
                    			<th>Country</th>
                    			<th>Total Points</th>
                		</tr>
            		</thead>
            		<tbody>
                	<?php
                	$rank = 0;
                	$previousPoints = null;
                	foreach ($topEarners as $index => $athlete):
                    		// Increment rank only if points are different from previous points
                    		if ($previousPoints === null || $athlete['points'] < $previousPoints) {
                        		$rank = $index + 1;
                    		}
                    		$previousPoints = $athlete['points'];
                	?>
		            	<tr>
		                	<td><?= $rank ?></td>
		                	<td><a href="athlete.php?id=<?= $athlete['id'] ?>"><?= htmlspecialchars($athlete['name']) ?></a></td>
		                	<td><?= htmlspecialchars($validCountryCodes[$athlete['nationality']] ?? $athlete['nationality']) ?></td>
		                	<td><?= htmlspecialchars($athlete['points']) ?></td>
		            	</tr>
                	<?php endforeach;?>
            		</tbody>
        	</table>
    	</div>
</body>
</html>
