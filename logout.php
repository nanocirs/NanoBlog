<?php 
setcookie('session_token', '', time() - 3600, '/', $_SERVER['SERVER_NAME'], false, true);
header('location: /index.php');