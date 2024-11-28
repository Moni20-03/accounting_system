<?php
session_start();
include('db_connection.php');

$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit;
}

// Fetch company details
$query = "SELECT company_name FROM company_details WHERE company_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
$company_name = $company['company_name'] ?? '';

// Check for voucher type
if (!isset($_GET['voucher_type'])) {
    header("Location: type_selection.php");
    exit;
}

$voucher_type = htmlspecialchars($_GET['voucher_type']);
$unique_id = uniqid($voucher_type . '-');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create <?= $voucher_type ?> Voucher</title>
    <link rel="stylesheet" href="css/create_voucher.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->
    <div class="main-content">
        <div class="form-container">
            <h2>Create <?= $voucher_type ?> Voucher</h2>
            <form method="POST" action="process_voucher.php">
                <input type="hidden" name="voucher_type" value="<?= $voucher_type ?>">

                <!-- Common Fields -->
                <label for="voucher_number">Voucher Number:</label>
                <input type="text" id="voucher_number" name="voucher_number" value="<?= $unique_id ?>" readonly><br>

                <label for="date">Date:</label>
                <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required><br>

                <?php
                // Fetch ledgers for dropdowns
                $ledger_query = "SELECT ledger_id, ledger_name FROM ledgers ORDER BY ledger_name";
                $ledgers = $conn->query($ledger_query);
                if (!$ledgers) {
                    echo "<p class='error-message'>Error fetching ledgers: " . $conn->error . "</p>";
                    exit;
                }
                ?>

                <?php if (in_array($voucher_type, ['Receipt', 'Payment'])): ?>
                    <label for="debit_ledger_id">Debit Ledger:</label>
                    <select id="debit_ledger_id" name="debit_ledger_id" required>
                        <option value="">Select Ledger</option>
                        <?php while ($row = $ledgers->fetch_assoc()): ?>
                            <option value="<?= $row['ledger_id'] ?>"><?= htmlspecialchars($row['ledger_name']) ?></option>
                        <?php endwhile; ?>
                    </select><br>

                    <label for="credit_ledger_id">Credit Ledger:</label>
                    <select id="credit_ledger_id" name="credit_ledger_id" required>
                        <option value="">Select Ledger</option>
                        <?php
                        $ledgers->data_seek(0); // Reset pointer
                        while ($row = $ledgers->fetch_assoc()): ?>
                            <option value="<?= $row['ledger_id'] ?>"><?= htmlspecialchars($row['ledger_name']) ?></option>
                        <?php endwhile; ?>
                    </select><br>
                <?php endif; ?>

                <!-- Other voucher-specific fields omitted for brevity -->

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" required><br>

                <label for="narration">Narration:</label>
                <textarea id="narration" name="narration"></textarea><br>

                <center><button type="submit">Save Voucher</button></center>
            </form>
        </div>
    </div>
</body>
</html>
