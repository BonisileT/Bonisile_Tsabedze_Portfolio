<?php
session_start();
include 'config.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer'){ 
    header("Location: login.php"); 
    exit(); 
}

// Display success message from order
$success_message = '';
if(isset($_SESSION['success_message'])){
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f9;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 600px;
        margin: 50px auto;
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }
    h1 {
        color: #333;
        margin-bottom: 30px;
    }
    p {
        font-size: 18px;
        color: #555;
        margin-bottom: 30px;
    }
    .btn {
        display: block;
        width: 80%;
        margin: 10px auto;
        padding: 15px 0;
        text-decoration: none;
        font-size: 18px;
        color: #fff;
        background: #007bff;
        border-radius: 8px;
        transition: background 0.3s, transform 0.2s;
    }
    .btn:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }
    .btn-logout {
        background: #b30000;
    }
    .btn-logout:hover {
        background: #800000;
    }
    .success-msg {
        color: #28a745;
        font-weight: bold;
        margin-bottom: 20px;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Customer Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></p>

    <?php if($success_message): ?>
        <div class="success-msg"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <a class="btn" href="place_order.php">Place Order</a>
    <a class="btn" href="track_orders.php">View Orders</a>
    <a class="btn" href="sales_report.php">Invoices</a>
    <a class="btn btn-logout" href="logout.php">Logout</a>
</div>
</body>
</html>
