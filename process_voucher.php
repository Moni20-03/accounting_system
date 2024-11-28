<?php
session_start();
include('db_connection.php');

$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    echo "<script>alert('Session expired. Please log in again.');</script>";
    exit;
}
$query = "SELECT company_name FROM company_details WHERE company_id = '$company_id'";
$result = $conn->query($query);
$company = $result->fetch_assoc();
$company_name = $company['company_name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucher_type = $_POST['voucher_type'];
    $voucher_number = $_POST['voucher_number'];
    $date = $_POST['date'];
    $amount = (float)$_POST['amount'];
    $narration = $_POST['narration'] ?? '';
    $payment_mode = $_POST['payment_mode'] ?? null;

    $debit_ledger_id = $_POST['debit_ledger_id'] ?? null;
    $credit_ledger_id = $_POST['credit_ledger_id'] ?? null;

    // Adjust for Sales and Purchase
    if ($voucher_type === 'Sales') {
        $debit_ledger_id = $_POST['party_ledger_id'];
        $credit_ledger_id = $_POST['sales_account_id'];
    } elseif ($voucher_type === 'Purchase') {
        $debit_ledger_id = $_POST['purchase_account_id'];
        $credit_ledger_id = $_POST['party_ledger_id'];
    }

    // Validate ledger existence
    $debit_exists = $conn->query("SELECT 1 FROM ledgers WHERE ledger_id = '$debit_ledger_id'");
    $credit_exists = $conn->query("SELECT 1 FROM ledgers WHERE ledger_id = '$credit_ledger_id'");

    if ($debit_exists->num_rows === 0 || $credit_exists->num_rows === 0) {
        die("Invalid ledger selected for Debit or Credit.");
    }

    // Insert voucher
    $sql = "INSERT INTO vouchers (company_id, voucher_type, voucher_number, date, amount, narration, payment_mode, debit_ledger_id, credit_ledger_id)
            VALUES ('$company_id', '$voucher_type', '$voucher_number', '$date', $amount, '$narration', 
            " . ($payment_mode ? "'$payment_mode'" : "NULL") . ", '$debit_ledger_id', '$credit_ledger_id')";

    if ($conn->query($sql)) {
        // Update ledger balances
        $conn->query("UPDATE ledgers SET current_balance = current_balance + $amount WHERE ledger_id = $debit_ledger_id");
        $conn->query("UPDATE ledgers SET current_balance = current_balance - $amount WHERE ledger_id = $credit_ledger_id");

        echo "Voucher created successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
