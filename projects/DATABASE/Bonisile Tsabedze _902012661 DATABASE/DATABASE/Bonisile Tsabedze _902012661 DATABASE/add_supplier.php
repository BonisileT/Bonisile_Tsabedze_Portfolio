<?php
session_start();
include 'config.php';

// Check if CEO is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ceo') {
    header("Location: ceo_login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("INSERT INTO suppliers (Name, Address, ContactNumber) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $address, $contact);

    if($stmt->execute()) {
        $success = "Supplier added successfully!";
    } else {
        $error = "Error adding supplier: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Supplier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0px 0px 10px #ccc;
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .message {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
        }
        .success { color: green; }
        .error { color: red; }
        .back-btn {
            display: block;
            margin: 20px auto 0;
            text-align: center;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            width: 150px;
        }
        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Supplier</h2>

    <?php
    if(isset($success)) echo "<p class='message success'>$success</p>";
    if(isset($error)) echo "<p class='message error'>$error</p>";
    ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Supplier Name" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="text" name="contact" placeholder="Contact Number" required>
        <button type="submit">Add Supplier</button>
    </form>

    <a href="ceo_dashboard.php" class="back-btn">Return to Dashboard</a>
</div>

</body>
</html>
