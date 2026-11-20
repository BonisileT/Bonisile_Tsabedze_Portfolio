<?php
session_start();
include 'config.php';

// Only supervisor
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'supervisor') {
    die("ACCESS DENIED: Supervisor Only!");
}

// Validate input
if (!isset($_POST['purchase_id'], $_POST['action'])) {
    die("Invalid request.");
}

$purchaseID = intval($_POST['purchase_id']);
$action = $_POST['action'];

if (!in_array($action, ['approve', 'reject'])) {
    die("Invalid action.");
}

// Update purchase status
$status = $action === 'approve' ? 'Approved' : 'Rejected';

$stmt = $conn->prepare("UPDATE purchase SET Status = ? WHERE PurchaseID = ?");
$stmt->bind_param("si", $status, $purchaseID);

if ($stmt->execute()) {
    header("Location: purchases.php?message=Purchase $status successfully");
    exit;
} else {
    echo "Error updating status: " . $conn->error;
}
?>
