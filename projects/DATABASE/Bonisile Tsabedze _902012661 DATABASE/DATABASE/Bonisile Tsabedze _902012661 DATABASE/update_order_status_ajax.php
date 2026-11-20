<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Only supervisor
if(!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'supervisor'){
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'ACCESS DENIED']);
    exit;
}

// Validate input
if(!isset($_POST['order_id'], $_POST['action'])){
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$orderID = intval($_POST['order_id']);
$action = strtolower($_POST['action']);

if(!in_array($action, ['approved','rejected'])){
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Check current status from DB
$stmt = $conn->prepare("SELECT ApprovalStatus FROM orders WHERE OrderID=?");
$stmt->bind_param("i", $orderID);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$currentStatus = $result->fetch_assoc()['ApprovalStatus'];
if(strtolower($currentStatus) !== 'pending'){
    echo json_encode(['success' => false, 'message' => 'Order already processed']);
    exit;
}

// Update order
$stmt = $conn->prepare("UPDATE orders SET ApprovalStatus=? WHERE OrderID=?");
$stmt->bind_param("si", $action, $orderID);
$stmt->execute();

// If rejected, log to dashboard_overview
if($action === 'rejected'){
    $stmt2 = $conn->prepare("INSERT INTO dashboard_overview (OrderID, Status, DateUpdated) VALUES (?, 'Rejected', NOW())");
    $stmt2->bind_param("i", $orderID);
    $stmt2->execute();
}

echo json_encode(['success' => true]);
?>
