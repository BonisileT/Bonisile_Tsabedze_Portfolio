<?php
session_start();
include 'config.php';

$error = ""; // initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ceoID = $_POST['ceoID'];

    // Prepare SQL to fetch CEO user by ID
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id=? AND role='ceo'");
    $stmt->bind_param("i", $ceoID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Store CEO info in session
        $_SESSION['id'] = $user["id"];
        $_SESSION['username'] = $user["username"];
        $_SESSION['role'] = $user["role"];

        // Redirect to dashboard
        header("Location: ceo_dashboard.php");
        exit();
    } else {
        $error = "CEO account not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CEO Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .login-container { width: 300px; margin: 100px auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 8px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>CEO Login</h2>

    <form method="POST" action="">
        <input type="number" name="ceoID" placeholder="CEO ID" required>
        <button type="submit">Login</button>
    </form>

    <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
</div>

</body>
</html>
