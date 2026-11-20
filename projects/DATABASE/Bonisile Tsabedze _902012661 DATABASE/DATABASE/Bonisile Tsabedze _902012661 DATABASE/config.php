<?php

$conn = mysqli_connect("localhost","root","","interiorfunn");
if(!$conn){
    die("Connection failed: ".mysqli_connect_error());
}

// Email config (adjust if using SMTP later)
$default_from = "no-reply@interiorfunn.com";
?>
