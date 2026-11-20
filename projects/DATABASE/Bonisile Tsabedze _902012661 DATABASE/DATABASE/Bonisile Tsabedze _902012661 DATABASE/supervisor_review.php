<?php
session_start();
include 'config.php';

// Protect page: only supervisor
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor'){
    header("Location: supervisor_login.php");
    exit();
}

// Validate orderID
if(!isset($_GET['order'])){
    die("No order selected.");
}
$orderID = intval($_GET['order']);

// Fetch order
$order = $conn->query("SELECT * FROM orders WHERE OrderID = $orderID")->fetch_assoc();
if(!$order){
    die("Order not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['approval'];
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("
        UPDATE orders SET 
        ApprovalStatus=?, 
        Feedback=?, 
        ApprovedDate=NOW() 
        WHERE OrderID=?
    ");
    $stmt->bind_param("ssi", $status, $feedback, $orderID);
    $stmt->execute();

    header("Location: supervisor_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Review Order #<?= $orderID ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 600px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
h1 {
    color: #0056b3;
    text-align: center;
    margin-bottom: 30px;
}
label {
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
}
select, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
    font-size: 16px;
    transition: 0.3s;
}
select:focus, textarea:focus {
    border-color: #0056b3;
    box-shadow: 0 0 5px rgba(0,86,179,0.4);
}
button {
    width: 100%;
    padding: 15px;
    background: #0056b3;
    color: #fff;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background: #003d80;
}
a.back {
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
    color: #0056b3;
}
a.back:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="container">
<h1>Review Order #<?= $orderID ?></h1>

<form method="POST">
    <label>Approval Status:</label>
    <select name="approval" required>
        <option value="Approved">Approve</option>
        <option value="Rejected">Reject</option>
    </select>

    <label>Feedback:</label>
    <textarea name="feedback" rows="5" placeholder="Enter feedback for the customer..."></textarea>

    <button type="submit">Submit</button>
</form>

<a class="back" href="supervisor_dashboard.php">&larr; Back to Dashboard</a>
</div>
</body>
</html>
