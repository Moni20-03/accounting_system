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
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reset and General Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f0f0;
    color: #333;
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* Main Container */
.container-wrapper {
    display: flex;
    width: 100%;
    height: 100%;
}

/* Sidebar Styling */
.sidebar {
    background-color: #003366;
    width: 250px;
    height: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
    z-index: 100;
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

/* Main Content Styling */
.main-content {
    margin-left: 250px; /* Keep space for sidebar */
    flex-grow: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    overflow-y: auto;
}

/* Container for Account Info Menu */
.container {
    width: 100%;
    max-width: 1200px; /* Limit width for large screens */
    margin: 0 auto;
    text-align: center;
}

/* Button Styles */
button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    margin: 10px;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background-color: #0056b3;
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

    .main-content {
        margin-left: 200px;
    }

    .container {
        padding: 20px;
    }
}

    </style>
</head>
<body>

    <!-- Wrapper for Sidebar and Main Content -->
    <div class="container-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <p><strong>User:</strong> <?= htmlspecialchars($username); ?></p>
                <p><strong>Company:</strong> <?= htmlspecialchars($company_name); ?></p>
            </div>
            <hr>
            <ul class="menu">
                <li><a href="account-info.php">Account Info</a></li>
                <li><a href="sales.php">Accounting Vouchers</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="display.php">Display</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Account Info Page Content -->
            <div class="container" id="account-info">
                <div id="main-menu">
                    <h2>Account Info</h2>
                    <button onclick="showSubMenu('groups-menu')">Groups</button>
                    <button onclick="showSubMenu('ledgers-menu')">Ledgers</button>
                    <!-- <button onclick="goBack()">Back</button> -->
                </div>

                <!-- Submenu for Groups -->
                <div id="groups-menu" class="submenu" style="display: none;">
                    <h3>Groups</h3>
                    <a href="create_group.php"><button>Create</button></a>
                    <a href="display_group.php"><button>Display</button></a>
                    <a href="alter_group.php"><button>Alter</button></a>
                    <button onclick="goBack()">Back</button>
                </div>

                <!-- Submenu for Ledgers -->
                <div id="ledgers-menu" class="submenu" style="display: none;">
                    <h3>Ledgers</h3>
                    <a href="create_ledger.php"><button>Create</button></a>
                    <a href="display_ledgers.php"><button>Display</button></a>
                    <a href="alter_ledger.php"><button>Alter</button></a>
                    <button onclick="goBack()">Back</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSubMenu(menuId) {
            document.querySelectorAll('.submenu').forEach(menu => menu.style.display = 'none');
            document.getElementById('main-menu').style.display = 'none';
            document.getElementById(menuId).style.display = 'block';
        }

        function goBack() {
            document.querySelectorAll('.submenu').forEach(menu => menu.style.display = 'none');
            document.getElementById('main-menu').style.display = 'block';
        }
    </script>
</body>
</html>
