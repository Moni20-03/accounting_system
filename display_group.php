<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['company_id'])) {
    header("Location: login.php"); // Redirect if not logged in or no company selected
    exit();
}

// Fetch company and user details
include('db_connection.php');
$company_id = $_SESSION['company_id'];
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT company_name FROM company_details WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_name);
$stmt->fetch();
$stmt->close();

// Fetch all groups for dropdown
$sql = "SELECT group_id, group_name FROM Groups";
$result = $conn->query($sql);

$groups = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
}

$group_details = null;

// Check if a group is selected and fetch its details
if (isset($_POST['group_id'])) {
    $group_id = $_POST['group_id'];

    $details_sql = "SELECT * FROM Groups WHERE group_id = ?";
    $stmt = $conn->prepare($details_sql);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $group_details = $result->fetch_assoc();
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
    <title>Display Group - FINPACK</title>
    <style>
        /* General Reset */
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

        /* Sidebar Menu */
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

        /* Main Content Area */
        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
        }

        /* Form Styling */
        .form-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }

        label {
            font-size: 1rem;
            margin-bottom: 5px;
            text-align: left;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1rem;
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

            .form-container {
                padding: 15px;
                width: 90%;
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
            <div class="form-container">
                <h2>Display Group Details</h2>
                
                <!-- Group Selection Dropdown with Filter -->
                <form method="POST" action="display_group.php">
                    <label for="group_id">Select Group:</label>
                    <input type="text" id="groupSearch" onkeyup="filterGroups()" placeholder="Search for groups...">
                    <select id="group_id" name="group_id" required>
                        <option value="">Select a Group</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php echo $group['group_id']; ?>" <?php echo isset($group_id) && $group_id == $group['group_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($group['group_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Show Details</button>
                </form>

                <!-- Group Details Form (Read-Only) -->
                <?php if ($group_details): ?>
                    <h3>Group Information</h3>
                    <form>
                        <label for="group_name">Group Name:</label>
                        <input type="text" id="group_name" value="<?php echo htmlspecialchars($group_details['group_name']); ?>" readonly>

                        <label for="parent_group">Under Group:</label>
                        <input type="text" id="parent_group" 
                               value="<?php echo $group_details['parent_group_id'] ? htmlspecialchars($group_details['parent_group_id']) : 'Primary'; ?>" readonly>

                        <label for="group_type">Group Type:</label>
                        <input type="text" id="group_type" value="<?php echo htmlspecialchars($group_details['group_type']); ?>" readonly>

                        <label for="description">Description:</label>
                        <textarea id="description" readonly><?php echo htmlspecialchars($group_details['description']); ?></textarea>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // JavaScript function to filter dropdown options
        function filterGroups() {
            let input = document.getElementById("groupSearch").value.toUpperCase();
            let dropdown = document.getElementById("group_id");
            let options = dropdown.getElementsByTagName("option");

            for (let i = 0; i < options.length; i++) {
                let txtValue = options[i].text || options[i].innerText;
                options[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
            }
        }
    </script>

</body>
</html>
