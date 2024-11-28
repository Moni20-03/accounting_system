<?php
session_start();
include('db_connection.php');

// Fetch company name from session
$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    echo "<script>alert('Session expired. Please log in again.');</script>";
    exit;
}
$query = "SELECT company_name FROM company_details WHERE company_id = '$company_id'";
$result = $conn->query($query);
$company = $result->fetch_assoc();
$company_name = $company['company_name'] ?? '';

// Fetch current total debit and credit balances dynamically
$query = "SELECT 
            SUM(CASE WHEN debit_credit = 'Debit' THEN opening_balance ELSE 0 END) AS total_debit,
            SUM(CASE WHEN debit_credit = 'Credit' THEN opening_balance ELSE 0 END) AS total_credit
          FROM ledgers WHERE company_id = '$company_id'";
$result = $conn->query($query);
$totals = $result->fetch_assoc();
$total_debit = $totals['total_debit'] ?? 0.00;
$total_credit = $totals['total_credit'] ?? 0.00;

if (isset($_POST['submit'])) {
    // Sanitize and validate inputs
    $ledger_name = trim($conn->real_escape_string($_POST['ledger_name'] ?? ''));
    $group_id = (int) ($_POST['group_id'] ?? 0);
    $debit_credit = $_POST['debit_credit'] ?? '';
    $opening_balance = (float) ($_POST['opening_balance'] ?? 0.00);

    // Validation
    if (empty($ledger_name) || empty($group_id) || empty($debit_credit)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Validate duplicate ledger name
        $check_sql = "SELECT * FROM ledgers WHERE ledger_name = '$ledger_name' AND company_id = '$company_id'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Ledger with the same name already exists for this company.');</script>";
        } else {
            // Insert ledger
            $sql = "INSERT INTO ledgers (company_id, ledger_name, group_id, opening_balance, current_balance, debit_credit)
                    VALUES ('$company_id', '$ledger_name', '$group_id', $opening_balance, $opening_balance, '$debit_credit')";

            if ($conn->query($sql) === TRUE) {
                // Update total balances dynamically
                $query = "SELECT 
                            SUM(CASE WHEN debit_credit = 'Debit' THEN opening_balance ELSE 0 END) AS total_debit,
                            SUM(CASE WHEN debit_credit = 'Credit' THEN opening_balance ELSE 0 END) AS total_credit
                          FROM ledgers WHERE company_id = '$company_id'";
                $result = $conn->query($query);
                $totals = $result->fetch_assoc();
                $total_debit = $totals['total_debit'] ?? 0.00;
                $total_credit = $totals['total_credit'] ?? 0.00;

                echo "<script>alert('Ledger created successfully!');</script>";
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ledger</title>
    <link rel="stylesheet" href="css/create-ledger.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->

    <div class="main-content">
        <div class="form-container">
            <center><h2>Create Ledger</h2></center>

            <!-- Show updated total balances -->
            <div class="total-balances">
                <h3>Total Debit: <?= number_format($total_debit, 2); ?></h3>
                <h3>Total Credit: <?= number_format($total_credit, 2); ?></h3>
            </div>

            <!-- Form to create ledger -->
            <form method="post">
                <label>Company:</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($company_name); ?>" readonly><br>

                <label>Ledger Name:</label>
                <input type="text" name="ledger_name" value="<?= $_POST['ledger_name'] ?? ''; ?>" required><br>

                <label>Group:</label>
                <select name="group_id" required>
                    <option value="">Select Group</option>
                    <?php
                    $result = $conn->query("SELECT * FROM groups");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['group_id']}'>{$row['group_name']}</option>";
                    }
                    ?>
                </select><br>

                <label>Opening Balance:</label>
                <input type="number" name="opening_balance" step="0.01" value="<?= $_POST['opening_balance'] ?? ''; ?>"><br>

                <div class="radio-buttons">
                <label>
                            <input type="radio" id="uni" name="debit_credit" value="Debit" required>
                            Debit
                        </label>
                        <label>
                            <input type="radio" name="debit_credit" value="Credit" required>
                            Credit
                        </label>
                    </div><br>


                <button type="submit" name="submit">Create</button>
            </form>
        </div>
    </div>
</body>

</html>
