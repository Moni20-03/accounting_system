<?php
session_start();
include('db_connection.php');

// Fetch company ID from session
$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    echo "<script>alert('Session expired. Please log in again.');</script>";
    exit;
}

// Fetch all ledgers for the given company with the group name
$query = "SELECT 
            ledgers.ledger_id,
            ledgers.ledger_name,
            ledgers.group_id,
            ledgers.opening_balance,
            groups.group_name
          FROM ledgers
          JOIN groups ON ledgers.group_id = groups.group_id
          WHERE ledgers.company_id = '$company_id'";

$result = $conn->query($query);
$ledgers = [];
while ($row = $result->fetch_assoc()) {
    $ledgers[] = $row;
}

// Fetch ledger details if a specific ledger is selected
$ledger_details = null;
if (isset($_GET['ledger_id'])) {
    $ledger_id = (int) $_GET['ledger_id'];
    $query = "SELECT 
                ledgers.ledger_name,
                ledgers.group_id,
                ledgers.opening_balance,
                ledgers.current_balance,
                ledgers.debit_credit,
                groups.group_name
              FROM ledgers
              JOIN groups ON ledgers.group_id = groups.group_id
              WHERE ledgers.ledger_id = '$ledger_id' AND ledgers.company_id = '$company_id'";
    $result = $conn->query($query);
    $ledger_details = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger List</title>
    <link rel="stylesheet" href="css/display_ledger.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->

    <div class="main-content">
        <div class="form-container">
            <h2>Ledgers</h2>

            <!-- Table to display all ledgers -->
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Ledger Name</th>
                        <th>Group</th>
                        <th>Opening Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ledgers as $ledger): ?>
                        <tr>
                            <td><?= htmlspecialchars($ledger['ledger_name']); ?></td>
                            <td><?= htmlspecialchars($ledger['group_name']); ?></td> <!-- Display the group name -->
                            <td><?= number_format($ledger['opening_balance'], 2); ?></td>
                            <td><a href="?ledger_id=<?= $ledger['ledger_id']; ?>">View Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- If a specific ledger is selected, show its details -->
            <?php if ($ledger_details): ?>
                <h2>Ledger Details</h2>
                <div class="ledger-details">
                    <p><strong>Ledger Name:</strong> <?= htmlspecialchars($ledger_details['ledger_name']); ?></p>
                    <p><strong>Group:</strong> <?= htmlspecialchars($ledger_details['group_name']); ?></p> <!-- Show the group name -->
                    <p><strong>Opening Balance:</strong> <?= number_format($ledger_details['opening_balance'], 2); ?></p>
                    <p><strong>Current Balance:</strong> <?= number_format($ledger_details['current_balance'], 2); ?></p>
                    <p><strong>Type:</strong> <?= htmlspecialchars($ledger_details['debit_credit']); ?></p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
