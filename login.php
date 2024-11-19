<?php
include 'db_connection.php';
session_start(); // Start the session to handle login state
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
            header("Location:company_select.php"); // Redirect to user dashboard
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
    <style>
        
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #001f3f;
    position: relative;
    overflow: hidden;
}

.animated-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #004080, #001f3f);
    animation: gradientShift 6s infinite alternate;
    z-index: -1;
}

@keyframes gradientShift {
    0% { background-position: 0 0; }
    100% { background-position: 100% 100%; }
}

.login-container {
    background: rgba(0, 0, 0, 0.7);
    padding: 40px;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

h2 {
    margin-bottom: 20px;
    font-size: 1.8rem;
}

.login-form .form-group {
    position: relative;
    margin-bottom: 30px;
}

.login-form input {
    width: 100%;
    padding: 12px 10px;
    font-size: 1rem;
    color: #fff;
    background-color: transparent;
    border: 2px solid #004080;
    border-radius: 5px;
    transition: border-color 0.4s, box-shadow 0.4s;
}

.login-form input:focus {
    outline: none;
    border-color: #66b3ff;
    box-shadow: 0 0 10px rgba(102, 179, 255, 0.6);
}

.login-form input::placeholder {
    color: transparent; /* Ensure placeholder text is invisible */
}

.login-form label {
    position: absolute;
    left: 12px;
    top: 12px;
    font-size: 1rem;
    color: #bbb;
    pointer-events: none;
    transition: all 0.4s;
    /* background-color: ; */
    padding: 0 5px;
}

.login-form input:focus + label,
.login-form input:not(:placeholder-shown) + label {
    top: -18px;
    left: 5px;
    font-size: 0.85rem;
    color: #66b3ff;
}

/* Form links */
.form-links {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    margin-bottom: 20px;
}

.form-links a {
    color: #66b3ff;
    text-decoration: none;
    transition: color 0.3s;
}

.form-links a:hover {
    color: #99ccff;
}

/* Login button */
.login-button {
    background-color: #0066cc;
    color: #fff;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.3s;
    width: 100%;
}

.login-button:hover {
    background-color: #005bb5;
    transform: translateY(-3px);
}

    </style>
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
        <input type="text" id="username" name="username" placeholder=" " required>
        <label for="username">Username</label>
    </div>
    <div class="form-group">
        <input type="password" id="password" name="password" placeholder=" " required>
        <label for="password">Password</label>
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
