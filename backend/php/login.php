<?php 
include 'header.php'; 
include '../../backend/php/userFunctions.php';

// Process the login if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'];
    $password = $data['password'];

    // Call the loginUser function directly
    $response = loginUser($username, $password);

    // Return JSON response directly
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>
        
        <!-- Display messages here -->
        <div id="message" class="mt-3"></div>
        
        <form id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="handleLogin()">Login</button>
        </form>
    </div>

    <script>
        // Handle login form submission
        function handleLogin() {
            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;

            fetch("login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ username, password })
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById("message");
                if (data.success) {
                    // Store token as a cookie
                    document.cookie = `auth_token=${data.token}; path=/; max-age=3600`;
                    messageDiv.innerHTML = `<div class="alert alert-success">Login successful! <a href="index.php">Go to homepage</a></div>`;
                } else {
                    // Display error message
                    messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
</body>
</html>
