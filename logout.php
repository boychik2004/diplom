<?php
session_start();

// Удаляем все сессионные переменные
session_unset();

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header("Location: login.php");
exit();
?>
