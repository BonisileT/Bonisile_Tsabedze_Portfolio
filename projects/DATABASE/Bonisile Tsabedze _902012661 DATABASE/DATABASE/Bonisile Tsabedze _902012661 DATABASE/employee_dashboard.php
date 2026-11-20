<?php
session_start();
include 'config.php';

// Protect dashboard: only employee or manager
if(!isset($_SESSION['UserID']) || !in_array($_SESSION['role'], ['employee','manager'])){
    header("Location: login.php");
    exit();
}

// Fetch products with low stock (<3)
$low_stock = mysqli_query($conn,"SELECT * FROM products WHERE QuantityAvailable < 3");
$low_list = "";
if(mysqli_num_rows($low_stock) > 0){
    $low_products = [];
    while($p = mysqli_fetch_assoc($low_stock)){
        $low_products[] = $p['Name']." (".$p['QuantityAvailable'].")";
    }
    $low_list = implode(", ", $low_products);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Dashboard</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0;}
.container { max-width: 900px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); text-align:center;}
h1 { margin-bottom:20px; color:#333;}
p { font-size:18px; margin-bottom:30px; color:#555;}
.btn { display:inline-block; margin:10px; padding:12px 25px; text-decoration:none; background:#007bff; color:#fff; border-radius:6px; transition:0.3s;}
.btn:hover { background:#0056b3;}
.btn.logout { background:#b30000;}
.btn.logout:hover { background:#800000;}
</style>
</head>
<body>
<div class="container">
    <h1>Employee Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></p>

    <a class="btn" href="track_customer_orders.php">Track Customer Orders</a>
    <a class="btn" href="low_stock.php">Low Stock</a>
    <a class="btn" href="order_stock.php">Request Supplier Order</a>
    <a class="btn logout" href="logout.php">Logout</a>
</div>

<?php if(!empty($low_list)): ?>
<script>
    alert('⚠ Low stock alert! Products running low: <?= addslashes($low_list); ?>');
</script>
<?php endif; ?>

</body>
</html>
