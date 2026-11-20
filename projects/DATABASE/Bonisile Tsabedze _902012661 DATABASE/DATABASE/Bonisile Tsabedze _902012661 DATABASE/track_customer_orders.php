<?php
session_start();
include 'config.php';

// Only employee or manager can access
if(!isset($_SESSION['UserID']) || !in_array($_SESSION['role'], ['employee','manager'])){
    header("Location: login.php");
    exit();
}

// Sorting Options
$sort = "ORDER BY orders.OrderDate DESC"; // Default sort

if(isset($_GET['sort'])){
    if($_GET['sort'] == "date_new") $sort = "ORDER BY orders.OrderDate DESC";
    elseif($_GET['sort'] == "date_old") $sort = "ORDER BY orders.OrderDate ASC";
    elseif($_GET['sort'] == "price_high") $sort = "ORDER BY orders.TotalOrderCost DESC";
    elseif($_GET['sort'] == "price_low") $sort = "ORDER BY orders.TotalOrderCost ASC";
}

// Fetch all orders with customer names
$query = "
    SELECT orders.OrderID, orders.OrderDate, orders.TotalOrderCost,  orders.Status,
           users.username AS CustomerName
    FROM orders
    JOIN users ON orders.CustomerID = users.id
    $sort
";
$orders = mysqli_query($conn, $query);

// Total sum of all orders
$sum_query = mysqli_query($conn, "SELECT SUM(TotalOrderCost) AS total_sum FROM orders");
$total_sum = mysqli_fetch_assoc($sum_query)['total_sum'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Track Customer Orders</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0;}
.container { max-width: 1000px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#333;}
table { width:100%; border-collapse: collapse; margin-bottom:25px;}
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:center;}
th { background:#007bff; color:white;}
tr:nth-child(even) { background:#f2f2f2;}
.total-box { background:#28a745; color:#fff; padding:15px; text-align:center; font-size:20px; border-radius:8px; margin-bottom:20px; font-weight:bold;}
.btn { display:inline-block; padding:12px 20px; background:#555; color:#fff; text-decoration:none; border-radius:6px; transition:0.3s; margin-top:10px;}
.btn:hover { background:#333;}
.status-pending { color:#b36b00; font-weight:bold; }
.status-completed { color:#1e7e34; font-weight:bold; }
.filter-box { text-align:right; margin-bottom:20px;}
select { padding:8px; border-radius:5px; border:1px solid #ccc; font-size:16px;}
</style>
<script>
function printOrders() { window.print(); }
</script>
</head>
<body>
<div class="container">
    <h2>All Customer Orders</h2>

    <!-- Total Box -->
    <div class="total-box">
        Total Amount of All Orders: $<?= number_format($total_sum ?? 0,2); ?>
    </div>

    <!-- Sorting -->
    <div class="filter-box">
        <form method="GET">
            <label><strong>Sort By:</strong></label>
            <select name="sort" onchange="this.form.submit()">
                <option value="date_new" <?= ($_GET['sort'] ?? '') == 'date_new' ? 'selected' : '' ?>>Newest First</option>
                <option value="date_old" <?= ($_GET['sort'] ?? '') == 'date_old' ? 'selected' : '' ?>>Oldest First</option>
                <option value="price_high" <?= ($_GET['sort'] ?? '') == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="price_low" <?= ($_GET['sort'] ?? '') == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            </select>
        </form>
    </div>

    <!-- Orders Table -->
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Order Date</th>
            <th>Total Cost</th>
            <th>Payment Method</th>
            <th>Status</th>
            <th>View Invoice</th>
        </tr>
        <?php if(mysqli_num_rows($orders) > 0): ?>
            <?php while($o = mysqli_fetch_assoc($orders)): ?>
                <?php $status_class = $o['Status'] == "Completed" ? "status-completed" : "status-pending"; ?>
                <tr>
                    <td><?= $o['OrderID']; ?></td>
                    <td><?= htmlspecialchars($o['CustomerName']); ?></td>
                    <td><?= $o['OrderDate']; ?></td>
                    <td>$<?= number_format($o['TotalOrderCost'],2); ?></td>
                    <td><?= htmlspecialchars($o['PaymentMethod'] ?? 'N/A'); ?></td>
                    <td class="<?= $status_class; ?>"><?= $o['Status'] ?? 'Pending'; ?></td>
                    <td><a href="sales_report.php?order=<?= $o['OrderID']; ?>" class="btn">View Invoice</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="color:red; font-weight:bold;">No orders found.</td></tr>
        <?php endif; ?>
    </table>

    <button class="btn" onclick="printOrders()">Print All Orders</button>
    <a href="employee_dashboard.php" class="btn">Back to Dashboard</a>
</div>
</body>
</html>
