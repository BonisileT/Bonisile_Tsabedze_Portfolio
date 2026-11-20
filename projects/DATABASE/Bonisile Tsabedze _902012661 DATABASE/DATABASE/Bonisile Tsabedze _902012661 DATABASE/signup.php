<?php
include 'config.php';

if(isset($_POST['signup'])){
    // Collect login info
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // select from dropdown

    // Check if username exists
    $check = mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($check) > 0){
        $error = "Username already exists!";
    } else {
        // Insert into users table
        mysqli_query($conn,"INSERT INTO users(username,password,role) VALUES('$username','$password','$role')");
        $user_id = mysqli_insert_id($conn); // Use this ID for customer/employee

        if($role == 'customer'){
            // Collect customer info
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $contact = $_POST['contact'];
            $address = $_POST['address'];

            // Insert into customers table
            mysqli_query($conn,"INSERT INTO customers(CustomerID, FirstName, LastName, ContactNumber, Address) 
                VALUES('$user_id','$firstName','$lastName','$contact','$address')");
        } elseif($role == 'employee'){
            // Collect employee info
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $position = $_POST['position'];

            // Insert into employees table
            mysqli_query($conn,"INSERT INTO employees(EmployeeID, FirstName, LastName, Position) 
                VALUES('$user_id','$firstName','$lastName','$position')");
        }

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup</title>
<style>
/* Same styling as before */
body { font-family: Arial, sans-serif; background: #f4f4f9; margin:0; padding:0; }
.container { max-width: 500px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#333;}
input, select, button { width:100%; padding:10px; margin-top:10px; border-radius:5px; border:1px solid #ccc; box-sizing:border-box;}
button { background:#28a745; color:#fff; font-size:16px; cursor:pointer; border:none;}
button:hover { background:#218838; }
.alert { color:#b30000; font-weight:bold; text-align:center; margin-bottom:15px;}
a { display:block; text-align:center; margin-top:15px; color:#007bff; text-decoration:none;}
a:hover { text-decoration:underline;}
</style>
<script>
function toggleFields() {
    var role = document.getElementById('role').value;
    if(role == 'customer'){
        document.getElementById('customerFields').style.display = 'block';
        document.getElementById('employeeFields').style.display = 'none';
    } else {
        document.getElementById('customerFields').style.display = 'none';
        document.getElementById('employeeFields').style.display = 'block';
    }
}
</script>
</head>
<body>
<div class="container">
    <h2>Signup</h2>
    <?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <select name="role" id="role" onchange="toggleFields()" required>
            <option value="">Select Role</option>
            <option value="customer">Customer</option>
            <option value="employee">Employee</option>
        </select>

        <!-- Customer Fields -->
        <div id="customerFields" style="display:none;">
            <input type="text" name="firstName" placeholder="First Name">
            <input type="text" name="lastName" placeholder="Last Name">
            <input type="text" name="contact" placeholder="Contact Number">
            <input type="text" name="address" placeholder="Address">
        </div>

        <!-- Employee Fields -->
        <div id="employeeFields" style="display:none;">
            <input type="text" name="firstName" placeholder="First Name">
            <input type="text" name="lastName" placeholder="Last Name">
            <input type="text" name="position" placeholder="Position">
        </div>

        <button name="signup">Signup</button>
    </form>
    <a href="login.php">Already have an account? Login</a>
</div>
<script>toggleFields();</script>
</body>
</html>
