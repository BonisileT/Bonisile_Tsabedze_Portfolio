<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interiorfunn System </title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="container">

    <h1> Interiorfunn System </h1>
    <p class="subtitle">Welcome to our store</p>

    <!-- USER STATUS -->
    <?php if(isset($_SESSION['username'])): ?>
        <div class="logged-in">
            Logged in as: <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>  
            (<?= htmlspecialchars($_SESSION['role']); ?>)
        </div>
    <?php else: ?>
        <div class="logged-out">
            Please login.
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Login Pages</h2>
        <div class="btn-group">
            <a class="btn" href="login.php">Customer & Employee Login</a>
            <a class="btn" href="manager_login.php">Manager Login</a>
            <a class="btn" href="ceo_login.php">CEO Login</a>

            <!-- ✅ Supervisor Login Added -->
            <a class="btn" href="supervisor_login.php">Supervisor Login</a>

            <a class="btn" href="signup.php">Customer & Employee Signup</a>
        </div>
    </div>

    <video controls>
        <source src="img/myv.mp4" type="video/mp4">
        <source src="myv.webm" type="video/webm">
        <source src="myv.ogg" type="video/ogg">
        Your browser does not support the video tag.
    </video>

    <footer>
        <marquee>
            &copy; 2025 INTERIORFUNN SHOP All rights reserved... Lwex graphics...
        </marquee>
    </footer>

    <!-- LOGOUT IF LOGGED IN -->
    <?php if(isset($_SESSION['role'])): ?>
        <div class="section">
            <a class="btn logout" href="logout.php">Logout</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
