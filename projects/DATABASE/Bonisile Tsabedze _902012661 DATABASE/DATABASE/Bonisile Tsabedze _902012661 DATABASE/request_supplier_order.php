<?php
session_start();
include 'config.php';

// Only allow employee or manager
if(!isset($_SESSION['UserID']) || !in_array($_SESSION['role'], ['employee','manager'])){
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if(isset($_POST['quantity']) && is_array($_POST['quantity'])){
    foreach($_POST['quantity'] as $productID => $qtyToAdd){
        $qtyToAdd = intval($qtyToAdd); // sanitize input
        if($qtyToAdd > 0){
            // Update product stock
            $stmt = $conn->prepare("UPDATE products SET QuantityAvailable = QuantityAvailable + ? WHERE ProductID = ?");
            $stmt->bind_param("ii", $qtyToAdd, $productID);
            $stmt->execute();
        }
    }
    $_SESSION['success'] = "Stock updated successfully!";
}

header("Location: employee_dashboard.php");
exit();
?>
