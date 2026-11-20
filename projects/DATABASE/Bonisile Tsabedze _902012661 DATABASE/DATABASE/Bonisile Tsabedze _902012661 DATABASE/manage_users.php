<?php
include 'config.php';
if($_SESSION['role']!='admin'){ header("Location: login.php"); exit(); }

$users = mysqli_query($conn,"SELECT * FROM users");
?>
<div class="container">
<h2>Manage Users</h2>
<table class="table">
<tr><th>ID</th><th>Username</th><th>Role</th></tr>
<?php while($u=mysqli_fetch_assoc($users)): ?>
<tr>
<td><?= $u['id']; ?></td>
<td><?= $u['username']; ?></td>
<td><?= $u['role']; ?></td>
</tr>
<?php endwhile; ?>
</table>
<a href="admin_dashboard.php" class="btn">Back</a>
</div>
