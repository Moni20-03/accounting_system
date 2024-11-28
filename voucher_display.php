<?php
include('db_connection.php');
// $voucher_type = $_GET['voucher_type'] ?? null;
$voucher_type = 'Receipt';

$vouchers = $conn->query("SELECT * FROM vouchers WHERE voucher_type = '$voucher_type'");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $voucher_type ?> Vouchers</title>
</head>
<body>
    <h2><?= $voucher_type ?> Vouchers</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Voucher Number</th>
                <th>Date</th>
                <th>Debit Ledger</th>
                <th>Credit Ledger</th>
                <th>Amount</th>
                <th>Narration</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $vouchers->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['voucher_number'] ?></td>
                    <td><?= $row['date'] ?></td>
                    <td><?= $row['debit_ledger_id'] ?></td>
                    <td><?= $row['credit_ledger_id'] ?></td>
                    <td><?= $row['amount'] ?></td>
                    <td><?= $row['narration'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
