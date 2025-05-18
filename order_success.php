<?php
session_start();

if (!isset($_GET['order_id'])) {
    header("Location: account.php");
    exit;
}
$orderId = intval($_GET['order_id']);
$pageTitle = "Заказ оформлен";
include 'header.php';

// Подключение к БД
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'G2V4PB4P5k*AZW3D';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Проверяем, принадлежит ли заказ пользователю
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: account.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ оформлен</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <main class="checkout-page">
        <div class="success-message">
            <h2>Спасибо за заказ!</h2>
            <p>Номер вашего заказа: <strong>#<?= $orderId ?></strong></p>
            <p>Мы свяжемся с вами в ближайшее время.</p>
            <a href="account.php" class="btn back-btn">← Вернуться в аккаунт</a>
        </div>
    </main>
</body>
</html>