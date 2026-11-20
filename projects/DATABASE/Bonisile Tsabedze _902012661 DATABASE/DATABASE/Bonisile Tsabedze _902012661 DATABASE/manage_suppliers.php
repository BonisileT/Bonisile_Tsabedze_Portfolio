<?php
session_start();
include 'config.php';

// Only allow CEO
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ceo') {
    header("Location: ceo_login.php");
    exit();
}

// DELETE supplier if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE SupplierID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_suppliers.php");
    exit();
}

// Fetch all suppliers
$result = $conn->query("SELECT * FROM suppliers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { width: 80%; margin: 50px auto; background: #fff; padding: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        a { color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .add-btn { background-color: #28a745; color: white; padding: 8px 15px; border-radius: 3px; text-decoration: none; }
        .add-btn:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="container">
    <h2>Supplier Management</h2>

    <a class="add-btn" href="add_supplier.php">Add New Supplier</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Contact</th>
            <th>Action</th>
        </tr>

        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['SupplierID'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <td><?= $row['Address'] ?></td>
                    <td><?= $row['ContactNumber'] ?></td>
                    <td>
                        <a href="?delete=<?= $row['SupplierID'] ?>" onclick="return confirm('Delete this supplier?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No suppliers found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
