<?php
session_start();
session_destroy();
header("Location: ceo_login.php");
exit();
