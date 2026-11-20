<?php
session_start();
include 'config.php';

// Supervisor access only
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'supervisor') {
    die("<h2>ACCESS DENIED: Supervisor Only!</h2>");
}

// Insert approved orders into purchase table
$sqlApproved = "
    SELECT 
        o.OrderID,
        o.OrderDate,
        o.EmployeeID,
        i.ProductCode,
        i.Quantity,
        p.SellingPrice
    FROM orders o
    JOIN invoices i ON o.OrderID = i.OrderID
    JOIN products p ON i.ProductCode = p.ProductCode
    WHERE LOWER(o.ApprovalStatus) = 'approved'
";
$result = $conn->query($sqlApproved);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Check if already inserted
        $check = $conn->prepare("SELECT PurchaseID FROM purchase WHERE OrderID=? AND ProductCode=?");
        $check->bind_param("is", $row['OrderID'], $row['ProductCode']);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("
                INSERT INTO purchase 
                (OrderID, EmployeeID, ProductCode, PurchaseDate, Quantity, CostOfOneItem, TotalCost) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $employeeID = $row['EmployeeID'] ?? NULL; // Use NULL if EmployeeID not assigned
            $totalCost = $row['Quantity'] * $row['SellingPrice'];
            $stmt->bind_param(
                "iissidd",
                $row['OrderID'],
                $employeeID,
                $row['ProductCode'],
                $row['OrderDate'],
                $row['Quantity'],
                $row['SellingPrice'],
                $totalCost
            );
            $stmt->execute();
        }
        $check->close();
    }
}

// Fetch purchases to display
$purchaseResult = $conn->query("
    SELECT p.*, o.CustomerID, c.FirstName, c.LastName 
    FROM purchase p
    JOIN orders o ON p.OrderID = o.OrderID
    JOIN customers c ON o.CustomerID = c.CustomerID
    ORDER BY p.PurchaseDate DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Supervisor Purchases</title>
<style>
body { font-family: Arial; background:#f4f4f9; padding:20px; }
table { border-collapse: collapse; width:100%; margin-top:20px; }
th, td { border:1px solid #444; padding:10px; text-align:left; }
th { background:#222; color:#fff; }
a.receipt { background:#007bff; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; }
</style>
</head>
<body>

<h2>Approved Purchases</h2>

<table>
<tr>
<th>Employee ID</th>
<th>Customer</th>
<th>Product Code</th>
<th>Purchase Date</th>
<th>Quantity</th>
<th>Cost of One Item</th>
<th>Total Cost</th>
<th>Receipt</th>
</tr>

<?php if ($purchaseResult->num_rows > 0): ?>
    <?php while ($row = $purchaseResult->fetch_assoc()): ?>
    <tr>
        <td><?= $row['EmployeeID'] ?? "<i>None</i>" ?></td>
        <td><?= htmlspecialchars($row['FirstName'].' '.$row['LastName']) ?></td>
        <td><?= htmlspecialchars($row['ProductCode']) ?></td>
        <td><?= $row['PurchaseDate'] ?></td>
        <td><?= $row['Quantity'] ?></td>
        <td><?= number_format($row['CostOfOneItem'],2) ?></td>
        <td><?= number_format($row['TotalCost'],2) ?></td>
        <td><a class="receipt" href="supervisor_receipt.php?purchase_id=<?= $row['PurchaseID'] ?>">View Receipt</a></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8">No approved purchases found.</td></tr>
<?php endif; ?>
</table>

</body>
</html>
