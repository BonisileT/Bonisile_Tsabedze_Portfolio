<?php
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'manager') {
    header("Location: login_manager.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manager Dashboard</title>
<style>
body { font-family: Arial; background:#f4f4f9; text-align:center; padding-top:50px;}
a.btn { display:inline-block; margin:10px; padding:12px 25px; background:#007bff; color:white; text-decoration:none; border-radius:5px;}
a.btn:hover { background:#0056b3; }
</style>
</head>
<body>
<h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></h1>

<a href="order_stock.php" class="btn">Request Supplier Stock</a>
<a href="logout.php" class="btn" style="background:#dc3545;">Logout</a>
</body>
</html>
