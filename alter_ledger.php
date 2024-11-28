<?php
session_start();
include('db_connection.php');

// Fetch company ID from session
$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    echo "<script>alert('Session expired. Please log in again.');</script>";
    exit;
}

// Fetch all ledgers for the company
$query = "SELECT 
            ledgers.ledger_id,
            ledgers.ledger_name,
            ledgers.group_id,
            ledgers.opening_balance,
            ledgers.debit_credit,
            groups.group_name
          FROM ledgers
          JOIN groups ON ledgers.group_id = groups.group_id
          WHERE ledgers.company_id = '$company_id'";

$result = $conn->query($query);

// Handle form submission to update the ledger
if (isset($_POST['submit'])) {
    $ledger_id = (int)$_POST['ledger_id'];
    $ledger_name = trim($conn->real_escape_string($_POST['ledger_name'] ?? ''));
    $group_id = (int)($_POST['group_id'] ?? 0);
    $debit_credit = $_POST['debit_credit'] ?? '';
    $opening_balance = (float)($_POST['opening_balance'] ?? 0.00);

    // Validation
    if (empty($ledger_name) || empty($group_id) || empty($debit_credit)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Update the ledger record in the database
        $update_sql = "UPDATE ledgers 
                       SET ledger_name = '$ledger_name', group_id = '$group_id', opening_balance = $opening_balance, debit_credit = '$debit_credit'
                       WHERE ledger_id = '$ledger_id' AND company_id = '$company_id'";

        if ($conn->query($update_sql) === TRUE) {
            echo "<script>alert('Ledger updated successfully!');</script>";
            // Refresh the page or redirect
            header('Location: alter_ledger.php');
            exit;
        } else {
            echo "<script>alert('Error updating ledger: " . $conn->error . "');</script>";
        }
    }
}

// If an individual ledger needs to be edited, fetch its details
if (isset($_GET['ledger_id'])) {
    $ledger_id = (int)$_GET['ledger_id'];
    $edit_query = "SELECT 
                    ledgers.ledger_id,
                    ledgers.ledger_name,
                    ledgers.group_id,
                    ledgers.opening_balance,
                    ledgers.debit_credit,
                    groups.group_name
                  FROM ledgers
                  JOIN groups ON ledgers.group_id = groups.group_id
                  WHERE ledgers.ledger_id = '$ledger_id' AND ledgers.company_id = '$company_id'";

    $edit_result = $conn->query($edit_query);
    $ledger_details = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledgers</title>
    <link rel="stylesheet" href="css/alter_ledger.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->

    <div class="main-content">
        <!-- Table of all ledgers -->
        <div class="table-container">
            <h2>All Ledgers</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Ledger Name</th>
                        <th>Group</th>
                        <th>Opening Balance</th>
                        <th>Debit/Credit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ledger = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($ledger['ledger_name']) ?></td>
                            <td><?= htmlspecialchars($ledger['group_name']) ?></td>
                            <td><?= number_format($ledger['opening_balance'], 2) ?></td>
                            <td><?= htmlspecialchars($ledger['debit_credit']) ?></td>
                            <td><a href="alter_ledger.php?ledger_id=<?= $ledger['ledger_id'] ?>">Edit Details</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Ledger Form -->
        <?php if (isset($ledger_details)) { ?>
            <div class="form-container">
                <h2>Edit Ledger</h2>
                <form method="post">
                    <input type="hidden" name="ledger_id" value="<?= $ledger_details['ledger_id'] ?>">

                    <label>Ledger Name:</label>
                    <input type="text" name="ledger_name" value="<?= htmlspecialchars($ledger_details['ledger_name']) ?>" required><br>

                    <label>Group:</label>
                    <select name="group_id" required>
                        <option value="">Select Group</option>
                        <?php
                        $group_query = "SELECT * FROM groups";
                        $group_result = $conn->query($group_query);
                        while ($group = $group_result->fetch_assoc()) {
                            $selected = ($group['group_id'] == $ledger_details['group_id']) ? 'selected' : '';
                            echo "<option value='{$group['group_id']}' {$selected}>{$group['group_name']}</option>";
                        }
                        ?>
                    </select><br>

                    <label>Opening Balance:</label>
                    <input type="number" name="opening_balance" step="0.01" value="<?= $ledger_details['opening_balance'] ?>"><br>

                    <div class="radio-buttons">
                        <label>
                            <input type="radio" name="debit_credit" value="Debit" <?= ($ledger_details['debit_credit'] == 'Debit') ? 'checked' : ''; ?> required> Debit
                        </label>
                        <label>
                            <input type="radio" name="debit_credit" value="Credit" <?= ($ledger_details['debit_credit'] == 'Credit') ? 'checked' : ''; ?> required> Credit
                        </label>
                    </div><br>

                    <button type="submit" name="submit">Update Ledger</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>
