<?php
session_start();
include 'config.php';

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();

        // Verify password
        if(password_verify($password, $user['password'])){
            // Set session variables
            $_SESSION['UserID'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch($user['role']){
                case 'customer':
                    header("Location: customer_dashboard.php");
                    exit();
                case 'employee':
                    header("Location: employee_dashboard.php");
                    exit();
                case 'manager':
                    header("Location: admin_dashboard.php");
                    exit();
                default:
                    $error = "Invalid role. Contact admin.";
            }
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Username not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0; }
.container { max-width: 400px; margin: 80px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#333;}
input, button { width:100%; padding:10px; margin-top:10px; border-radius:5px; border:1px solid #ccc; box-sizing:border-box;}
button { background:#007bff; color:#fff; font-size:16px; cursor:pointer; border:none;}
button:hover { background:#0056b3;}
.alert { color:#b30000; font-weight:bold; text-align:center; margin-bottom:15px;}
a { display:block; text-align:center; margin-top:15px; color:#007bff; text-decoration:none;}
a:hover { text-decoration:underline;}
</style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
    </form>
    <a href="signup.php">Don't have an account? Signup</a>
</div>
</body>
</html>
