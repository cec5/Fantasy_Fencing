<?php // This page shows the fantasy points leaderboard (Dev wrote this page)
include(dirname(__DIR__).'/common/header.php'); 
require_once(dirname(__DIR__).'/../backend/php/dataArrays.php');
require_once(dirname(__DIR__).'/../backend/php/databaseFunctions.php');
require_once(dirname(__DIR__).'/../backend/php/fantasyFunctions.php');
require_once(dirname(__DIR__).'/../backend/php/otherFunctions.php');

$season = $_GET['season'] ?? '2025';
$weapon = $_GET['weapon'] ?? 'epee';
$gender = $_GET['gender'] ?? 'male';
$ageCategory = $_GET['ageCategory'] ?? 'S';

$fantasyLeaderboard = getFantasyLeaderboard($season, $weapon, $gender, $ageCategory);
?>

<body>
    <div class="container mt-5">
        <h2>Fantasy Leaderboard</h2>
        
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
            <div class="col-md-2">
                <label for="weapon" class="form-label">Weapon</label>
                <select class="form-select" id="weapon" name="weapon">
                    <option value="epee" <?= $weapon == 'epee' ? 'selected' : '' ?>>Epee</option>
                    <option value="foil" <?= $weapon == 'foil' ? 'selected' : '' ?>>Foil</option>
                    <option value="sabre" <?= $weapon == 'sabre' ? 'selected' : '' ?>>Sabre</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="ageCategory" class="form-label">Category</label>
                <select class="form-select" id="ageCategory" name="ageCategory">
                    <?php foreach ($ageCategories as $code => $name): ?>
                        <option value="<?= $code ?>" <?= $code == $ageCategory ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Results Table -->
        <h3 class="mt-5">Ranking</h3>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>Nationality</th>
                    <th>Total Fantasy Points</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 0;
                $previousPoints = null;
                foreach ($fantasyLeaderboard as $index => $player):
                    // Assign rank only if points are different from previous
                    if ($previousPoints === null || $player['totalPoints'] < $previousPoints) {
                        $rank = $index + 1;
                    }
                    $previousPoints = $player['totalPoints'];
                ?>
                    <tr>
                        <td><?= $rank ?></td>
                        <td><?= htmlspecialchars($player['username']) ?></td>
                        <td><?= htmlspecialchars($validCountryCodes[$player['nationality']] ?? $player['nationality']) ?></td>
                        <td><?= htmlspecialchars($player['totalPoints']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
