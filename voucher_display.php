<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucher_type = $_POST['voucher_type'];
    $query = "SELECT * FROM vouchers WHERE voucher_type = '$voucher_type'";
    $result = $conn->query($query);

    // Export logic for the whole table
    if (isset($_POST['export_all'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="vouchers.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Voucher Number', 'Date', 'Debit Ledger', 'Credit Ledger', 'Amount', 'Narration']);
        
        // Fetch each row, get corresponding ledgers, and export them
        while ($row = $result->fetch_assoc()) {
            $debit_ledger_name = getLedgerName($conn, $row['debit_ledger_id']);
            $credit_ledger_name = getLedgerName($conn, $row['credit_ledger_id']);
            fputcsv($output, [
                $row['voucher_number'],
                $row['date'],
                $debit_ledger_name,
                $credit_ledger_name,
                $row['amount'],
                $row['narration']
            ]);
        }
        fclose($output);
        exit;
    }
    
    // Export logic for a specific row
    if (isset($_POST['export_row'])) {
        $voucher_id = $_POST['voucher_id'];
        $query_row = "SELECT * FROM vouchers WHERE voucher_id = '$voucher_id'";
        $row_result = $conn->query($query_row);
        $row = $row_result->fetch_assoc();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="voucher_' . $row['voucher_number'] . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Voucher Number', 'Date', 'Debit Ledger', 'Credit Ledger', 'Amount', 'Narration']);
        
        $debit_ledger_name = getLedgerName($conn, $row['debit_ledger_id']);
        $credit_ledger_name = getLedgerName($conn, $row['credit_ledger_id']);
        fputcsv($output, [
            $row['voucher_number'],
            $row['date'],
            $debit_ledger_name,
            $credit_ledger_name,
            $row['amount'],
            $row['narration']
        ]);
        
        fclose($output);
        exit;
    }
}

// Function to fetch ledger name by ledger ID
function getLedgerName($conn, $ledger_id) {
    $query = "SELECT ledger_name FROM ledgers WHERE ledger_id = '$ledger_id'";
    $result = $conn->query($query);
    $ledger = $result->fetch_assoc();
    return $ledger['ledger_name'];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Display Vouchers</title>
    <style>
    /* Reset and General Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}

/* Form Styling */
.voucher-form {
    max-width: 600px;
    width: 100%;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin-bottom: 30px;
}

.voucher-form label {
    display: block;
    margin-bottom: 10px;
    color: #003366;
    font-weight: bold;
    font-size: 1.1rem;
}

.voucher-form select {
    width: 100%;
    padding: 10px 15px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    color: #333;
    background-color: #f9f9f9;
}

.voucher-form button {
    padding: 10px 20px;
    margin: 5px;
    border: none;
    border-radius: 5px;
    background-color: #003366;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

.voucher-form button:hover {
    background-color: #005580;
}

/* Table Styling */
.voucher-table {
    width: 100%;
    max-width: 900px;
    border-collapse: collapse;
    margin: 20px 0;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.voucher-table th,
.voucher-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    font-size: 0.9rem;
    color: #333;
}

.voucher-table th {
    background-color: #003366;
    color: white;
    text-transform: uppercase;
}

.voucher-table tr:hover {
    background-color: #f4f4f4;
}

.voucher-table tbody tr:last-child td {
    border-bottom: none;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .voucher-form, .voucher-table {
        width: 100%;
    }

    .voucher-form label,
    .voucher-form select,
    .voucher-form button {
        font-size: 1rem;
    }

    .voucher-table th,
    .voucher-table td {
        font-size: 0.8rem;
        padding: 8px 10px;
    }
}

</style>
</head>
<body>
<form method="POST" class="voucher-form">
    <label for="voucher_type">Select Voucher Type:</label>
    <select id="voucher_type" name="voucher_type" required>
        <option value="Receipt">Receipt</option>
        <option value="Payment">Payment</option>
        <option value="Contra">Contra</option>
        <option value="Sales">Sales</option>
        <option value="Purchase">Purchase</option>
    </select>
    <button type="submit">Display</button>
    <button type="submit" name="export_all">Export All</button>
</form>

<?php if (isset($result)): ?>
    <table class="voucher-table">
        <thead>
            <tr>
                <th>Voucher Number</th>
                <th>Date</th>
                <th>Debit Ledger</th>
                <th>Credit Ledger</th>
                <th>Amount</th>
                <th>Narration</th>
                <th>Export</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    // Get Debit and Credit Ledger Names
                    $debit_ledger_name = getLedgerName($conn, $row['debit_ledger_id']);
                    $credit_ledger_name = getLedgerName($conn, $row['credit_ledger_id']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['voucher_number']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($debit_ledger_name) ?></td>
                    <td><?= htmlspecialchars($credit_ledger_name) ?></td>
                    <td><?= htmlspecialchars($row['amount']) ?></td>
                    <td><?= htmlspecialchars($row['narration']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="voucher_id" value="<?= $row['voucher_id'] ?>">
                            <button type="submit" name="export_row">Export</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>
</body>
</html>
