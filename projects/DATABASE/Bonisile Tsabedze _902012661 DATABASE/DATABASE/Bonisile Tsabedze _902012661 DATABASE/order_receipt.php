<?php
include 'config.php';
$order_id = $_GET['order']??0;
$order = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM invoices WHERE OrderID=$order_id"));
?>
<div class="container">
<h2>Order Receipt</h2>
<p>Order ID: <?= $order_id; ?></p>
<p>Product Code: <?= $order['ProductCode']; ?></p>
<p>Quantity: <?= $order['Quantity']; ?></p>
<p>Price Each: $<?= $order['PriceEach']; ?></p>
<p>Total Cost: $<?= $order['TotalCost']; ?></p>
<a href="customer_dashboard.php" class="btn">Back to Dashboard</a>
</div>
