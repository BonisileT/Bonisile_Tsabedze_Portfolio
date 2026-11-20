<?php
session_start();
include 'config.php';

// Only customers
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

// Get order ID from URL
$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$customer_id = $_SESSION['UserID'];

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE OrderID = ? AND CustomerID = ?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if(!$order){
    die("Order not found or you do not have access to it.");
}

// Fetch invoice items
$stmt_items = $conn->prepare("SELECT i.*, p.Name FROM invoices i JOIN products p ON i.ProductCode=p.ProductCode WHERE i.OrderID=?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();

// Prepare invoice items array
$invoice_items = [];
$total = 0;
while($row = mysqli_fetch_assoc($items)){
    $invoice_items[] = $row;
    $total += $row['TotalCost'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice #<?= $order_id ?></title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0;}
.container { max-width: 800px; margin: 50px auto; background: #fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#333;}
table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
th, td { border: 1px solid #ccc; padding: 10px; text-align: center;}
th { background: #007bff; color: #fff;}
tr:nth-child(even) { background: #f2f2f2;}
.total { text-align:right; font-weight:bold; font-size: 18px;}
.btn { display:inline-block; padding:10px 20px; background:#555; color:#fff; text-decoration:none; border-radius:5px; margin-right:10px; margin-top:10px;}
.btn:hover { background:#333; }
</style>
<script>
function printInvoice() {
    window.print();
}
</script>
</head>
<body>
<div class="container">
    <h2>Invoice #<?= $order_id ?></h2>
    <p><strong>Order Date:</strong> <?= $order['OrderDate'] ?></p>
    <p><strong>Status:</strong> <?= $order['Status'] ?? 'Pending' ?></p>

    <table>
        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price Each</th>
            <th>Total Cost</th>
        </tr>
        <?php foreach($invoice_items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['Name']); ?></td>
            <td><?= $item['Quantity']; ?></td>
            <td>$<?= number_format($item['PriceEach'],2); ?></td>
            <td>$<?= number_format($item['TotalCost'],2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" class="total">Grand Total:</td>
            <td>$<?= number_format($total,2); ?></td>
        </tr>
    </table>

    <button class="btn" onclick="printInvoice()">Print Invoice</button>
    <a href="customer_dashboard.php" class="btn">Back to Dashboard</a>
</div>
</body>
</html>
