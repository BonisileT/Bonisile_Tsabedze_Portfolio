<?php
session_start();
include 'config.php';

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'manager') {
    header("Location: login_manager.php");
    exit();
}

if (isset($_POST['quantity'])) {
    $_SESSION['order_details'] = $_POST['quantity']; // Save for receipt
    foreach ($_POST['quantity'] as $productCode => $qty) {
        $qty = intval($qty);
        if ($qty > 0) {
            $stmt = $conn->prepare("UPDATE products SET QuantityAvailable = QuantityAvailable + ? WHERE ProductCode = ?");
            $stmt->bind_param("is", $qty, $productCode);
            $stmt->execute();
        }
    }
}

// Redirect to receipt page
header("Location: supplier_receipt.php");
exit();
