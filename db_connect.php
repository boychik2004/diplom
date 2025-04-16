<?php
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'G2V4PB4P5k*AZW3D';

try {
    // Устанавливаем подключение к базе данных
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    // Устанавливаем режим обработки ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Подключение к базе данных успешно установлено.";  // Эта строка теперь закомментирована
} catch (PDOException $e) {
    // В случае ошибки подключения выводим сообщение
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
