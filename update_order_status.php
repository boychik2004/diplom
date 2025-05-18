<?php
session_start();

// Проверка, что пользователь залогинен и является админом
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: account.php");
    exit();
}

$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'G2V4PB4P5k*AZW3D';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderId = intval($_POST['order_id']);
        $newStatus = $_POST['new_status'];

        $validStatuses = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            header("Location: account.php?msg=" . urlencode("Неверный статус"));
            exit();
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);

        header("Location: account.php?msg=" . urlencode("Статус заказа #{$orderId} изменён на '{$newStatus}'"));
        exit();
    } else {
        header("Location: account.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Ошибка изменения статуса: " . $e->getMessage(), 3, "error.log");
    header("Location: account.php?msg=" . urlencode("Ошибка при обновлении статуса"));
    exit();
}
?>