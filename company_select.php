<?php
session_start();
include('db_connection.php');

// Check if user is logged in (for example, via session)
if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_id = $_POST['company_id'];
    $entered_password = $_POST['company_password'];  // Use the correct input name for password

    // Fetch the company password from the database (no secret_code field anymore)
    $stmt = $conn->prepare("SELECT company_password FROM company_details WHERE company_id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $stmt->bind_result($company_password);  // Only fetch company_password now
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if (password_verify($entered_password, $company_password)) {
        // Store company ID in session
        $_SESSION['company_id'] = $company_id;
        echo "<script>alert('Access Granted'); window.location.href='company_dashboard.php';</script>";
    } else {
        echo "<script>alert('Access Denied! Invalid Password');</script>";
    }
}

// Fetch the list of companies for selection
$stmt = $conn->prepare("SELECT company_id, company_name FROM company_details");
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Company</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="company-selection-container">
        <h1>Select Company</h1>

        <!-- Display list of companies -->
        <form action="company_select.php" method="POST">
            <label for="company_id">Select a Company:</label>
            <select id="company_id" name="company_id" required>
                <option value="">-- Select Company --</option>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['company_id']; ?>"><?= htmlspecialchars($row['company_name']); ?></option>
                <?php endwhile; ?>
            </select>

            <br><br>

            <!-- Password input for security verification -->
            <div id="password-section" style="display:none;">
                <label for="company_password">Enter Password:</label>
                <input type="password" id="company_password" name="company_password" required>
                <button type="submit">Submit</button>
            </div>

            <br>
            <button type="button" id="create-company-btn" onclick="window.location.href='Create_company.php'">Create Company</button>
        </form>
    </div>

    <script>
        // Show the password input field when a company is selected
        document.getElementById('company_id').addEventListener('change', function() {
            var companyId = this.value;
            var passwordSection = document.getElementById('password-section');
            if (companyId) {
                passwordSection.style.display = 'block';
            } else {
                passwordSection.style.display = 'none';
            }
        });
    </script>
</body>
</html>
