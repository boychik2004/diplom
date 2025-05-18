<?php
session_start();

// Уничтожение сессии
$_SESSION = array();
session_destroy();

// Перенаправление на страницу входа
header("Location: admin_login.php");
exit;
?>