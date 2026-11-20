<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['name'] ?? '');
    $productCode = trim($_POST['code'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    if (!$productName || !$productCode) {
        echo json_encode(['success'=>false,'message'=>'Please enter both Product Code and Name.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO products (ProductCode, Name, QuantityAvailable, SellingPrice) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssid", $productCode, $productName, $quantity, $price);

    if($stmt->execute()){
        echo json_encode([
            'success'=>true,
            'message'=>'Product added successfully!',
            'product'=>[
                'ProductCode'=>$productCode,
                'Name'=>$productName,
                'QuantityAvailable'=>$quantity,
                'SellingPrice'=>$price
            ]
        ]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Database error: '.$stmt->error]);
    }
} else {
    echo json_encode(['success'=>false,'message'=>'Invalid request method.']);
}
