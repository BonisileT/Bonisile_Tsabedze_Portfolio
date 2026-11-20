<?php
session_start();
include 'config.php';

// Only Supervisor Access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor'){
    header("Location: supervisor_login.php");
    exit();
}

// Dashboard stats
$totalOrders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$approvedOrders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE ApprovalStatus='approved'")->fetch_assoc()['c'];
$pendingOrders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE ApprovalStatus='pending'")->fetch_assoc()['c'];
$rejectedOrders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE ApprovalStatus='rejected'")->fetch_assoc()['c'];

// Fetch all orders
$orders = $conn->query("
    SELECT o.OrderID, o.CustomerID, o.EmployeeID, o.OrderDate, o.TotalOrderCost, 
           o.Status, o.ApprovalStatus, o.Feedback,
           c.FirstName, c.LastName
    FROM orders o
    LEFT JOIN customers c ON o.CustomerID=c.CustomerID
    ORDER BY o.OrderDate DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Supervisor Dashboard</title>
<style>
body {margin:0; font-family:Arial; background:#eef1f7;}
.sidebar {
    width:230px; background:#1e2a38; color:white; height:100vh; position:fixed; top:0; left:0;
    padding-top:30px;
}
.sidebar a {
    display:block; padding:14px 20px; color:white; text-decoration:none; font-size:16px;
}
.sidebar a:hover {background:#394a5a;}
.header {
    margin-left:230px; padding:20px; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1);
}
.dashboard-container {
    margin-left:230px; padding:20px;
}
.card {
    background:white; width:22%; display:inline-block; margin:10px; padding:20px;
    border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); text-align:center;
}
.card h2 {margin:0; font-size:28px; color:#0056b3;}
.card p {margin:5px 0 0; color:#555;}
.table-container {
    margin-top:20px; background:white; padding:20px; border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}
table {width:100%; border-collapse:collapse;}
th, td {padding:10px; border-bottom:1px solid #ddd;}
th {background:#0056b3; color:white;}
.button {padding:6px 12px; color:white; text-decoration:none; border-radius:5px; margin:3px;}
.approve {background:#28a745;}
.reject {background:#dc3545;}
.receipt {background:#007bff;}
.status-approved {color:green; font-weight:bold;}
.status-rejected {color:red; font-weight:bold;}
.status-pending {color:orange; font-weight:bold;}
</style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align:center;">Supervisor</h2>
    <a href="supervisor_dashboard.php">📊 Dashboard</a>
    <a href="purchases.php">📦 Approved Purchases</a>
    <a href="logout.php" style="background:#a30000;">🚪 Logout</a>
</div>

<div class="header">
    <h1>Welcome, <?= $_SESSION['username'] ?></h1>
</div>

<div class="dashboard-container">
    <h2>Dashboard Overview</h2>

    <div class="card">
        <h2><?= $totalOrders ?></h2>
        <p>Total Orders</p>
    </div>
    <div class="card">
        <h2><?= $pendingOrders ?></h2>
        <p>Pending Approvals</p>
    </div>
    <div class="card">
        <h2><?= $approvedOrders ?></h2>
        <p>Approved Orders</p>
    </div>
    <div class="card">
        <h2><?= $rejectedOrders ?></h2>
        <p>Rejected Orders</p>
    </div>
<div class="table-container">
    <h2>Orders List</h2>

    <table id="ordersTable">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Employee</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Approval</th>
            <th>Action</th>
        </tr>

        <?php while($row = $orders->fetch_assoc()): ?>
            <tr id="order-<?= $row['OrderID'] ?>">
                <td><?= $row['OrderID'] ?></td>
                <td><?= htmlspecialchars($row['FirstName'] . " " . $row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['EmployeeID'] ?? '-') ?></td>
                <td><?= $row['OrderDate'] ?></td>
                <td><?= number_format($row['TotalOrderCost'], 2) ?></td>
                <td><?= ucfirst($row['Status']) ?></td>
                <td class="approval-status"><?= ucfirst($row['ApprovalStatus']) ?></td>
                <td class="action-buttons">
                    <?php if(strtolower($row['ApprovalStatus']) === 'pending'): ?>
                        <button class="button approve" data-id="<?= $row['OrderID'] ?>">✅ Approve</button>
                        <button class="button reject" data-id="<?= $row['OrderID'] ?>">❌ Reject</button>
                    <?php elseif(strtolower($row['ApprovalStatus']) === 'approved'): ?>
                        <span style="color:green;">Approved ✔</span>
                        <a class="button receipt" href="supervisor_receipt.php?purchase_id=<?= $row['OrderID'] ?>" target="_blank">🧾 View Receipt</a>
                    <?php elseif(strtolower($row['ApprovalStatus']) === 'rejected'): ?>
                        <span style="color:red;">Rejected ✖</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    // Approve button click
    $('.approve').click(function(){
        let orderID = $(this).data('id');
        updateOrderStatus(orderID, 'approved');
    });

    // Reject button click
    $('.reject').click(function(){
        let orderID = $(this).data('id');
        updateOrderStatus(orderID, 'rejected');
    });

    function updateOrderStatus(orderID, action){
        $.ajax({
            url: 'update_order_status_ajax.php',
            method: 'POST',
            data: { order_id: orderID, action: action },
            dataType: 'json',
            success: function(response){
                if(response.success){
                    let row = $('#order-' + orderID);
                    if(action === 'approved'){
                        row.find('.approval-status').text('Approved');
                        row.find('.action-buttons').html(`
                            <span style="color:green;">Approved ✔</span>
                            <a class="button receipt" href="supervisor_receipt.php?purchase_id=${orderID}" target="_blank">🧾 View / Print Receipt</a>
                        `);
                    } else if(action === 'rejected'){
                        row.find('.approval-status').text('Rejected');
                        row.find('.action-buttons').html(`
                            <span style="color:red;">Rejected ✖</span>
                        `);
                    }
                } else {
                    alert(response.message || 'Error updating order status');
                }
            },
            error: function(){
                alert('AJAX error updating order status');
            }
        });
    }

});
</script>

    