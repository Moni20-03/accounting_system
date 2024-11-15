<?php
include 'db_connection.php';

$balanceQuery = "SELECT SUM(current_debit) AS totalDebit, SUM(current_credit) AS totalCredit FROM ledgers";
$balanceResult = $conn->query($balanceQuery);

if ($balanceResult && $row = $balanceResult->fetch_assoc()) {
    $totalDebit = $row['totalDebit'] ?? 0.00;
    $totalCredit = $row['totalCredit'] ?? 0.00;
    $difference = abs($totalDebit - $totalCredit);

    echo json_encode([
        "totalDebit" => number_format($totalDebit, 2),
        "totalCredit" => number_format($totalCredit, 2),
        "difference" => number_format($difference, 2),
        "isTally" => ($totalDebit === $totalCredit)
    ]);
} else {
    echo json_encode(["error" => "Failed to fetch balances"]);
}

$conn->close();
?>
