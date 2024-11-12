<?php
session_start(); // Start the session to handle login state

// Database connection
$servername = "localhost";
$username = "root"; // Update with your DB username
$password = "";     // Update with your DB password
$dbname = "finpack"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Check for user credentials from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($input_password, $user['password'])) {
            // Correct credentials, set session variable
            $_SESSION['username'] = $input_username;
            header("Location: Sidebar.html"); // Redirect to user dashboard
            exit();
        } else {
            // Incorrect password
            $error_message = "Invalid password.";
        }
    } else {
        // Username doesn't exist
        $error_message = "Username not found.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINPACK - Login</title>
    <link rel="stylesheet" href="login-styles.css">
</head>
<body>
    <div class="animated-background"></div>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-links">
                <a href="#" class="forgot-password">Forgot Password?</a>
                <a href="register.php" class="register-link">New User? Register</a>
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>
</html>
