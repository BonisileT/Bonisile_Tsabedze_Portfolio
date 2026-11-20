<?php
include 'config.php';

// Fetch products with QuantityAvailable < 10
$products = mysqli_query($conn,"SELECT * FROM products WHERE QuantityAvailable < 10");

// Prepare list of low stock items for alert and high urgency
$low_list = [];
$high_urgency_list = [];
if(mysqli_num_rows($products) > 0){
    while($p = mysqli_fetch_assoc($products)){
        $low_list[] = $p['Name'] . " (" . $p['QuantityAvailable'] . ")";
        if($p['QuantityAvailable'] < 5){
            $high_urgency_list[] = $p['Name'] . " (" . $p['QuantityAvailable'] . ")";
        }
    }
    // Reset pointer for table display
    mysqli_data_seek($products, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Low Stock Alerts</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0;}
.container { max-width: 900px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#333;}
table { width:100%; border-collapse: collapse; margin-bottom:20px;}
th, td { border:1px solid #ccc; padding:10px; text-align:center;}
th { background:#007bff; color:#fff;}
tr:nth-child(even) { background:#f2f2f2;}
.low-stock { background: #ffcccc; font-weight: bold; } /* Highlight if high urgency */
.medium-stock { background: #fff0b3; } /* Highlight if medium urgency */
.btn { display:inline-block; padding:10px 20px; background:#555; color:#fff; text-decoration:none; border-radius:5px; margin-right:10px;}
.btn:hover { background:#333; }
.notify-btn { background:#b30000; }
.notify-btn:hover { background:#800000; }
</style>
<script>
function printAlert() {
    alert("⚠ High urgency stock items have been notified to the manager.");
    // Here you can add AJAX or email functionality later
}
</script>
</head>
<body>
<div class="container">
<h2>Low Stock Alerts</h2>

<?php if(mysqli_num_rows($products) == 0): ?>
    <p>No low stock items</p>
<?php else: ?>
    <table>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Urgency</th>
        </tr>
        <?php while($p = mysqli_fetch_assoc($products)): 
            $urgency = '';
            $row_class = '';
            if($p['QuantityAvailable'] < 5){
                $urgency = 'High';
                $row_class = 'low-stock';
            } elseif($p['QuantityAvailable'] < 10){
                $urgency = 'Medium';
                $row_class = 'medium-stock';
            }
        ?>
        <tr class="<?= $row_class; ?>">
            <td><?= htmlspecialchars($p['Name']); ?></td>
            <td><?= $p['QuantityAvailable']; ?></td>
            <td><?= $urgency; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

<a href="employee_dashboard.php" class="btn">Back</a>

<?php if(!empty($high_urgency_list)): ?>
<button class="btn notify-btn" onclick="printAlert()">Notify Manager</button>
<?php endif; ?>

</div>

<?php if(!empty($low_list)): ?>
<script>
    alert("⚠ Low stock alert! Products running low: <?= addslashes(implode(', ', $low_list)); ?>");
</script>
<?php endif; ?>
</body>
</html>
