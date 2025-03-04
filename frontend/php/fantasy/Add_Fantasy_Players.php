<?php
require_once('fantasyHeader.php');
require_once('../../../backend/php/dataArrays.php');
require_once('../../../backend/php/databaseFunctions.php');
require_once('../../../backend/php/otherFunctions.php');
require_once('../../../backend/php/fantasyFunctions.php');

$season = $_GET['season'] ?? '2025';
$competitionId = $_GET['competitionId'] ?? ''; // Get competition ID from URL
$userId = $_SESSION['userId'] ?? 1; 

$allPlayers = getFantasyLeaderboard($season, '', '', '', $competitionId); // Fetch players for the specific competition
$userTeam = getUserSelections($userId, $season, $competitionId); // Fetch current user selections for the competition
?>

<body>
    <div class="container mt-5">
        <h2>Fantasy Fencing Team Builder</h2>

        <!-- User Team Display -->
        <h3>Your Team</h3>
        <ul id="user-team" class="list-group">
            <?php foreach ($userTeam as $player): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($player['name']) ?> (<?= htmlspecialchars($player['nationality']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Player List -->
        <h3 class="mt-5">Available Players</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Total Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allPlayers as $player): ?>
                    <tr>
                        <td><?= htmlspecialchars($player['username']) ?></td>
                        <td><?= htmlspecialchars($player['nationality']) ?></td>
                        <td><?= htmlspecialchars($player['totalPoints']) ?></td>
                        <td>
                            <button class="btn btn-success btn-sm add-player" data-id="<?= $player['id'] ?>" data-name="<?= htmlspecialchars($player['username']) ?>" data-country="<?= htmlspecialchars($player['nationality']) ?>">
                                +
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.add-player').forEach(button => {
            button.addEventListener('click', function() {
                const playerId = this.getAttribute('data-id');
                const playerName = this.getAttribute('data-name');
                const playerCountry = this.getAttribute('data-country');
                
                fetch('updateFantasyTeam.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `userId=<?= $userId ?>&season=<?= $season ?>&competitionId=<?= $competitionId ?>&athleteId=${playerId}&action=add`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let teamList = document.getElementById('user-team');
                        let newPlayer = document.createElement('li');
                        newPlayer.className = 'list-group-item';
                        newPlayer.textContent = `${playerName} (${playerCountry})`;
                        teamList.appendChild(newPlayer);
                    } else {
                        alert(data.message);
                    }
                });
            });
        });
    </script>
</body>
</html>
