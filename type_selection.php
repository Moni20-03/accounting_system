<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Voucher Type</title>
    <link rel="stylesheet" href="css/type_selection.css">
</head>
<body>
    <?php include 'Sidebar.php'; ?> <!-- Include Sidebar -->
    <div class="content-container">
        <form method="GET" action="create_voucher.php" class="voucher-form">
            <h2>Select Voucher Type</h2>
            <label for="voucher_type">Choose Voucher Type:</label>
            <select name="voucher_type" required>
                <option value="">Select Type</option>
                <option value="Receipt">Receipt</option>
                <option value="Payment">Payment</option>
                <option value="Contra">Contra</option>
                <option value="Sales">Sales</option>
                <option value="Purchase">Purchase</option>
                <option value="Journal">Journal</option>
            </select>
            <button type="submit">Proceed</button>
        </form>
    </div>
</body>
</html>
