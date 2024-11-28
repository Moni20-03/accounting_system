<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINPACK - Account Info</title>
    <link rel="stylesheet" href="css/account_info.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->

    <div class="main-content">
        <!-- Account Info Container -->
        <div class="container" id="account-info-menu">
            <h2>Account Info</h2>
            <button onclick="showMenu('groups-menu')">Groups</button>
            <button onclick="showMenu('ledgers-menu')">Ledgers</button>
        </div>

        <!-- Groups Menu -->
<div class="container submenu" id="groups-menu" style="display: none;">
    <h3>Groups</h3>
    <a href="create_group.php"><button>Create</button></a>
    <a href="display_group.php"><button>Display</button></a>
    <a href="alter_group.php"><button>Alter</button></a>
    <button onclick="goBack()">Back</button>
</div>

<!-- Ledgers Menu -->
<div class="container submenu" id="ledgers-menu" style="display: none;">
    <h3>Ledgers</h3>
    <a href="create_ledger.php"><button>Create</button></a>
    <a href="display_ledgers.php"><button>Display</button></a>
    <a href="alter_ledger.php"><button>Alter</button></a>
    <button onclick="goBack()">Back</button>
</div>

    </div>

    <script>
        function showMenu(menuId) {
    // Hide all menus
    document.querySelectorAll('.submenu').forEach(menu => menu.style.display = 'none');
    // Hide the Account Info menu
    document.getElementById('account-info-menu').style.display = 'none';
    // Show the selected menu
    document.getElementById(menuId).style.display = 'flex'; // Flex to respect `gap` and alignment
}

function goBack() {
    // Hide all menus
    document.querySelectorAll('.submenu').forEach(menu => menu.style.display = 'none');
    // Show the Account Info menu
    document.getElementById('account-info-menu').style.display = 'flex'; // Back to main menu
}

    </script>
</body>
</html>
