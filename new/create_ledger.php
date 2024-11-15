<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'acc';
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Ledger</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Create New Ledger</h2>
    <form method="POST" action="">
        <label for="ledger_name">Ledger Name:</label>
        <input type="text" id="ledger_name" name="ledger_name" required>

        <label for="group_id">Group:</label>
        <select id="group_id" name="group_id" required>
            <?php
            $groupQuery = "SELECT group_id, group_name FROM Groups";
            $result = $conn->query($groupQuery);
            while ($group = $result->fetch_assoc()) {
                echo "<option value='{$group['group_id']}'>{$group['group_name']}</option>";
            }
            ?>
        </select>

        <label for="usage_type">Usage Type:</label>
        <select id="usage_type" name="usage_type" required>
            <option value="Debit">Debit</option>
            <option value="Credit">Credit</option>
        </select>

        <label for="balance_type">Balance Type:</label>
        <select id="balance_type" name="balance_type">
            <option value="Debit">Debit</option>
            <option value="Credit">Credit</option>
        </select>

        <label for="opening_balance">Opening Balance:</label>
        <input type="number" id="opening_balance" name="opening_balance" step="0.01" required>

        <button type="submit">Create Ledger</button>
    </form>
</body>
</html>
