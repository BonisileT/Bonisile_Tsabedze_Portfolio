<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // Manager's first name

    // Fetch employee with Position = 'Manager'
    $stmt = $conn->prepare("SELECT EmployeeID, FirstName, LastName, Position FROM employees WHERE FirstName = ? AND Position = 'Manager'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $manager = $stmt->get_result()->fetch_assoc();

    if ($manager) {
        // Login successful
        $_SESSION['UserID'] = $manager['EmployeeID'];
        $_SESSION['username'] = $manager['FirstName'] . ' ' . $manager['LastName'];
        $_SESSION['role'] = 'manager';
        header("Location: order_stock.php"); // Redirect to order stock
        exit();
    } else {
        $error = "Access denied. Only managers can login here.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manager Login</title>
<style>
body { font-family: Arial; background:#f4f4f9; }
.container { width:350px; margin:100px auto; padding:25px; background:white; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
input[type=text] { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
button { width:100%; padding:12px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
</style>
</head>
<body>
<div class="container">
    <h2>Manager Login</h2>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="First Name" required autofocus>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
