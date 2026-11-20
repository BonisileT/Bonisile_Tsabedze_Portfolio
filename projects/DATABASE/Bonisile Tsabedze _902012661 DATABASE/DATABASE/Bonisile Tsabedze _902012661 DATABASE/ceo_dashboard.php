<?php
session_start();
include 'config.php';

// CEO access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ceo') {
    header("Location: ceo_login.php");
    exit();
}

// Fetch stock
$stockResult = $conn->query("SELECT * FROM products");

// Fetch orders
$ordersResult = $conn->query("
    SELECT o.OrderID, c.FirstName, c.LastName, o.OrderDate, o.TotalOrderCost, o.Status
    FROM orders o
    LEFT JOIN customers c ON o.CustomerID = c.CustomerID
    ORDER BY o.OrderDate DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CEO Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 90%; margin: 50px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h1 { color: #007BFF; }
        a { display: inline-block; margin: 10px 0; color: #fff; background-color: #007BFF; padding: 8px 15px; text-decoration: none; border-radius: 4px; }
        a:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        h2 { color: #333; margin-top: 40px; }
    </style>
</head>
<body>
<div class="container">
    <h1>CEO Dashboard</h1>
    <p>Welcome, <?= $_SESSION['username']; ?></p>

    <a href="manage_suppliers.php">Manage Suppliers</a>
    <a href="ceo_logout.php">Logout</a>

    <!-- STOCK TABLE -->
    <h2>All Stock</h2>
    <table>
        <tr>
            <th>Product Code</th>
            <th>Name</th>
            <th>Description</th>
            <th>Quantity Available</th>
            <th>Selling Price</th>
        </tr>
        <?php if($stockResult->num_rows > 0): ?>
            <?php while($row = $stockResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['ProductCode'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <td><?= $row['Description'] ?></td>
                    <td><?= $row['QuantityAvailable'] ?></td>
                    <td><?= $row['SellingPrice'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No stock available.</td></tr>
        <?php endif; ?>
    </table>

    <!-- ORDERS TABLE -->
    <h2>All Orders</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Order Date</th>
            <th>Total Cost</th>
            <th>Status</th>
        </tr>
        <?php if($ordersResult->num_rows > 0): ?>
            <?php while($row = $ordersResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['OrderID'] ?></td>
                    <td><?= $row['FirstName'] . ' ' . $row['LastName'] ?></td>
                    <td><?= $row['OrderDate'] ?></td>
                    <td><?= $row['TotalOrderCost'] ?></td>
                    <td><?= $row['Status'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No orders found.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
