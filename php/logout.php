<?php
session_start();
session_unset();
session_destroy();
header("Location: url=..html/login.html");
exit();
?>
