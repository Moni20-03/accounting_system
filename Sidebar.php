<?php
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}
if (!isset($_SESSION['username']) || !isset($_SESSION['company_id'])) {
    header("Location: login.php"); // Redirect if not logged in or no company selected
    exit();
}


// Fetch the company name dynamically (optional)
include('db_connection.php');
$company_id = $_SESSION['company_id'];
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT company_name FROM company_details WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_name);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINPACK - Dashboard</title>
    <link rel="stylesheet" href="css/sidebarstyle.css">
</head>
<body>
    <div class="sidebar">
        <div class="user-info">
            <p><strong>User:</strong> <?= htmlspecialchars($username); ?></p>
            <p><strong>Company:</strong> <?= htmlspecialchars($company_name); ?></p>
        </div>
        <hr>

        <!-- Sidebar Links -->
        <ul class="menu">
            <li><a href="account-info.php">Account Info</a></li>
            <li><a href="type_selection.php">Accouting Vouchers</a></li>
            <li><a href="voucher_display.php">Display</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</body>
</html>
