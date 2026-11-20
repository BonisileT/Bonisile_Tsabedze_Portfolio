<?php
session_start();
include 'config.php';

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'manager') {
    header("Location: login_manager.php");
    exit();
}

// Fetch products
$productQuery = "SELECT ProductCode, Name, QuantityAvailable, SellingPrice FROM products ORDER BY Name ASC";
$productResult = mysqli_query($conn, $productQuery);
if (!$productResult) die("Database Error: " . mysqli_error($conn));

// Fetch suppliers
$supplierQuery = "SELECT SupplierID, Name FROM suppliers ORDER BY Name ASC";
$supplierResult = mysqli_query($conn, $supplierQuery);
if (!$supplierResult) die("Database Error: " . mysqli_error($conn));
?>
<!DOCTYPE html>
<html>
<head>
<title>Request Supplier Stock</title>
<style>
body { font-family: Arial; background:#f4f4f9; }
.container { max-width:950px; margin:50px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.15);}
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:center; }
th { background:#007bff; color:white; }
input[type=number], input[type=text] { width:80px; padding:5px; }
select { padding:5px; margin-top:10px; margin-bottom:20px; }
.btn { margin-top:20px; padding:12px 25px; background:#28a745; color:white; text-decoration:none; border-radius:5px; border:none; cursor:pointer; }
.btn:hover { background:#1f6e2c; }
.back { background:#555; }
.back:hover { background:#333; }
#add-product-form { margin-top:30px; padding:15px; border:1px solid #ddd; border-radius:5px; background:#f9f9f9; }
#message { margin:10px 0; color:green; }
.error { color:red; }
</style>
</head>
<body>
<div class="container">
<h2>Request Supplier Stock</h2>

<form method="POST" action="process_order.php">

<!-- Supplier Dropdown -->
<label for="supplier">Select Supplier:</label>
<select name="supplier" id="supplier" required>
    <option value="">--Choose Supplier--</option>
    <?php while ($supplier = mysqli_fetch_assoc($supplierResult)): ?>
        <option value="<?= $supplier['SupplierID']; ?>"><?= htmlspecialchars($supplier['Name']); ?></option>
    <?php endwhile; ?>
</select>

<table id="products-table">
<tr>
    <th>Product</th>
    <th>Current Quantity</th>
    <th>Selling Price</th>
    <th>Add Stock</th>
</tr>
<?php while ($row = mysqli_fetch_assoc($productResult)): ?>
<tr>
    <td><?= htmlspecialchars($row['Name']); ?></td>
    <td><?= $row['QuantityAvailable']; ?></td>
    <td><?= number_format($row['SellingPrice'],2); ?></td>
    <td>
        <input type="number" name="quantity[<?= $row['ProductCode']; ?>]" min="0" value="0">
    </td>
</tr>
<?php endwhile; ?>
</table>

<button type="submit" class="btn">Submit Order</button>
<a href="manager_dashboard.php" class="btn back">Return to Dashboard</a>
</form>

<!-- Add New Product Form (AJAX) -->
<div id="add-product-form">
<h3>Add New Product</h3>
<div id="message"></div>
<form id="new-product-form">
    <label>Product Code:</label>
    <input type="text" name="code" required>

    <label>Product Name:</label>
    <input type="text" name="name" required>

    <label>Initial Quantity:</label>
    <input type="number" name="quantity" value="0" min="0" required>

    <label>Selling Price:</label>
    <input type="number" name="price" step="0.01" min="0" value="0" required>

    <button type="submit" class="btn">Add Product</button>
</form>
</div>

<script>
// AJAX for adding new product
document.getElementById('new-product-form').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch('add_product_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if(data.success){
            messageDiv.textContent = data.message;
            messageDiv.className = '';

            // Append new product row to table
            const table = document.getElementById('products-table');
            const row = table.insertRow(-1);
            row.innerHTML = `
                <td>${data.product.Name}</td>
                <td>${data.product.QuantityAvailable}</td>
                <td>${parseFloat(data.product.SellingPrice).toFixed(2)}</td>
                <td><input type="number" name="quantity[${data.product.ProductCode}]" min="0" value="0"></td>
            `;

            // Reset form
            document.getElementById('new-product-form').reset();
        } else {
            messageDiv.textContent = data.message;
            messageDiv.className = 'error';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>

</div>
</body>
</html>
