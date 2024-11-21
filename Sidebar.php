<?php
session_start();
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
    <style>
        /* Reset and General Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #001f3f;
    color: #fff;
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* Sidebar Styling */
.sidebar {
    background-color: #003366;
    width: 250px;
    height: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
}

.user-info {
    text-align: center;
    margin-bottom: 20px;
}

.user-info p {
    margin: 5px 0;
    color: #66b3ff;
    font-size: 0.9rem;
}

hr {
    border: 1px solid #005580;
    margin: 15px 0;
}

/* Menu Styling */
.menu {
    list-style: none;
    padding: 0;
}

.menu li {
    margin-bottom: 10px;
}

.menu a {
    display: block;
    padding: 12px 15px;
    font-size: 1rem;
    color: #fff;
    text-decoration: none;
    background-color: #004080;
    border-radius: 5px;
    transition: background-color 0.3s, transform 0.3s;
}

.menu a:hover {
    background-color: #005bb5;
    transform: translateX(5px);
}

/* Active Menu Link (Optional) */
.menu a.active {
    background-color: #0066cc;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
        padding: 15px;
    }

    .menu a {
        font-size: 0.9rem;
    }
}

        </style>
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
            <li><a href="sales.php">Accouting Vouchers</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="display.php">Display</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</body>
</html>
