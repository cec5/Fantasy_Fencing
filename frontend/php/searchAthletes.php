<?php 
include 'header.php'; 
include '../../backend/php/dataArrays.php';
?>
<body>
	<div class="container mt-5">
        	<h2>Search FIE Athletes</h2>
        	<form action="" method="GET">
            		<div class="row g-3 align-items-end">
                		<div class="col-md-3">
                    			<label for="name" class="form-label">Name</label>
                    			<input type="text" class="form-control" id="name" name="name" placeholder="Athlete Name">
                		</div>
                		<div class="col-md-2">
                    			<label for="gender" class="form-label">Gender</label>
                    			<select class="form-select" id="gender" name="gender">
                       				<option value="">Any</option>
                        			<option value="male">Male</option>
                        			<option value="female">Female</option>
                    			</select>
                		</div>
                		<div class="col-md-2">
                    			<label for="weapon" class="form-label">Weapon</label>
                    			<select class="form-select" id="weapon" name="weapon">
						<option value="">Any</option>
						<option value="epee">Epee</option>
						<option value="foil">Foil</option>
						<option value="sabre">Sabre</option>
                    			</select>
                		</div>
                		<div class="col-md-2">
                    			<label for="country" class="form-label">Country</label>
                    			<input type="text" class="form-control" id="country" name="country" list="countryList" autocomplete="off" placeholder="Country">
                    			<datalist id="countryList">
                        			<?php foreach ($validCountryCodes as $code => $country): ?>
                            			<option value="<?= htmlspecialchars($country) ?>"></option>
                        			<?php endforeach; ?>
                    			</datalist>
                		</div>
                		<div class="col-md-2">
                    			<button type="submit" class="btn btn-primary w-100">Search</button>
                		</div>
            		</div>
        	</form>

        	<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)): ?>
            		<h3 class="mt-5">Search Results</h3>
            		<table class="table table-striped">
                		<thead>
                    			<tr>
                        			<th>Name</th>
                        			<th>Gender</th>
                        			<th>Weapon</th>
                        			<th>Country</th>
                    			</tr>
                		</thead>
                		<tbody>
		            		<?php
		            		require_once '../../backend/php/databaseFunctions.php';
		            		$countryCode = array_search($_GET['country'] ?? '', $validCountryCodes) ?: ''; // Convert country name to code
		            		$results = searchAthletes($_GET['name'] ?? '', $_GET['gender'] ?? '', $_GET['weapon'] ?? '', $countryCode);
				    	foreach ($results as $athlete): 
				        	$weaponDisplay = ucfirst($athlete['weapon']);
				        	if ($athlete['weapon2']) {
				            		$weaponDisplay .= '/' . ucfirst($athlete['weapon2']);
				        	}
				        	$genderDisplay = ucfirst($athlete['gender']);
				        	$countryName = $validCountryCodes[$athlete['nationality']] ?? $athlete['nationality'];
				    	?>
				        <tr>
		                    		<td><a href="athlete.php?id=<?= $athlete['id'] ?>"><?= htmlspecialchars($athlete['name']) ?></a></td>
		                    		<td><?= htmlspecialchars($genderDisplay) ?></td>
		                    		<td><?= htmlspecialchars($weaponDisplay) ?></td>
		                    		<td><?= htmlspecialchars($countryName) ?></td>
		                	</tr>
		            		<?php endforeach; ?>
                		</tbody>
            		</table>
       	 	<?php endif; ?>
    	</div>
</body>
</html>
