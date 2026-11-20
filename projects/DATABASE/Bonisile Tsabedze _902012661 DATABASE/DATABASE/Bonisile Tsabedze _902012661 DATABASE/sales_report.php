<?php
session_start();
include 'config.php';

// 1. Security Check: Allow Customer, Employee, or Manager
if(!isset($_SESSION['UserID'])){
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['UserID'];
$orderID = $_GET['order'] ?? 0;

// 2. Handle Employee Actions (Only if role is employee or manager)
if(isset($_POST['update_status']) && in_array($role, ['employee', 'manager'])) {
    $targetID = $_POST['order_id'];
    $newStatus = $_POST['new_status']; 
    $feedback = $_POST['feedback'];

    $updateQuery = "UPDATE orders SET Status = ?, ApprovalStatus = ?, Feedback = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssi", $newStatus, $newStatus, $feedback, $targetID);
    
    if($stmt->execute()) {
        echo "<script>alert('Order Updated Successfully'); window.location.href='sales_report.php?order=$targetID';</script>";
    }
}

// 3. Fetch Data (Secure check: Customers can only see THEIR OWN orders)
$query = "SELECT i.*, p.Name as ProductName, o.Status, o.Feedback, o.CustomerID, o.OrderDate
          FROM invoices i 
          JOIN products p ON i.ProductCode = p.ProductCode 
          JOIN orders o ON i.OrderID = o.OrderID
          WHERE i.OrderID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $orderID);
$stmt->execute();
$result = $stmt->get_result();
$invoiceData = [];
while($row = $result->fetch_assoc()) { $invoiceData[] = $row; }

// Security: If customer tries to view an order ID that isn't theirs
if($role == 'customer' && !empty($invoiceData) && $invoiceData[0]['CustomerID'] != $user_id) {
    die("Access Denied: You cannot view this invoice.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $orderID ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial; padding: 30px; background: #f4f4f9; }
        .invoice-card { background: white; padding: 40px; border-radius: 10px; max-width: 800px; margin: auto; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f8f9fa; }
        .action-box { background: #fff3cd; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #ffeeba; }
        textarea { width: 100%; height: 60px; margin-top: 10px; padding: 10px; box-sizing: border-box; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; text-decoration: none; }
        .btn-approve { background: #28a745; margin-right: 10px; }
        .btn-reject { background: #dc3545; }
        .btn-print { background: #007bff; float: right; }
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 14px; }
        .status-Accepted { background: #d4edda; color: #155724; }
        .status-Rejected { background: #f8d7da; color: #721c24; }
        @media print { .no-print { display: none; } body { background: white; } .invoice-card { box-shadow: none; border: none; width: 100%; } }
    </style>
</head>
<body>

<div class="invoice-card">
    <button class="btn btn-print no-print" onclick="window.print()">Print Invoice</button>
    
    <div class="header">
        <div>
            <h1 style="margin:0; color:#007bff;">INVOICE</h1>
            <p>Order ID: #<?= $orderID ?></p>
            <p>Date: <?= !empty($invoiceData) ? $invoiceData[0]['OrderDate'] : '' ?></p>
        </div>
        <div style="text-align:right;">
            <p><strong>Status:</strong> <span class="badge status-<?= $invoiceData[0]['Status'] ?? 'Pending' ?>"><?= $invoiceData[0]['Status'] ?? 'Pending' ?></span></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grandTotal = 0;
            foreach($invoiceData as $item): 
                $grandTotal += $item['TotalCost'];
            ?>
            <tr>
                <td><?= htmlspecialchars($item['ProductName']) ?></td>
                <td><?= $item['Quantity'] ?></td>
                <td>$<?= number_format($item['PriceEach'], 2) ?></td>
                <td>$<?= number_format($item['TotalCost'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align:right; font-weight:bold;">Grand Total:</td>
                <td style="font-weight:bold; color:#007bff;">$<?= number_format($grandTotal, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if(!empty($invoiceData[0]['Feedback'])): ?>
        <div style="margin-top:20px; padding:15px; background:#e9ecef; border-left:5px solid #007bff;">
            <strong>Note from Staff:</strong><br>
            <?= htmlspecialchars($invoiceData[0]['Feedback']) ?>
        </div>
    <?php endif; ?>

    <?php if(in_array($role, ['employee', 'manager'])): ?>
    <div class="action-box no-print">
        <h3>Process Order Approval</h3>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?= $orderID ?>">
            <label>Feedback for Customer:</label>
            <textarea name="feedback" placeholder="Explain approval or reason for rejection..."></textarea>
            <br><br>
            <button type="submit" name="update_status" onclick="this.form.new_status.value='Accepted'" class="btn btn-approve">Accept Order</button>
            <button type="submit" name="update_status" onclick="this.form.new_status.value='Rejected'" class="btn btn-reject">Reject Order</button>
            <input type="hidden" name="new_status" value="">
        </form>
    </div>
    <?php endif; ?>

    <div class="no-print" style="margin-top:30px; text-align:center;">
        <hr>
        <a href="<?= $role == 'customer' ? 'track_orders.php' : 'track_all_orders.php' ?>" style="color:#666; text-decoration:none;">← Return to Order List</a>
    </div>
</div>

</body>
</html>