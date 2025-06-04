<?php
session_start();
$pageTitle = "Детали заказа";
include 'header.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header("Location: account.php");
    exit();
}

$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'UHABHc8Z9Y6F8X#2';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем информацию о заказе
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
        header("Location: account.php");
        exit();
    }

    // Получаем товары в заказе
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name AS product_name, p.image AS product_image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Функция для перевода статуса
function getStatusText($status)
{
    $statuses = [
        'pending' => 'Ожидает оплаты',
        'paid' => 'Оплачен',
        'processing' => 'В обработке',
        'shipped' => 'Отправлен',
        'delivered' => 'Доставлен',
        'cancelled' => 'Отменён'
    ];
    return $statuses[$status] ?? 'Неизвестен';
}

// Функция для класса статуса
function getStatusClass($status)
{
    $classes = [
        'pending' => 'status-pending',
        'paid' => 'status-paid',
        'processing' => 'status-processing',
        'shipped' => 'status-shipped',
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled'
    ];
    return $classes[$status] ?? '';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="account.css">
</head>
<body>

<main class="account-main">
    <div class="container">

        <!-- Информация о заказе -->
        <section class="order-meta">
            <h2>Заказ #<?= htmlspecialchars($order['id']) ?></h2>
            <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
            <p><strong>Статус:</strong> 
                <span class="status <?= getStatusClass($order['status']) ?>">
                    <?= getStatusText($order['status']) ?>
                </span>
            </p>
            <p><strong>Имя клиента:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Комментарий:</strong> <?= htmlspecialchars($order['comment']) ?></p>
        </section>

        <!-- Товары в заказе -->
        <section class="order-items">
            <h3>Товары в заказе</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Размер</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" width="80">
                            </td>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['size']) ?></td>
                            <td><?= number_format($item['price'], 2, ',', ' ') ?> ₽</td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Общая сумма -->
        <section class="order-summary">
            <h3>Общая сумма: <?= number_format($order['total_amount'], 2, ',', ' ') ?> ₽</h3>
        </section>

        <!-- Кнопка возврата -->
        <a href="account.php" class="btn back-btn">Вернуться в аккаунт</a>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>