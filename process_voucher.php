<?php
session_start();
include('db_connection.php');

$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    echo "<script>alert('Session expired. Please log in again.');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucher_type = $_POST['voucher_type'];
    $voucher_number = $_POST['voucher_number'];
    $date = $_POST['date'];
    $amount = (float)$_POST['amount'];
    $narration = $_POST['narration'] ?? '';
    $debit_ledger_id = $_POST['debit_ledger_id'] ?? null;
    $credit_ledger_id = $_POST['credit_ledger_id'] ?? null;

    if ($debit_ledger_id === $credit_ledger_id) {
        die("Debit and Credit Ledgers cannot be the same.");
    }

    $sql = "INSERT INTO vouchers (company_id, voucher_type, voucher_number, date, amount, narration, debit_ledger_id, credit_ledger_id)
            VALUES ('$company_id', '$voucher_type', '$voucher_number', '$date', $amount, '$narration', '$debit_ledger_id', '$credit_ledger_id')";
    if ($conn->query($sql)) {
        $conn->query("UPDATE ledgers SET current_balance = current_balance + $amount WHERE ledger_id = '$debit_ledger_id'");
        $conn->query("UPDATE ledgers SET current_balance = current_balance - $amount WHERE ledger_id = '$credit_ledger_id'");
        echo "<script>alert('Voucher created successfully.'); window.location.href='voucher_display.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
