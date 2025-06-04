<?php
require_once(dirname(__DIR__) . '/common/validation.php');
require_once(dirname(__DIR__) . '/../backend/php/userFunctions.php');

$message = '';
$success = false;

// Ensure user is logged in and fetch user info
if (!isset($userId)) {
	header("Location: /account/login.php");
	exit();
}

$userInfo = getUserInfo($userId);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['updateUsername'])) {
		$newUsername = trim($_POST['username']);
		$result = !empty($newUsername) ? updateUsername($userId, $newUsername) : "Username cannot be empty.";
	} elseif (isset($_POST['updateEmail'])) {
		$newEmail = $_POST['email'];
		$result = filter_var($newEmail, FILTER_VALIDATE_EMAIL) ? updateEmail($userId, $newEmail) : "Invalid email format.";
	} elseif (isset($_POST['updateNationality'])) {
		$newNationality = $_POST['countryCode'];
		$result = !empty($newNationality) ? updateNationality($userId, $newNationality) : "Please select a valid nationality.";
	} elseif (isset($_POST['updatePassword'])) {
		$currentPassword = $_POST['currentPassword'];
		$newPassword = $_POST['newPassword'];
		$result = updateUserPassword($userId, $currentPassword, $newPassword);
	}

	if ($result === true) {
		header("Location: /account/profile.php?message=" . urlencode("Profile successfully updated.") . "&success=1");
		exit();
	} else {
		$message = $result;
		$success = false;
	}
}

// Handle flash message from redirects
if (isset($_GET['message']) && isset($_GET['success'])) {
	$message = $_GET['message'];
	$success = $_GET['success'] == '1';
}

include(dirname(__DIR__) . '/common/header.php');
?>

<main class="container my-5">
	<h2>Profile: <?= htmlspecialchars($userInfo['username']) ?></h2>

	<?php if ($message): ?>
		<div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
			<?= htmlspecialchars($message) ?>
		</div>
	<?php endif; ?>

	<!-- Form to update username -->
	<form action="account/profile.php" method="POST">
		<div class="mb-3">
			<label class="form-label">Current Username: <?= htmlspecialchars($userInfo['username']) ?></label>
			<input type="text" class="form-control" name="username" placeholder="New Username">
		</div>
		<button type="submit" class="btn btn-primary" name="updateUsername">Update Username</button>
	</form>

	<hr>

	<!-- Form to update email -->
	<form action="account/profile.php" method="POST">
		<div class="mb-3">
			<label class="form-label">Current Email: <?= htmlspecialchars($userInfo['email']) ?></label>
			<input type="email" class="form-control" name="email" placeholder="New Email">
		</div>
		<button type="submit" class="btn btn-primary" name="updateEmail">Update Email</button>
	</form>

	<hr>

	<!-- Form to update nationality -->
	<form action="account/profile.php" method="POST">
		<div class="mb-3">
			<label class="form-label">Current Nationality:
				<?= htmlspecialchars($validCountryCodes[$userInfo['nationality']] ?? $userInfo['nationality']) ?></label>
			<input type="text" class="form-control" id="nationality" list="countryList" autocomplete="off"
				placeholder="New Nationality">
			<datalist id="countryList"></datalist>
			<input type="hidden" name="countryCode" id="countryCode">
		</div>
		<button type="submit" class="btn btn-primary" name="updateNationality">Update Nationality</button>
	</form>

	<hr>

	<!-- Form to update password -->
	<form action="account/profile.php" method="POST">
		<div class="mb-3">
			<label for="currentPassword" class="form-label">Current Password</label>
			<input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
		</div>
		<div class="mb-3">
			<label for="newPassword" class="form-label">New Password</label>
			<input type="password" class="form-control" id="newPassword" name="newPassword" minlength="8" required>
		</div>
		<div class="mb-3">
			<label for="confirmPassword" class="form-label">Confirm New Password</label>
			<input type="password" class="form-control" id="confirmPassword" required>
			<div id="passwordMismatchMsg" style="color: red; display: none;">Passwords do not match.</div>
		</div>
		<button type="submit" class="btn btn-primary" name="updatePassword" id="submitPasswordButton" disabled>Update
			Password</button>
	</form>
</main>

<?php include(dirname(__DIR__) . '/common/footer.php'); ?>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		const countryInput = document.getElementById("nationality");
		const countryList = document.getElementById("countryList");
		const countryCodeInput = document.getElementById("countryCode");
		const validCountries = <?= json_encode(array_values($validCountryCodes)) ?>;
		const countryCodeMap = <?= json_encode($validCountryCodes) ?>;

		countryInput.addEventListener("input", function () {
			const query = countryInput.value.toLowerCase();
			countryList.innerHTML = "";
			validCountries.filter(country => country.toLowerCase().includes(query)).forEach(country => {
				const option = document.createElement("option");
				option.value = country;
				countryList.appendChild(option);
			});
		});

		countryInput.addEventListener("change", function () {
			const selectedCountry = countryInput.value;
			const countryCode = Object.keys(countryCodeMap).find(code => countryCodeMap[code] === selectedCountry);
			countryCodeInput.value = countryCode || "";
		});

		function checkPasswordMatch() {
			const newPassword = document.getElementById("newPassword").value;
			const confirmPassword = document.getElementById("confirmPassword").value;
			const button = document.getElementById("submitPasswordButton");

			button.disabled = newPassword.length < 8 || newPassword !== confirmPassword;
			document.getElementById("passwordMismatchMsg").style.display = newPassword !== confirmPassword ? "block" : "none";
		}

		document.getElementById("newPassword").addEventListener("input", checkPasswordMatch);
		document.getElementById("confirmPassword").addEventListener("input", checkPasswordMatch);
		checkPasswordMatch();
	});
</script>