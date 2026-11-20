<?php
session_start();
include 'config.php';

// Supervisor access only
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'supervisor') {
    die("<h2>ACCESS DENIED: Supervisor Only!</h2>");
}

// Get purchase ID
if (!isset($_GET['purchase_id'])) {
    die("<h2>No purchase specified.</h2>");
}
$purchaseID = intval($_GET['purchase_id']);

// Fetch purchase details
$stmt = $conn->prepare("
    SELECT p.*, o.CustomerID, o.OrderDate, c.FirstName, c.LastName
    FROM purchase p
    JOIN orders o ON p.OrderID = o.OrderID
    JOIN customers c ON o.CustomerID = c.CustomerID
    WHERE p.PurchaseID = ?
");
$stmt->bind_param("i", $purchaseID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h2>Purchase not found.</h2>");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Purchase Receipt #<?= $row['PurchaseID'] ?></title>
<style>
body { font-family: Arial; background:#f4f4f9; padding:20px; }
.receipt { max-width:600px; margin:0 auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
h2 { text-align:center; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #444; padding:10px; text-align:left; }
th { background:#222; color:#fff; }
.print-button { margin-top:20px; display:block; width:100%; text-align:center; padding:10px; background:#28a745; color:#fff; border:none; border-radius:5px; font-size:16px; cursor:pointer; }
.print-button:hover { background:#218838; }
</style>
</head>
<body>

<div class="receipt">
    <h2>Purchase Receipt</h2>
    <p><strong>Purchase ID:</strong> <?= $row['PurchaseID'] ?></p>
    <p><strong>Order ID:</strong> <?= $row['OrderID'] ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></p>
    <p><strong>Employee ID:</strong> <?= $row['EmployeeID'] ?? '<i>None</i>' ?></p>
    <p><strong>Purchase Date:</strong> <?= $row['PurchaseDate'] ?></p>

    <table>
        <tr>
            <th>Product Code</th>
            <th>Quantity</th>
            <th>Cost of One Item</th>
            <th>Total Cost</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($row['ProductCode']) ?></td>
            <td><?= $row['Quantity'] ?></td>
            <td><?= number_format($row['CostOfOneItem'],2) ?></td>
            <td><?= number_format($row['TotalCost'],2) ?></td>
        </tr>
    </table>

    <button class="print-button" onclick="window.print()">🖨 Print Receipt</button>
</div>

</body>
</html>
