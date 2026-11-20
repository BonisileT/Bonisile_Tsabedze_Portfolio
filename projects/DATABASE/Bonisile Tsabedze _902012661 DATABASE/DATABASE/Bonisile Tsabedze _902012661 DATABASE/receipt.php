<?php
session_start();

if(!isset($_SESSION['receipt'])){
    header("Location: customer_dashboard.php");
    exit();
}

$receipt = $_SESSION['receipt'];

// Clear receipt session so it's shown only once
unset($_SESSION['receipt']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Receipt</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0; }
.container { max-width: 600px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#333; margin-bottom:20px;}
p { font-size:16px; margin:5px 0;}
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:left; }
th { background:#f4f4f4; }
a, button { display:block; text-align:center; margin-top:20px; padding:10px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px; width:200px; margin-left:auto; margin-right:auto;}
a:hover, button:hover { background:#0056b3;}
</style>
<script>
function printReceipt() {
    window.print();
}
</script>
</head>
<body>
<div class="container">
    <h2>Order Receipt</h2>

    <p><strong>Order ID:</strong> <?= $receipt['order_id']; ?></p>
    <p><strong>Customer Name:</strong> <?= htmlspecialchars($receipt['customer_name']); ?></p>
    <p><strong>Order Date:</strong> <?= $receipt['order_date']; ?></p>

    <table>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price Each</th>
            <th>Total</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($receipt['product_name']); ?></td>
            <td><?= $receipt['quantity']; ?></td>
            <td>$<?= number_format($receipt['price_each'],2); ?></td>
            <td>$<?= number_format($receipt['total'],2); ?></td>
        </tr>
    </table>

    <p><strong>Payment Method:</strong> <?= htmlspecialchars($receipt['payment']); ?></p>

    <button onclick="printReceipt()">Print Receipt</button>
    <a href="customer_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
