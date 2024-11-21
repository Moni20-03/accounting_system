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
$group_id = $_SESSION['group_id'];

$stmt = $conn->prepare("SELECT company_name FROM company_details WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_name);
$stmt->fetch();
$stmt->close();

// Fetch primary groups for dropdown
$sql = "SELECT group_id, group_name FROM Groups WHERE group_type = 'Primary'";
$result = $conn->query($sql);

$primary_groups = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $primary_groups[] = $row;
    }
}

$error_message = "";
$success_message = "";

// Insert group data into database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = $_POST['group_name'];
    $parent_group_id = $_POST['parent_group_id'] ? $_POST['parent_group_id'] : NULL;
    $description = $_POST['description'];
    $group_type = $_POST['group_type'];

    // Check for duplicate group name
    $check_sql = "SELECT COUNT(*) as count FROM Groups WHERE group_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $group_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();

    if ($row['count'] > 0) {
        $error_message = "A group with this name already exists. Please choose a different name.";
    } else {
        // Insert if no duplicate found
        $stmt = $conn->prepare("INSERT INTO Groups (group_name, parent_group_id, description, group_type, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("siss", $group_name, $parent_group_id, $description, $group_type);

        if ($stmt->execute()) {
            $success_message = "Group created successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group - FINPACK</title>
    <!-- <link rel="stylesheet" href="style.css"> Make sure this is the correct path for your global styles -->
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

        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
        }

        /* Centered Form Container */
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

        .error-message, .success-message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }

        .error-message {
            background-color: #ffcccc;
            color: #d9534f;
        }

        .success-message {
            background-color: #d4edda;
            color: #28a745;
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
            <!-- Form to Create Group -->
            <div class="form-container">
                <h2>Create a New Group</h2>
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php elseif ($success_message): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <form method="POST" action="create_group.php">
                    <label for="group_name">Group Name:</label>
                    <input type="text" id="group_name" name="group_name" required>

                    <label for="group_type">Group Type:</label>
                    <select id="group_type" name="group_type" required onchange="toggleParentGroup(this)">
                        <option value="Primary">Primary</option>
                        <option value="Subgroup">Subgroup</option>
                    </select>

                    <div id="parent-group-container" style="display: none;">
                        <label for="parent_group_id">Under Group:</label>
                        <select id="parent_group_id" name="parent_group_id">
                            <option value="">Select Primary Group</option>
                            <?php foreach ($primary_groups as $group): ?>
                                <option value="<?php echo $group['group_id']; ?>"><?php echo $group['group_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>

                    <button type="submit">Create Group</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleParentGroup(select) {
            document.getElementById('parent-group-container').style.display = select.value === 'Subgroup' ? 'block' : 'none';
        }
    </script>
</body>
</html>
