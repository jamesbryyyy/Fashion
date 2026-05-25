<?php
session_start();

/*
DESTROY SESSION
*/
session_unset();
session_destroy();

/*
REDIRECT TO LOGIN
*/
header("Location: login.php");
exit();
?>