<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
    <nav class="navbar">
        <div class="navbar-brand">LandManage</div>
        <div class="nav-links">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="land_lookup.php">Land Lookup</a>
                <a href="transactions.php">Transactions</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="user_dashboard.php">Dashboard</a>
                <a href="land_lookup.php">Land Lookup</a>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

<div class="container">
