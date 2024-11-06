<?php 
include 'header.php'; 
include '../../backend/php/dataArrays.php';
include '../../backend/php/databaseFunctions.php';

$season = $_GET['season'];
$competitionId = $_GET['id'];

// Fetch competition details and results
$competition = getCompetitionDetails($competitionId, $season);
$results = getSpecificCompetitionResult($competitionId, $season);
?>

<body>
    	<div class="container mt-5">
        	<!-- Competition Name as Main Title -->
        	<h1 class="text-center"><?= htmlspecialchars($competition['name'])?></h1>

        	<!-- Basic Information in Two Rows -->
        	<div class="row mt-3 justify-content-center">
            		<div class="col-md-3">
                		<div class="border p-3 text-center" style="font-size: 1.25rem;">
                    			<strong>Date</strong><br>
                    			<?= htmlspecialchars($competition['startDate'])?>
               			 </div>
            		</div>
            		<div class="col-md-3">
                		<div class="border p-3 text-center" style="font-size: 1.25rem;">
                    			<strong>Location</strong><br>
                    			<?= htmlspecialchars($competition['location'] . ', ' . $competition['country'])?>
                		</div>
            		</div>
        	</div>
        	<div class="row mt-3 justify-content-center">
            		<div class="col-md-3">
                		<div class="border p-3 text-center" style="font-size: 1.25rem;">
                    			<strong>Type</strong><br>
                    			<?= htmlspecialchars($competitionCategories[$competition['category']] ?? $competition['category']) ?>
                		</div>
            		</div>
            		<div class="col-md-3">
                		<div class="border p-3 text-center" style="font-size: 1.25rem;">
                    			<strong>Weapon</strong><br>
                    			<?= ucfirst($competition['weapon'])?>
                		</div>
            		</div>
            		<div class="col-md-3">
                		<div class="border p-3 text-center" style="font-size: 1.25rem;">
                    			<strong>Gender</strong><br>
                    			<?= ucfirst($competition['gender'])?>
                		</div>
            		</div>
       		</div>

        	<!-- Competition Results Table -->
        	<h4 class="mt-4">Competition Results</h4>
        	<?php if (!empty($results)):?>
            		<table class="table table-striped">
                		<thead>
                    			<tr>
                        			<th>Place</th>
                        			<th>Name</th>
                        			<th>Country</th>
                        			<th>Points</th>
                    			</tr>
                		</thead>
		        	<tbody>
				    	<?php foreach ($results as $result):?>
				        	<tr>
				            		<td><?= $result['place'] ?></td>
				            		<td><a href="athlete.php?id=<?= $result['athleteId']?>"><?= htmlspecialchars($result['name']) ?></a></td>
				            		<td><?= htmlspecialchars($validCountryCodes[$result['nationality']] ?? $result['nationality']) ?></td>
				            		<td><?= htmlspecialchars($result['points'])?></td>
				        	</tr>
				    	<?php endforeach;?>
		        	</tbody>
            		</table>
		<?php else:?>
            		<p class="mt-4 text-center">There are no results available for this competition.</p>
        	<?php endif;?>
    	</div>
</body>
</html>
