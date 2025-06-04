<?php
session_start();

// Удаляем все переменные сессии
session_unset();

// Уничтожаем сессию
session_destroy();

// Редирект на нужную страницу
header("Location: login.php");
exit();
?>