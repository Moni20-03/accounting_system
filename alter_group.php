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
$query = "SELECT group_id, group_name FROM Groups";
$result = $conn->query($query);
$groups = $result->fetch_all(MYSQLI_ASSOC);

// Initialize variables
$group_id = '';
$group_details = [];

// Handle form submission to load selected group details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_id'])) {
    $group_id = $_POST['group_id'];
    $stmt = $conn->prepare("SELECT * FROM Groups WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $group_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle form submission to update group details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_group'])) {
    $group_id = $_POST['group_id'];
    $group_name = $_POST['group_name'];
    $parent_group_id = $_POST['parent_group_id'] ? $_POST['parent_group_id'] : NULL;
    $group_type = $_POST['group_type'];
    $description = $_POST['description'];

    // Update query
    $stmt = $conn->prepare("UPDATE Groups SET group_name = ?, parent_group_id = ?, group_type = ?, description = ?, updated_at = NOW() WHERE group_id = ?");
    $stmt->bind_param("sisii", $group_name, $parent_group_id, $group_type, $description, $group_id);
    if ($stmt->execute()) {
        echo "<script>alert('Group updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating group.');</script>";
    }
    $stmt->close();

    // Reload updated details
    $stmt = $conn->prepare("SELECT * FROM Groups WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $group_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alter Group - FINPACK</title>
    <link rel="stylesheet" href="css/display-group.css">
</head>
<body>

<?php include 'sidebar.php'; ?> <!-- Include Sidebar -->
    
    <div class="main-content">
        <div class="form-container fade-in">
            <h2>Alter Group Details</h2>
            
            <!-- Group Selection Dropdown -->
            <form method="POST" action="alter_group.php">
                <label for="group_id">Select Group:</label>
                <input type="text" id="groupSearch" onkeyup="filterGroups()" placeholder="Search for groups...">
                <select id="group_id" name="group_id" required onchange="this.form.submit()">
                    <option value="">Select a Group</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['group_id']; ?>" <?php echo isset($group_id) && $group_id == $group['group_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['group_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Group Details Form -->
            <?php if ($group_details): ?>
                <h3>Group Information</h3>
                <form method="POST" action="alter_group.php">
                    <input type="hidden" name="group_id" value="<?php echo $group_details['group_id']; ?>">

                    <label for="group_name">Group Name:</label>
                    <input type="text" id="group_name" name="group_name" value="<?php echo htmlspecialchars($group_details['group_name']); ?>" required>

                    <label for="parent_group_id">Under Group:</label>
                    <select id="parent_group_id" name="parent_group_id">
                        <option value="">Primary</option>
                        <?php foreach ($groups as $group): ?>
                            <?php if ($group['group_id'] != $group_details['group_id']): ?>
                                <option value="<?php echo $group['group_id']; ?>" <?php echo $group_details['parent_group_id'] == $group['group_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <label for="group_type">Group Type:</label>
                    <input type="text" id="group_type" name="group_type" value="<?php echo htmlspecialchars($group_details['group_type']); ?>" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($group_details['description']); ?></textarea>

                    <button type="submit" name="update_group">Update Group</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
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
