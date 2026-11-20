<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE Username=? AND Password=? AND Role='supervisor'");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'supervisor';
        header("Location: supervisor_dashboard.php");
        exit();
    } else {
        $error = "Invalid login details!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor Login</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0056b3, #00aaff);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: #ffffff;
            width: 350px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            text-align: center;
        }

        h2 {
            margin-top: 0;
            color: #0056b3;
            font-size: 24px;
            margin-bottom: 20px;
        }

        input[type="text"], 
        input[type="password"] {
            width: 92%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: 0.3s;
        }

        input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0,86,179,0.4);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0056b3;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #003d80;
        }

        .error {
            margin-top: 15px;
            color: red;
            font-weight: bold;
        }
    </style>

</head>
<body>

<div class="login-container">
    <h2>Supervisor Login</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Supervisor Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <?php if($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
</div>

</body>
</html>
