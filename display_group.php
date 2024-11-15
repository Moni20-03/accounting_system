<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'finpack';
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <link rel="stylesheet" href="display_grp_style.css">
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
</head>
<body>
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
</body>
</html>
