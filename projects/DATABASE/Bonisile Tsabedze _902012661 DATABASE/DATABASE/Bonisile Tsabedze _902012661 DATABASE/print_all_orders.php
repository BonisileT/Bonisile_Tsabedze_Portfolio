<?php
session_start();
include 'config.php';

// Only allow admin/manager or employee access
if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['manager', 'employee'])){
    header("Location: login.php");
    exit();
}

// Fetch all customer orders
$query = "
    SELECT orders.OrderID, orders.CustomerID, users.username AS CustomerName, orders.OrderDate, 
           orders.TotalOrderCost, orders.PaymentMethod, orders.Status
    FROM orders
    JOIN users ON orders.CustomerID = users.id
    ORDER BY OrderDate DESC
";
$orders = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print All Orders</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { text-align:center; margin-bottom:20px; }
table { width:100%; border-collapse: collapse; margin-bottom:20px; }
th, td { border:1px solid #000; padding:10px; text-align:center; }
th { background:#ccc; }
tr:nth-child(even){ background:#f9f9f9; }
.total-row td { font-weight:bold; }
@media print {
    button { display:none; }
}
</style>
<script>
function printPage() {
    window.print();
}
</script>
</head>
<body>
<h2>All Customer Orders</h2>

<table>
<tr>
    <th>Order ID</th>
    <th>Customer Name</th>
    <th>Order Date</th>
    <th>Total Cost</th>
    <th>Payment Method</th>
    <th>Status</th>
</tr>

<?php 
$total_sum = 0;
if(mysqli_num_rows($orders) > 0):
    while($o = mysqli_fetch_assoc($orders)):
        $total_sum += $o['TotalOrderCost'];
?>
<tr>
    <td><?= $o['OrderID'] ?></td>
    <td><?= htmlspecialchars($o['CustomerName']) ?></td>
    <td><?= $o['OrderDate'] ?></td>
    <td>$<?= number_format($o['TotalOrderCost'],2) ?></td>
    <td><?= htmlspecialchars($o['PaymentMethod']) ?></td>
    <td><?= htmlspecialchars($o['Status'] ?? 'Pending') ?></td>
</tr>
<?php 
    endwhile;
else: ?>
<tr><td colspan="6" style="color:red; font-weight:bold;">No orders found.</td></tr>
<?php endif; ?>
<tr class="total-row">
    <td colspan="3">Grand Total:</td>
    <td colspan="3">$<?= number_format($total_sum,2) ?></td>
</tr>
</table>

<button onclick="printPage()">Print</button>
</body>
</html>
