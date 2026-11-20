<?php
include 'config.php';
if($_SESSION['role']!='admin'){ header("Location: login.php"); exit(); }
?>
<div class="container">
<h1>Admin Dashboard</h1>
<p>Welcome, <?= $_SESSION['username']; ?></p>
<a class="btn" href="manage_users.php">Manage Users</a>
<a class="btn" href="sales_report.php">Sales Report</a>
<a class="btn" href="logout.php" style="background:#b30000;">Logout</a>
</div>
