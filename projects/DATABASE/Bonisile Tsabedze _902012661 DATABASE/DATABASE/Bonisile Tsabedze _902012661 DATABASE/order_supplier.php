<?php
include 'config.php';
if($_SESSION['role']!='manager'){ header("Location: login.php"); exit(); }

$requests = mysqli_query($conn,"SELECT pr.*, products.Name AS ProductName, suppliers.Name AS SupplierName
FROM purchase_requests pr 
JOIN products ON products.ProductCode=pr.ProductCode 
JOIN suppliers ON suppliers.SupplierID=pr.SupplierID 
WHERE pr.Status='Pending Manager Approval'");
?>
<div class="container">
<h2>Approve Supplier Orders</h2>
<table class="table">
<tr><th>ID</th><th>Supplier</th><th>Product</th><th>Qty</th><th>Cost</th><th>Action</th></tr>
<?php while($r=mysqli_fetch_assoc($requests)): ?>
<tr>
<td><?= $r['RequestID']; ?></td>
<td><?= $r['SupplierName']; ?></td>
<td><?= $r['ProductName']; ?></td>
<td><?= $r['Quantity']; ?></td>
<td>$<?= $r['CostOfOneItem']; ?></td>
<td><a href="supplier_receipt.php?id=<?= $r['RequestID']; ?>" class="btn">Approve</a></td>
</tr>
<?php endwhile; ?>
</table>
<a href="employee_dashboard.php" class="btn">Back</a>
</div>
