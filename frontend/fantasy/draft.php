<?php
require_once(dirname(__DIR__) . '/common/validation.php');
require_once(dirname(__DIR__) . '/../backend/php/dataArrays.php');
require_once(dirname(__DIR__) . '/../backend/php/databaseFunctions.php');
require_once(dirname(__DIR__) . '/../backend/php/fantasyFunctions.php');

// Validate incoming GET
$season = $_GET['season'] ?? '';
$competitionId = $_GET['id'] ?? '';

$competition = getCompetitionDetails($competitionId, $season);
$selections = getUserSelections($userId, $season, $competitionId);
$isLocked = isCompetitionLocked($season, $competitionId);

// Handle athlete selection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {
	$athleteId = $_POST['athleteId'] ?? '';
	$action = $_POST['action'] ?? '';

	if (!empty($athleteId) && in_array($action, ['add', 'remove'])) {
		$response = updateUserSelection($userId, $season, $competitionId, $athleteId, $action);
		header("Location: /fantasy/draft.php?season=$season&id=$competitionId&message=" . urlencode($response['message']));
		exit();
	}
}

// Handle athlete search
$searchResults = [];
if (!empty($_GET['name']) || !empty($_GET['country'])) {
	$searchName = $_GET['name'] ?? '';
	$searchCountry = $_GET['country'] ?? '';
	$searchResults = searchAthletes($searchName, $competition['gender'], $competition['weapon'], strtoupper(array_search($searchCountry, $validCountryCodes) ?: ''));
}

// Handle flash message
$flashMessage = $_GET['message'] ?? '';

include(dirname(__DIR__) . '/common/header.php');
?>

<main class="container my-5">
	<?php if ($flashMessage): ?>
		<div class="alert alert-info text-center"><?= htmlspecialchars($flashMessage) ?></div>
	<?php endif; ?>
	<h1 class="text-center">Draft Athletes for <?= htmlspecialchars($competition['name']) ?></h1>

	<!-- Competition Info -->
	<div class="row mt-3 justify-content-center">
		<div class="col-md-3">
			<div class="border p-2 text-center" style="font-size: 1.25rem;">
				<strong>Date</strong><br><?= htmlspecialchars($competition['startDate']) ?></div>
		</div>
		<div class="col-md-3">
			<div class="border p-2 text-center" style="font-size: 1.25rem;">
				<strong>Location</strong><br><?= htmlspecialchars($competition['location'] . ', ' . $competition['country']) ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="border p-2 text-center" style="font-size: 1.25rem;">
				<strong>Type</strong><br><?= htmlspecialchars($competitionCategories[$competition['category']] ?? $competition['category']) ?>
			</div>
		</div>
	</div>

	<div class="row mt-3 justify-content-center">
		<div class="col-md-3">
			<div class="border p-2 text-center" style="font-size: 1.25rem;">
				<strong>Weapon</strong><br><?= ucfirst($competition['weapon']) ?></div>
		</div>
		<div class="col-md-3">
			<div class="border p-2 text-center" style="font-size: 1.25rem;">
				<strong>Gender</strong><br><?= ucfirst($competition['gender']) ?></div>
		</div>
		<div class="col-md-3">
			<div class="border p-2 text-center" style="font-size: 1.25rem;">
				<strong>Level</strong><br><?= htmlspecialchars($ageCategories[$competition['ageCategory']] ?? $competition['ageCategory']) ?>
			</div>
		</div>
	</div>

	<!-- User Draft Selections -->
	<h3 class="mt-5">Current Roster</h3>
	<?php if ($isLocked): ?>
		<p class="text-danger text-center">This competition is locked. No changes can be made.</p>
	<?php endif; ?>

	<table class="table table-sm table-striped table-bordered text-center">
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Nationality</th>
				<?php if (!$isLocked): ?>
					<th>Action</th><?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php for ($i = 0; $i < 5; $i++): ?>
				<tr>
					<td><?= $i + 1 ?></td>
					<?php if (isset($selections[$i])): ?>
						<td><?= htmlspecialchars($selections[$i]['name']) ?></td>
						<td><?= htmlspecialchars($validCountryCodes[$selections[$i]['nationality']] ?? $selections[$i]['nationality']) ?>
						</td>
						<?php if (!$isLocked): ?>
							<td>
								<form method="POST" class="d-inline">
									<input type="hidden" name="athleteId" value="<?= $selections[$i]['athleteId'] ?>">
									<input type="hidden" name="action" value="remove">
									<button type="submit" class="btn btn-danger btn-sm">Remove</button>
								</form>
							</td>
						<?php endif; ?>
					<?php else: ?>
						<td colspan="<?= $isLocked ? '2' : '3' ?>"></td>
					<?php endif; ?>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>

	<?php if (!$isLocked): ?>
		<h3 class="mt-5">Search for Athletes</h3>
		<form method="GET" class="row g-3">
			<input type="hidden" name="season" value="<?= $season ?>">
			<input type="hidden" name="id" value="<?= $competitionId ?>">
			<div class="col-md-4">
				<input type="text" class="form-control" name="name" placeholder="Athlete Name">
			</div>
			<div class="col-md-4">
				<input type="text" class="form-control" name="country" list="countryList" placeholder="Country">
				<datalist id="countryList">
					<?php foreach ($validCountryCodes as $code => $country): ?>
						<option value="<?= htmlspecialchars($country) ?>"></option>
					<?php endforeach; ?>
				</datalist>
			</div>
			<div class="col-md-4">
				<button type="submit" class="btn btn-primary w-100">Search</button>
			</div>
		</form>

		<?php if (!empty($searchResults)): ?>
			<h4 class="mt-4">Search Results</h4>
			<table class="table table-sm table-striped table-bordered text-center">
				<thead>
					<tr>
						<th>Name</th>
						<th>Country</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($searchResults as $athlete): ?>
						<tr>
							<td><?= htmlspecialchars($athlete['name']) ?></td>
							<td><?= htmlspecialchars($validCountryCodes[$athlete['nationality']] ?? $athlete['nationality']) ?></td>
							<td>
								<form method="POST" class="d-inline">
									<input type="hidden" name="athleteId" value="<?= $athlete['id'] ?>">
									<input type="hidden" name="action" value="add">
									<button type="submit" class="btn btn-success btn-sm">Add</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	<?php endif; ?>
</main>
<?php include(dirname(__DIR__) . '/common/footer.php'); ?>