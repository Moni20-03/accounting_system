<?php
// Connect to database
$servername = "localhost";
$username = "root";  // Adjust your DB username
$password = "";      // Adjust your DB password
$dbname = "finpack"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Registration logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate password (min 8 characters, max 8 characters, includes letters, numbers, and special characters)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8}$/', $password)) {
        $error_message = "Password must be exactly 8 characters long and include letters, numbers, and special characters.";
    } else {
        // Check if username already exists (case insensitive)
        $stmt = $conn->prepare("SELECT username FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Username already exists.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                // Redirect to login page with success message in the next page
                header("Location: login.php?message=Registered successfully");
                exit();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FINPACK</title>
    <style>
/* Reset and box sizing */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Page background and layout */
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

/* Animated background waves */
.animated-waves {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, #004080, #001f3f);
    opacity: 0.8;
    animation: waves 6s infinite linear;
    z-index: -1;
}

@keyframes waves {
    0% { background-position: 0 0; }
    50% { background-position: 100% 100%; }
    100% { background-position: 0 0; }
}

/* Success popup */
#success-popup {
    position: fixed;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #28a745;
    color: white;
    padding: 20px;
    font-size: 1.2rem;
    border-radius: 10px;
    display: none;
    z-index: 1000;
    transition: top 0.5s ease;
}

#success-popup.show {
    top: 20px;
}

/* Container styling for the registration form */
.register-container .container {
    text-align: center;
    background: rgba(0, 0, 0, 0.6);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 600px;
    margin: 0 20px;
}

.register-container h2 {
    font-size: 2.2rem;
    margin-bottom: 20px;
}

/* Error message styling */
.error-message {
    color: #ff4d4d;
    background-color: rgba(255, 0, 0, 0.1);
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

/* Form styling */
.register-form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.form-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 15px;
    width: 100%;
}

/* Label styling */
label {
    font-weight: bold;
    font-size: 1rem;
    color: white;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Input styling */
input[type="text"],
input[type="password"] {
    width: 100%; /* Consistent input width */
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 1rem;
    background-color: #001f3f; /* Matches theme */
    color: #fff;
    transition: border-color 0.3s, background-color 0.3s;
}

/* Focus and error effects on input */
input[type="text"]:focus,
input[type="password"]:focus {
    border-color: #007bff;
    background-color: #004080;
    outline: none;
}

input[type="text"].error,
input[type="password"].error {
    border-color: #ff4d4d;
    background-color: #2e2e2e;
}

input[type="text"].error:hover,
input[type="password"].error:hover {
    border-color: #ff6666;
    background-color: #3a3a3a;
}

/* Password requirements text */
.password-requirements {
    font-size: 0.8rem;
    color: #bbb;
    margin-top: 5px;
}

/* Button styling with hover and transition */
.animated-button {
    display: inline-block;
    padding: 12px 25px;
    color: #fff;
    font-size: 1rem;
    font-weight: bold;
    text-decoration: none;
    background-color: #0066cc;
    border-radius: 30px;
    transition: transform 0.3s, box-shadow 0.3s;
    box-shadow: 0 5px 15px rgba(0, 102, 204, 0.4);
    margin-top: 20px;
    border: none; /* Removed black border */
    text-align: center; /* Center the button */
}

/* Button hover effects */
.animated-button:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 102, 204, 0.6);
}

    </style>
</head>
<body>
    <!-- Success Popup -->
    <div id="success-popup">Registered successfully!</div>
    
    <div class="register-container">
        <div class="container">
            <h2>Register</h2>
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST" class="register-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password must be exactly 8 characters, including letters, numbers, and special characters.</small>
                </div>
                <button type="submit" class="animated-button">Register</button>
            </form>
        </div>
    </div>

    <script>
        // Show success message when redirected from successful registration
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('message') === 'Registered successfully') {
            const popup = document.getElementById('success-popup');
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.top = '20px'; // Slide down effect
            }, 10); // Short delay for smooth transition
            setTimeout(() => {
                window.location.href = 'login.php'; // Redirect after popup
            }, 3000); // Wait for the popup to show before redirecting
        }
    </script>
</body>
</html>
