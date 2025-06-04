<?php
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$pageTitle = "Просмотр заказов";
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

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['new_status'], $_POST['order_id']]);
        header("Location: admin_orders.php");
        exit();
    } catch (PDOException $e) {
        die("Ошибка при обновлении статуса заказа: " . $e->getMessage());
    }
}

// Получение списка заказов
try {
    $stmt = $pdo->query("
        SELECT id, customer_name, email, phone, address, comment, total_amount, status, created_at
        FROM orders
        ORDER BY created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении заказов: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Меню</h2>
            <ul>
                <li><a href="admin.php">Товары</a></li>
                <li><a href="admin_orders.php" class="active">Заказы</a></li>
                <li><a href="admin_logout.php">Выйти</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-actions">
                <!-- Кнопка Назад -->
                <a href="admin.php" class="btn">Назад к товарам</a>
            </div>

            <h1>Список заказов</h1>

            <?php if (empty($orders)): ?>
                <p>Нет заказов.</p>
            <?php else: ?>
                <!-- Таблица с адаптацией -->
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID заказа</th>
                                <th>Имя клиента</th>
                                <th>Email</th>
                                <th>Телефон</th>
                                <th>Адрес</th>
                                <th>Комментарий</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): 
                                $id = htmlspecialchars($order['id']);
                                $customerName = htmlspecialchars($order['customer_name']);
                                $email = htmlspecialchars($order['email']);
                                $phone = htmlspecialchars($order['phone']);
                                $address = htmlspecialchars($order['address']);
                                $comment = htmlspecialchars($order['comment']);
                                $totalAmount = number_format($order['total_amount'], 2, ',', ' ');
                                $status = htmlspecialchars($order['status']);
                                $createdAt = htmlspecialchars($order['created_at']);
                            ?>
                            <tr>
                                <td data-label="ID заказа"><?= $id ?></td>
                                <td data-label="Имя клиента"><?= $customerName ?></td>
                                <td data-label="Email"><?= $email ?></td>
                                <td data-label="Телефон"><?= $phone ?></td>
                                <td data-label="Адрес"><?= $address ?></td>
                                <td data-label="Комментарий"><?= $comment ?></td>
                                <td data-label="Сумма"><?= $totalAmount ?> ₽</td>
                                <td data-label="Статус"><?= $status ?></td>
                                <td data-label="Дата"><?= $createdAt ?></td>
                                <td data-label="Действие">
                                    <form method="post" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $id ?>">
                                        <select name="new_status" onchange="this.form.submit()">
                                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Ожидает оплаты</option>
                                            <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Оплачен</option>
                                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>В обработке</option>
                                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Отправлен</option>
                                            <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Отменён</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>