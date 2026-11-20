<?php
session_start();
include 'config.php';

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user by username
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Check role BEFORE logging in
        if ($user['role'] !== 'manager') {
            $error = "Access Denied! Only MANAGERS are allowed to log in here.";
        } 
        else if (password_verify($password, $user['password'])) {

            // Login success — set session
            $_SESSION['UserID'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect manager
            header("Location: manager_dashboard.php");
            exit();
        } 
        else {
            $error = "Incorrect password!";
        }

    } else {
        $error = "Username not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Manager Login</title>
<style>
body { font-family: Arial; background:#f4f4f9; }
.container { max-width:400px; margin:80px auto; padding:25px; background:white; border-radius:10px; 
             box-shadow:0px 0px 10px rgba(0,0,0,0.2); }
input, button { width:100%; padding:12px; margin-top:10px; }
button { background:#007bff; color:white; border:0; font-size:16px; cursor:pointer; }
button:hover { background:#0056b3; }
.alert { color:red; text-align:center; font-weight:bold; }
</style>
</head>
<body>

<div class="container">
<h2 style="text-align:center;">Manager Login</h2>

<?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Manager Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button name="login">Login</button>
</form>

</div>

</body>
</html>
