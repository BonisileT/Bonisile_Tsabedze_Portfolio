<?php
include 'config.php';

function sendLowStockEmail($product, $quantity){
    global $default_from;
    $to = "admin@interiorfunn.com"; // admin email
    $subject = "Low Stock Alert: $product";
    $message = "Dear Admin,\n\nThe product '$product' is running low. Current stock: $quantity.\nPlease reorder soon.";
    $headers = "From: $default_from\r\n";
    mail($to,$subject,$message,$headers);
}

// Check low stock and notify admin
$low_stock = mysqli_query($conn,"SELECT * FROM products WHERE QuantityAvailable<3");
while($p=mysqli_fetch_assoc($low_stock)){
    sendLowStockEmail($p['Name'], $p['QuantityAvailable']);
}
?>
