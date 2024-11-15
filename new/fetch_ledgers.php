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

$type = $_GET['type'];

$debitLedgers = [];
$creditLedgers = [];

switch ($type) {
    case 'Receipt':
        $creditLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Credit'")->fetch_all(MYSQLI_ASSOC);
        $debitLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Debit'")->fetch_all(MYSQLI_ASSOC);
        break;

    case 'Payment':
        $creditLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Credit'")->fetch_all(MYSQLI_ASSOC);
        $debitLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Debit'")->fetch_all(MYSQLI_ASSOC);
        break;

    case 'Contra':
        $creditLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Credit'")->fetch_all(MYSQLI_ASSOC);
        $debitLedgers = $creditLedgers; // For Contra, both ledgers are "Credit"
        break;

    case 'Sales':
    case 'Purchase':
    case 'Journal':
        $creditLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Credit'")->fetch_all(MYSQLI_ASSOC);
        $debitLedgers = $conn->query("SELECT * FROM Ledgers WHERE usage_type = 'Debit'")->fetch_all(MYSQLI_ASSOC);
        break;
}

echo json_encode(['debitLedgers' => $debitLedgers, 'creditLedgers' => $creditLedgers]);
