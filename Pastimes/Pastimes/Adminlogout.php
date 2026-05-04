<?php
session_start();
session_destroy();
header("Location: Adminlogin.php");
exit();