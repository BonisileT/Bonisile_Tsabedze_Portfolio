<?php
session_start();
include 'config.php';

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'manager') {
    header("Location: login_manager.php");
    exit();
}

if (!isset($_SESSION['order_details']) || empty($_SESSION['order_details'])) {
    echo "<h2 style='text-align:center; margin-top:50px;'>No recent supplier order found.</h2>";
    echo "<div style='text-align:center; margin-top:20px;'><a href='manager_dashboard.php' class='btn'>Return to Dashboard</a></div>";
    exit();
}

$order_details = $_SESSION['order_details'];
$total_quantity = 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>Supplier Order Receipt</title>
<style>
body { font-family: Arial; background:#f4f4f9; }
.container { max-width:900px; margin:50px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.15);}
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:center; }
th { background:#007bff; color:white; }
.btn { margin-top:20px; padding:12px 25px; background:#28a745; color:white; text-decoration:none; border-radius:5px; border:none; cursor:pointer; }
.btn:hover { background:#1f6e2c; }
</style>
</head>
<body>
<div class="container">
<h2>Supplier Order Receipt</h2>
<table>
<tr>
    <th>Product Code</th>
    <th>Name</th>
    <th>Quantity Added</th>
</tr>
<?php
foreach ($order_details as $productCode => $qty) {
    $qty = intval($qty);
    if ($qty <= 0) continue;
    $total_quantity += $qty;

    $stmt = $conn->prepare("SELECT Name FROM products WHERE ProductCode=?");
    $stmt->bind_param("s", $productCode);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
?>
<tr>
    <td><?= htmlspecialchars($productCode); ?></td>
    <td><?= htmlspecialchars($product['Name']); ?></td>
    <td><?= $qty; ?></td>
</tr>
<?php } ?>
</table>
<h3>Total Items Added: <?= $total_quantity; ?></h3>
<a href="manager_dashboard.php" class="btn">Return to Dashboard</a>
</div>
</body>
</html>
