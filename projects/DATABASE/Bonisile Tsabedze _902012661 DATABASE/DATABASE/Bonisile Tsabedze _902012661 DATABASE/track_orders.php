<?php 
session_start();
include 'config.php';

// Only allow customers
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

// Use correct session key
$customer_id = $_SESSION['UserID'];

// Sorting options
$sort = "ORDER BY OrderDate DESC"; // default
if(isset($_GET['sort'])){
    switch($_GET['sort']){
        case "date_new": $sort = "ORDER BY OrderDate DESC"; break;
        case "date_old": $sort = "ORDER BY OrderDate ASC"; break;
        case "price_high": $sort = "ORDER BY TotalOrderCost DESC"; break;
        case "price_low": $sort = "ORDER BY TotalOrderCost ASC"; break;
    }
}

// Fetch orders for this customer including approval and feedback
$query = "SELECT * FROM orders WHERE CustomerID = ? $sort";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();

// Calculate total sum
$sum_query = $conn->prepare("SELECT SUM(TotalOrderCost) AS total_sum FROM orders WHERE CustomerID = ?");
$sum_query->bind_param("i", $customer_id);
$sum_query->execute();
$total_sum = $sum_query->get_result()->fetch_assoc()['total_sum'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Your Orders</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 1000px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}
.total-box {
    background: #28a745;
    color: #fff;
    padding: 15px;
    text-align: center;
    font-size: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: bold;
}
.filter-box {
    text-align: right;
    margin-bottom: 20px;
}
select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
th {
    background: #007bff;
    color: white;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
.btn-small {
    background: #28a745;
    padding: 8px 12px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
    font-size: 14px;
}
.btn-small:hover {
    background: #1e7e34;
}
.status-pending { color: orange; font-weight:bold; }
.status-approved { color: green; font-weight:bold; }
.status-rejected { color: red; font-weight:bold; }
.feedback-notify { color: #007bff; font-weight:bold; }
.btn {
    display: inline-block;
    padding: 12px 20px;
    background: #555;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
    margin-top: 10px;
    text-align: center;
}
.btn:hover { background: #333; }
</style>
</head>
<body>
<div class="container">
    <h2>Your Orders</h2>

    <!-- Total Spent -->
    <div class="total-box">
        Total Amount Spent: $<?= number_format($total_sum ?? 0, 2); ?>
    </div>

    <!-- Sorting -->
    <div class="filter-box">
        <form method="GET">
            <label><strong>Sort By:</strong> </label>
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
            <th>Date</th>
            <th>Total Cost</th>
            <th>Status</th>
            <th>Approval</th>
            <th>Feedback</th>
            <th>Invoice</th>
        </tr>
        <?php if(mysqli_num_rows($orders) > 0): ?>
            <?php while($o = mysqli_fetch_assoc($orders)): ?>
                <?php
                    $status_class = ($o['Status'] ?? "Pending") == "Completed" ? "status-completed" : "status-pending";
                    $approval_class = $o['ApprovalStatus'] == "approved" ? "status-approved" : ($o['ApprovalStatus'] == "rejected" ? "status-rejected" : "status-pending");
                ?>
                <tr>
                    <td><?= $o['OrderID']; ?></td>
                    <td><?= $o['OrderDate']; ?></td>
                    <td>$<?= number_format($o['TotalOrderCost'],2); ?></td>
                    <td class="<?= $status_class; ?>"><?= $o['Status'] ?? "Pending"; ?></td>
                    <td class="<?= $approval_class; ?>"><?= ucfirst($o['ApprovalStatus'] ?? "Pending"); ?></td>
                    <td>
                        <?php if(!empty($o['Feedback'])): ?>
                            <span class="feedback-notify"><?= htmlspecialchars($o['Feedback']); ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="sales_report.php?order=<?= $o['OrderID']; ?>" class="btn-small">View Invoice</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="color:red; font-weight:bold;">No orders found.</td></tr>
        <?php endif; ?>
    </table>

    <a href="customer_dashboard.php" class="btn">Back to Dashboard</a>
</div>
</body>
</html>
