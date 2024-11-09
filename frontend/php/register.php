<?php 
include 'header.php';
include '../../backend/php/dataArrays.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    	<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<title>Register</title>
    	<link href="../css/bootstrap.min.css" rel="stylesheet">
    	<script>
        	document.addEventListener("DOMContentLoaded", function () {
            		const countryInput = document.getElementById("nationality");
            		const countryList = document.getElementById("countryList");

            		// Populate the dropdown list dynamically
            		const validCountries = <?= json_encode(array_values($validCountryCodes)) ?>;
            		const countryCodeMap = <?= json_encode($validCountryCodes) ?>;

            		// Filter countries as user types
            		countryInput.addEventListener("input", function () {
                		const query = countryInput.value.toLowerCase();
                		countryList.innerHTML = "";
                		validCountries
                    		.filter(country => country.toLowerCase().includes(query))
                    		.forEach(country => {
                        		const option = document.createElement("option");
                        		option.value = country;
                        		countryList.appendChild(option);
                    		});
            		});

            		// Ensure selected country is valid before form submission
            		document.getElementById("registerForm").addEventListener("submit", function (e) {
                		const selectedCountry = countryInput.value;
                		const countryCode = Object.keys(countryCodeMap).find(code => countryCodeMap[code] === selectedCountry);
                		if (!countryCode) {
                    			e.preventDefault();
                    			alert("Please select a valid country from the list.");
                    			return;
                		}
                		document.getElementById("countryCode").value = countryCode;
            		});
        	});

        	// Password confirmation check
        	function checkPasswordMatch() {
            		const password = document.getElementById("password").value;
            		const confirmPassword = document.getElementById("confirmPassword").value;
            		document.getElementById("submitButton").disabled = password.length < 8 || password !== confirmPassword;
            		document.getElementById("passwordMismatchMsg").style.display = password !== confirmPassword ? "block" : "none";
        	}
    	</script>
</head>
<body>
    	<div class="container mt-5">
        	<h2>Register</h2>
        	<?php
        	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            		include '../../backend/php/userFunctions.php';
            		$username = $_POST['username'];
            		$email = $_POST['email'];
            		$nationality = $_POST['countryCode'];
            		$password = $_POST['password'];
            		$result = registerUser($username, $email, $nationality, $password);
            		if ($result === true) {
                		echo "<div class='alert alert-success mt-3'>Registration successful! <a href='login.php'>Click here to log in</a>.</div>";
            		} else {
                		echo "<div class='alert alert-danger mt-3'>" . htmlspecialchars($result) . "</div>";
            		}
        	}
        	?>
        	<form id="registerForm" action="register.php" method="POST" class="needs-validation" novalidate>
            		<div class="mb-3">
                		<label for="username" class="form-label">Username</label>
                		<input type="text" class="form-control" id="username" name="username" required>
                		<div class="invalid-feedback">Please provide a username.</div>
            		</div>
		    	<div class="mb-3">
		        	<label for="email" class="form-label">Email</label>
		        	<input type="email" class="form-control" id="email" name="email" required pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$">
		        	<div class="invalid-feedback">Please provide a valid email.</div>
		    	</div>
		    	<div class="mb-3">
		        	<label for="nationality" class="form-label">Country</label>
		        	<input type="text" class="form-control" id="nationality" list="countryList" autocomplete="off" required>
		        	<datalist id="countryList"></datalist>
		        	<input type="hidden" name="countryCode" id="countryCode">
		        	<div class="invalid-feedback">Please select a valid country.</div>
		    	</div>
		    	<div class="mb-3">
		        	<label for="password" class="form-label">Password</label>
		        	<input type="password" class="form-control" id="password" name="password" minlength="8" required onkeyup="checkPasswordMatch()">
		        	<div class="invalid-feedback">Password must be at least 8 characters.</div>
		    	</div>
		    	<div class="mb-3">
		        	<label for="confirmPassword" class="form-label">Confirm Password</label>
		        	<input type="password" class="form-control" id="confirmPassword" required onkeyup="checkPasswordMatch()">
		        	<div id="passwordMismatchMsg" style="color: red; display: none;">Passwords do not match.</div>
		    	</div>
		    	<button type="submit" class="btn btn-primary" id="submitButton" disabled>Register</button>
        	</form>
    	</div>
</body>
</html>
