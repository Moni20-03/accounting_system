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
// $group_id = $_SESSION['group_id'];

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
if ($result->num_rows > 0) 
{
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
    <link rel="stylesheet" href="css/create-group.css"> 
        
</head>
<body>
    
    <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->
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
