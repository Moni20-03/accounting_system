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
    <title>Record Transaction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Record Transaction</h2>
    <form method="POST" action="">
        <label for="transaction_type">Transaction Type:</label>
        <select id="transaction_type" name="transaction_type" onchange="filterLedgers()" required>
            <option value="Receipt">Receipt</option>
            <option value="Payment">Payment</option>
            <option value="Sales">Sales</option>
            <option value="Purchase">Purchase</option>
            <option value="Journal">Journal</option>
            <option value="Contra">Contra</option>
        </select>

        <label for="debit_ledger_id">Debit Ledger:</label>
        <select id="debit_ledger_id" name="debit_ledger_id" required>
            <!-- Options populated dynamically -->
        </select>

        <label for="credit_ledger_id">Credit Ledger:</label>
        <select id="credit_ledger_id" name="credit_ledger_id" required>
            <!-- Options populated dynamically -->
        </select>

        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required>

        <button type="submit">Record Transaction</button>
    </form>

    <script>
        const debitLedgerDropdown = document.getElementById('debit_ledger_id');
        const creditLedgerDropdown = document.getElementById('credit_ledger_id');

        const filterLedgers = async () => {
            const transactionType = document.getElementById('transaction_type').value;

            // Fetch ledgers dynamically based on voucher type
            const response = await fetch('fetch_ledgers.php?type=' + transactionType);
            const { debitLedgers, creditLedgers } = await response.json();

            // Populate debit ledger dropdown
            debitLedgerDropdown.innerHTML = '';
            debitLedgers.forEach(ledger => {
                const option = document.createElement('option');
                option.value = ledger.ledger_id;
                option.text = ledger.ledger_name;
                debitLedgerDropdown.appendChild(option);
            });

            // Populate credit ledger dropdown
            creditLedgerDropdown.innerHTML = '';
            creditLedgers.forEach(ledger => {
                const option = document.createElement('option');
                option.value = ledger.ledger_id;
                option.text = ledger.ledger_name;
                creditLedgerDropdown.appendChild(option);
            });
        };

        // Initialize on page load
        filterLedgers();
    </script>
</body>
</html>
