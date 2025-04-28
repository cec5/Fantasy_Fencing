<?php
require_once(dirname(__DIR__).'/../backend/php/userFunctions.php');
require_once(dirname(__DIR__).'/../backend/php/dataArrays.php');

$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    	$username = trim($_POST['username']);
    	$email = trim($_POST['email']);
    	$nationality = trim($_POST['countryCode']);
    	$password = $_POST['password'];

    	$result = registerUser($username, $email, $nationality, $password);

    	if ($result === true) {
        	$message = "Registration successful! <a href='/account/login.php'>Click here to log in</a>.";
        	$success = true;
    	} else {
        	$message = $result;
        	$success = false;
    	}
}

include(dirname(__DIR__).'/common/header.php');
?>

<main class="container my-5">
    	<h2>Register</h2>

    	<?php if ($message): ?>
        	<div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> mt-3">
            		<?= $success ? $message : htmlspecialchars($message) ?>
        	</div>
    	<?php endif; ?>

    	<form id="registerForm" action="account/register.php" method="POST" class="needs-validation" novalidate>
        	<div class="mb-3">
            		<label for="username" class="form-label">Username</label>
            		<input type="text" class="form-control" id="username" name="username" required>
            		<div class="invalid-feedback">Please provide a username.</div>
        	</div>

        	<div class="mb-3">
            		<label for="email" class="form-label">Email</label>
            		<input type="email" class="form-control" id="email" name="email" required pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$">
            		<div class="invalid-feedback">Please provide a valid email address.</div>
        	</div>

        	<div class="mb-3">
            		<label for="nationality" class="form-label">Country</label>
            		<input type="text" class="form-control" id="nationality" list="countryList" autocomplete="off" required>
            		<datalist id="countryList"></datalist>
            		<input type="hidden" name="countryCode" id="countryCode">
            		<div class="invalid-feedback">Please select a valid country from the list.</div>
        	</div>

        	<div class="mb-3">
            		<label for="password" class="form-label">Password</label>
            		<input type="password" class="form-control" id="password" name="password" minlength="8" required>
            		<div class="invalid-feedback">Password must be at least 8 characters long.</div>
        	</div>

        	<div class="mb-3">
            		<label for="confirmPassword" class="form-label">Confirm Password</label>
            		<input type="password" class="form-control" id="confirmPassword" required>
            		<div id="passwordMismatchMsg" style="color: red; display: none;">Passwords do not match.</div>
        	</div>

        	<button type="submit" class="btn btn-primary" id="submitButton" disabled>Register</button>
    	</form>
</main>

<?php include(dirname(__DIR__).'/common/footer.php'); ?>

<!-- JavaScript Section -->
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

    	document.getElementById("registerForm").addEventListener("submit", function (e) {
        	const selectedCountry = countryInput.value;
        	const countryCode = Object.keys(countryCodeMap).find(code => countryCodeMap[code] === selectedCountry);
        	if (!countryCode) {
            		e.preventDefault();
            		alert("Please select a valid country from the list.");
            		return;
        	}
        	countryCodeInput.value = countryCode;
    	});

    	// Password Match Checker
    	function checkPasswordMatch() {
        	const password = document.getElementById("password").value;
        	const confirmPassword = document.getElementById("confirmPassword").value;
        	document.getElementById("submitButton").disabled = password.length < 8 || password !== confirmPassword;
        	document.getElementById("passwordMismatchMsg").style.display = password !== confirmPassword ? "block" : "none";
    	}

    	document.getElementById("password").addEventListener("input", checkPasswordMatch);
    	document.getElementById("confirmPassword").addEventListener("input", checkPasswordMatch);
    	checkPasswordMatch();
});
</script>

