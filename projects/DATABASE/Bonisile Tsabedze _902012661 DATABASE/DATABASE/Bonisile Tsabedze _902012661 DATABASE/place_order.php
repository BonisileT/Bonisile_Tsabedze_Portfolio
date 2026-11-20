<?php
include 'config.php';
session_start();

// Redirect if not customer
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

// Fetch products
$products = mysqli_query($conn,"SELECT * FROM products");

$message = "";

if(isset($_POST['order'])){
    $product_code = $_POST['product'];
    $qty = (int)$_POST['quantity'];
    $payment = $_POST['payment'];

    // Fetch product details
    $prod_result = mysqli_query($conn,"SELECT * FROM products WHERE ProductCode='".mysqli_real_escape_string($conn,$product_code)."'");
    $prod = mysqli_fetch_assoc($prod_result);

    if(!$prod){
        $message = "<div class='alert'>Product not found!</div>";
    } elseif($prod['QuantityAvailable'] < $qty){
        $message = "<div class='alert'>Insufficient stock! Only {$prod['QuantityAvailable']} available.</div>";
    } else {
        $total = $prod['SellingPrice'] * $qty;

        $customer_id = $_SESSION['UserID'];

        // Insert order into orders table
        $stmt_order = $conn->prepare("INSERT INTO orders(CustomerID, EmployeeID, OrderDate, TotalOrderCost) VALUES (?, NULL, CURDATE(), ?)");
        $stmt_order->bind_param("id", $customer_id, $total);
        $stmt_order->execute();
        $order_id = $conn->insert_id;

        // Insert into invoices
        $stmt_invoice = $conn->prepare("INSERT INTO invoices(OrderID, ProductCode, Quantity, PriceEach, TotalCost) VALUES (?, ?, ?, ?, ?)");
        $stmt_invoice->bind_param("isidd", $order_id, $product_code, $qty, $prod['SellingPrice'], $total);
        $stmt_invoice->execute();

        // Decrease product quantity in SQL
        $stmt_update = $conn->prepare("UPDATE products SET QuantityAvailable = QuantityAvailable - ? WHERE ProductCode = ?");
        $stmt_update->bind_param("is", $qty, $product_code);
        $stmt_update->execute();

        // Fetch customer info for receipt
        $cust_result = mysqli_query($conn,"SELECT FirstName, LastName FROM customers WHERE CustomerID = $customer_id");
        $customer = mysqli_fetch_assoc($cust_result);

        // Store receipt info in session
        $_SESSION['receipt'] = [
            'order_id' => $order_id,
            'customer_name' => $customer['FirstName'].' '.$customer['LastName'],
            'product_name' => $prod['Name'],
            'quantity' => $qty,
            'price_each' => $prod['SellingPrice'],
            'total' => $total,
            'payment' => $payment,
            'order_date' => date('Y-m-d')
        ];

        // Redirect to receipt page
        header("Location: receipt.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Place Order</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0; }
.container { max-width: 600px; margin: 50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align: center; margin-bottom: 20px; color: #333;}
label { display: block; margin-top: 15px; font-weight: bold;}
input, select, button { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box;}
button { background: #007bff; color: #fff; font-size: 16px; margin-top: 20px; cursor: pointer; border: none;}
button:hover { background: #0056b3; }
.alert { color: #b30000; font-weight: bold; text-align: center; margin-bottom: 15px;}
.btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #555; color: #fff; text-decoration: none; border-radius: 5px;}
.btn:hover { background: #333;}
</style>
</head>
<body>

<div class="container">
    <h2>Place Order</h2>

    <?= $message ?>

    <form method="POST">
        <label>Product</label>
        <select name="product" required>
            <option value="">Select Product</option>
            <?php while($p=mysqli_fetch_assoc($products)): ?>
                <option value="<?= htmlspecialchars($p['ProductCode']); ?>">
                    <?= htmlspecialchars($p['Name']); ?> ($<?= $p['SellingPrice']; ?>) - Available: <?= $p['QuantityAvailable']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Quantity</label>
        <input type="number" name="quantity" min="1" required>

        <label>Mode of Payment</label>
        <select name="payment" required>
            <option value="Cash">Cash</option>
            <option value="Card">Card</option>
            <option value="Mobile">Mobile Money</option>
        </select>

        <button name="order">Place Order</button>
    </form>

    <a href="customer_dashboard.php" class="btn">Back to Dashboard</a>
</div>

           <img src="img/wall-unit.jpg" alt="" width="200" height="200" style="vertical-align:bottom">
         <img src="img/sofa.jpg" alt="" width="200" height="200" style="vertical-align:middle">
         <img src="img/spoon.jpg" alt="" width="200" height="200" style="vertical-align:middle">
         <img src="img/table.jpg" alt="" width="200" height="200" style="vertical-align:middle">
         <img src="img/wardrope.jpg" alt="" width="200" height="200" style="vertical-align:middle">
          
        <div> <img src="img/bed.jpg" alt="" width="200" height="200" style="vertical-align:middle">
         <img src="img/chair.jpg" alt="" width="200" height="200" style="vertical-align:middle">
        
</div>

</body>
</html>
