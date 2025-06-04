<?php
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$pageTitle = "Просмотр заказа";
include 'header.php';

// Подключение к базе данных
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'UHABHc8Z9Y6F8X#2';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Получение ID заказа из параметра GET
if (!isset($_GET['id'])) {
    die("Не указан ID заказа.");
}

$order_id = (int)$_GET['id'];

// Получение информации о заказе
try {
    $stmt = $pdo->prepare("
        SELECT id, customer_name, email, phone, address, comment, total_amount, status, created_at
        FROM orders
        WHERE id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Заказ не найден.");
    }
} catch (PDOException $e) {
    die("Ошибка при получении заказа: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Меню</h2>
            <ul>
                <li><a href="admin.php">Товары</a></li>
                <li><a href="admin_orders.php">Заказы</a></li>
                <li><a href="admin_logout.php">Выйти</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <h1>Заказ №<?= htmlspecialchars($order['id']) ?></h1>

            <p><strong>Имя клиента:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Адрес:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
            <p><strong>Комментарий:</strong> <?= nl2br(htmlspecialchars($order['comment'])) ?></p>
            <p><strong>Сумма:</strong> <?= number_format($order['total_amount'], 2) ?> руб.</p>
            <p><strong>Статус:</strong> <?= htmlspecialchars($order['status']) ?></p>
            <p><strong>Дата создания:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

            <a href="admin_orders.php" class="btn">Назад к списку заказов</a>
        </main>
    </div>
</body>
</html>
