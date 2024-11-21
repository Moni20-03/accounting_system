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
    $acc_type = $_POST['acc_type'] ?? '';
    $opening_balance = (float) ($_POST['opening_balance'] ?? 0.00);

    // Validation
    if (empty($ledger_name) || empty($group_id) || empty($acc_type)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Validate duplicate ledger name
        $check_sql = "SELECT * FROM ledgers WHERE ledger_name = '$ledger_name' AND company_id = '$company_id'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Ledger with the same name already exists for this company.');</script>";
        } else {
            // Determine Debit or Credit based on account type
            $debit_credit = in_array($acc_type, ['Asset', 'Expense']) ? 'Debit' : 'Credit';

            // Insert ledger
            $sql = "INSERT INTO ledgers (company_id, ledger_name, group_id, acc_type, opening_balance, current_balance, debit_credit)
                    VALUES ('$company_id', '$ledger_name', '$group_id', '$acc_type', $opening_balance, $opening_balance, '$debit_credit')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Ledger created successfully!');</script>";
                // Clear session values for a clean state
                $_SESSION['ledger_name'] = $_SESSION['group_id'] = $_SESSION['acc_type'] = $_SESSION['opening_balance'] = '';
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
    <style>
        /* Global Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f8fc;
    color: #333;
    margin: 0;
    padding: 0;
}

h2, h3 {
    color: #007bff;
}

h3 {
    font-size: 1.2em;
}

/* Form Container */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 50%;
    margin: 20px auto;
    box-sizing: border-box;
}

form h2 {
    text-align: center;
    margin-bottom: 20px;
}

form label {
    font-size: 1em;
    display: block;
    margin-bottom: 8px;
    color: #333;
}

form input, form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 1em;
}

form input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 1em;
}

form button {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.1em;
    width: 100%;
}

form button:hover {
    background-color: #0056b3;
}

/* Input fields with focus */
input:focus, select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Total Balance Section */
h3 {
    margin-top: 20px;
    font-size: 1.1em;
    font-weight: 500;
}

h3 span {
    color: #007bff;
}

/* Confirmation Dialog Styles (for JS alerts) */
button[type="submit"]:focus {
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

input[readonly], select[readonly] {
    background-color: #f7f7f7;
    cursor: not-allowed;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    form {
        width: 90%;
    }
    h3 {
        font-size: 1em;
    }
}

        </style>
</head>
<body>
    <h2>Create Ledger</h2>

    <!-- Show updated total balances -->
    <h3>Total Debit: <?= number_format($total_debit, 2); ?></h3>
    <h3>Total Credit: <?= number_format($total_credit, 2); ?></h3>

    <form method="post">
        <label>Company:</label>
        <input type="text" name="company_name" value="<?= htmlspecialchars($company_name); ?>" readonly><br>

        <label>Ledger Name:</label>
        <input type="text" name="ledger_name" value="<?= $_SESSION['ledger_name'] ?? ''; ?>" required><br>

        <label>Group:</label>
        <select name="group_id" required>
            <option value="">Select Group</option>
            <?php
            $result = $conn->query("SELECT * FROM groups");
            while ($row = $result->fetch_assoc()) {
                $selected = ($_SESSION['group_id'] == $row['group_id']) ? 'selected' : '';
                echo "<option value='{$row['group_id']}' $selected>{$row['group_name']}</option>";
            }
            ?>
        </select><br>

        <label>Account Type:</label>
        <select name="acc_type" required>
            <option value="">Select Account Type</option>
            <option value="Asset" <?= ($_SESSION['acc_type'] == 'Asset') ? 'selected' : ''; ?>>Asset</option>
            <option value="Liability" <?= ($_SESSION['acc_type'] == 'Liability') ? 'selected' : ''; ?>>Liability</option>
            <option value="Expense" <?= ($_SESSION['acc_type'] == 'Expense') ? 'selected' : ''; ?>>Expense</option>
            <option value="Income" <?= ($_SESSION['acc_type'] == 'Income') ? 'selected' : ''; ?>>Income</option>
        </select><br>

        <label>Opening Balance:</label>
        <input type="number" name="opening_balance" step="0.01" value="<?= $_SESSION['opening_balance'] ?? ''; ?>"><br>

        <button type="submit" name="submit">Create</button>
    </form>
</body>
</html>
