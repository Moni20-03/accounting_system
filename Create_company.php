<?php
// Start session for form data persistence
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "finpack";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize inputs
    $_SESSION['form_data'] = $_POST; // Store form data in session temporarily
    $company_name = trim($_POST['company_name']);
    $company_address = trim($_POST['company_address']);
    $contact_email = trim($_POST['contact_email']);
    $contact_phone = trim($_POST['contact_phone']);
    $company_password = trim($_POST['company_password']);
    $books_begin_date = $_POST['books_begin_date'];

    $errors = [];

    // Validate inputs
    if (empty($company_name)) {
        $errors[] = "Company name is required.";
    }
    if (empty($company_address)) {
        $errors[] = "Company address is required.";
    }
    if (empty($company_password)) {
        $errors[] = "Company password is required.";
    }
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!empty($contact_phone) && !preg_match("/^\d{10,15}$/", $contact_phone)) {
        $errors[] = "Invalid phone number format.";
    }

    // Validate books begin date
    if (!empty($books_begin_date)) {
        $date_parts = explode('-', $books_begin_date);
        if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
            $errors[] = "Invalid date format for Books Beginning From Date.";
        } else {
            $current_year = date("Y");
            $year = (int)$date_parts[0];
            if ($year < 1900 || $year > $current_year + 10) { // Allow years only from 1900 to 10 years in the future
                $errors[] = "Books Beginning From Date year must be between 1900 and " . ($current_year + 10) . ".";
            }
        }
    } else {
        $errors[] = "Books Beginning From Date is required.";
    }

    // Check for duplicate company name
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM company_details WHERE company_name = ?");
        $stmt->bind_param("s", $company_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $errors[] = "Company name already exists.";
        }
    }

    // If no errors, insert into the database
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO company_details (company_name, company_address, contact_email, contact_phone, company_password, books_begin_date)
            VALUES (?, ?, ?, ?, ? ,?)
        ");
        $hashed_password = password_hash($company_password, PASSWORD_BCRYPT);
        // $hashed_secret = password_hash($secret_code, PASSWORD_BCRYPT);
        $stmt->bind_param("ssssss", $company_name, $company_address, $contact_email, $contact_phone, $hashed_password,$books_begin_date);

        if ($stmt->execute()) {
            // Clear session data after successful submission
            unset($_SESSION['form_data']);
            echo "<script>alert('Company Created Successfully'); window.location.href='login.php';</script>";
        } else {
            echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Company</title>
    <link rel="stylesheet" href="css/create_company_style.css">
</head>
<body>
    <div class="animated-background"></div>
    <div class="create-company-container">
        <h1>Create Company</h1>

        <!-- Error Message Section -->
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Create Company Form -->
        <form action="Create_company.php" method="POST" class="create-company-form">
            <label for="company_name">Company Name:</label>
            <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($_SESSION['form_data']['company_name'] ?? ''); ?>" required>

            <label for="company_address">Company Address:</label>
            <textarea id="company_address" name="company_address" rows="3" required><?= htmlspecialchars($_SESSION['form_data']['company_address'] ?? ''); ?></textarea>

            <label for="contact_email">Contact Email:</label>
            <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($_SESSION['form_data']['contact_email'] ?? ''); ?>">

            <label for="contact_phone">Contact Phone:</label>
            <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($_SESSION['form_data']['contact_phone'] ?? ''); ?>">

            <label for="company_password">Password:</label>
            <input type="password" id="company_password" name="company_password" required>

            <label for="books_begin_date">Books Beginning From Date:</label>
            <input type="date" id="books_begin_date" name="books_begin_date" value="<?= htmlspecialchars($_SESSION['form_data']['books_begin_date'] ?? ''); ?>" required>

            <button type="submit" class="create-company-button">Submit</button>
        </form>
    </div>
</body>
</html>
