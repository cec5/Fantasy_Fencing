<?php // This page lists competitions that players can currently draft for
include(dirname(__DIR__) . '/common/header.php');
require_once(dirname(__DIR__) . '/../backend/php/dataArrays.php');
require_once(dirname(__DIR__) . '/../backend/php/databaseFunctions.php');
require_once(dirname(__DIR__) . '/../backend/php/fantasyFunctions.php');

$season = $_GET['season'] ?? '2025';
$weapon = $_GET['weapon'] ?? '';
$gender = $_GET['gender'] ?? '';
$ageCategory = $_GET['ageCategory'] ?? '';

$competitions = getFilteredUpcomingCompetitions($season, $weapon, $gender, $ageCategory);
?>

<main class="container my-5">
	<h2>Draft Athletes for Upcoming Competitions</h2>

	<!-- Filters Form -->
	<form method="GET" class="row g-3 align-items-end">
		<div class="col-md-3">
			<label class="form-label">Weapon</label>
			<select name="weapon" class="form-select">
				<option value="">All</option>
				<option value="epee" <?= $weapon == 'epee' ? 'selected' : '' ?>>Epee</option>
				<option value="foil" <?= $weapon == 'foil' ? 'selected' : '' ?>>Foil</option>
				<option value="sabre" <?= $weapon == 'sabre' ? 'selected' : '' ?>>Sabre</option>
			</select>
		</div>
		<div class="col-md-3">
			<label class="form-label">Gender</label>
			<select name="gender" class="form-select">
				<option value="">All</option>
				<option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male</option>
				<option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female</option>
			</select>
		</div>
		<div class="col-md-3">
			<label class="form-label">Category</label>
			<select name="ageCategory" class="form-select">
				<option value="">All</option>
				<?php foreach ($ageCategories as $code => $name): ?>
					<option value="<?= $code ?>" <?= $ageCategory == $code ? 'selected' : '' ?>><?= $name ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-3 align-self-end">
			<button type="submit" class="btn btn-primary w-100">Filter</button>
		</div>
	</form>

	<!-- Competitions Table -->
	<?php if (!empty($competitions)): ?>
		<table class="table table-striped table-bordered">
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
				<?php foreach ($competitions as $comp): ?>
					<tr>
						<td><?= htmlspecialchars($comp['startDate']) ?></td>
						<td><a
								href="fantasy/draft.php?season=<?= $season ?>&id=<?= $comp['competitionId'] ?>"><?= htmlspecialchars($comp['name']) ?></a>
						</td>
						<td><?= htmlspecialchars($comp['location'] . ', ' . $comp['country']) ?></td>
						<td><?= htmlspecialchars($competitionCategories[$comp['category']] ?? $comp['category']) ?></td>
						<td><?= ucfirst($comp['weapon']) ?></td>
						<td><?= ucfirst($comp['gender']) ?></td>
						<td><?= htmlspecialchars($ageCategories[$comp['ageCategory']] ?? $comp['ageCategory']) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p class="mt-4 text-center">No upcoming competitions match your filters</p>
	<?php endif; ?>
</main>
<?php include(dirname(__DIR__) . '/common/footer.php'); ?>