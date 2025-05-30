<?php
session_start();
$pageTitle = "Личный кабинет";
include 'header.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем информацию о пользователе
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: logout.php");
        exit();
    }

    // Получаем фильтр по статусу из GET
    $statusFilter = $_GET['status'] ?? null;

    // Получаем список заказов пользователя
    $sql = "SELECT * FROM orders WHERE user_id = ?";
    if ($statusFilter) {
        $sql .= " AND status = ?";
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $params = [$_SESSION['user_id']];
    if ($statusFilter) {
        $params[] = $statusFilter;
    }
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        <!-- Информация о пользователе -->
        <section class="user-info">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2>Привет, <?= htmlspecialchars($user['username']) ?></h2>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Дата регистрации:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                </div>
                <a href="logout.php" class="logout-btn">Выход</a>
            </div>
        </section>

        <!-- История заказов -->
        <section class="order-history">
            <h2>Ваши заказы</h2>

            <!-- Форма фильтрации -->
            <form method="GET" class="filter-form">
                <label for="status_filter">Фильтр по статусу:</label>
                <select name="status" id="status_filter" onchange="this.form.submit()">
                    <option value="">Все статусы</option>
                    <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Ожидает оплаты</option>
                    <option value="paid" <?= ($statusFilter ?? '') === 'paid' ? 'selected' : '' ?>>Оплачен</option>
                    <option value="processing" <?= ($statusFilter ?? '') === 'processing' ? 'selected' : '' ?>>В обработке</option>
                    <option value="shipped" <?= ($statusFilter ?? '') === 'shipped' ? 'selected' : '' ?>>Отправлен</option>
                    <option value="delivered" <?= ($statusFilter ?? '') === 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                    <option value="cancelled" <?= ($statusFilter ?? '') === 'cancelled' ? 'selected' : '' ?>>Отменён</option>
                </select>
            </form>

            <?php if ($orders): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-block" style="margin-bottom: 30px; border: 1px solid #eee; padding: 20px; border-radius: 10px;">
                        <div class="order-header">
                            <h3>Заказ #<?= $order['id'] ?> — <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></h3>
                            <p><strong>Статус:</strong> 
                                <span class="status <?= getStatusClass($order['status']) ?>">
                                    <?= getStatusText($order['status']) ?>
                                </span>
                            </p>
                        </div>

                        <!-- Товары в заказе -->
                        <table class="orders-table" style="margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th>Фото</th>
                                    <th>Название</th>
                                    <th>Цена</th>
                                    <th>Количество</th>
                                    <th>Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Получаем товары из текущего заказа
                                $stmt = $pdo->prepare("
                                    SELECT oi.*, p.name AS product_name, p.image AS product_image 
                                    FROM order_items oi
                                    LEFT JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = ?
                                ");
                                $stmt->execute([$order['id']]);
                                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php if ($items): ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" width="80" style="border-radius: 5px;">
                                            </td>
                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td><?= number_format($item['price'], 2, ',', ' ') ?> ₽</td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> ₽</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">Нет товаров</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Общая сумма -->
                        <div style="text-align: right; margin-top: 10px; font-size: 1.1rem; font-weight: bold;">
                            Общая сумма: <?= number_format($order['total_amount'], 2, ',', ' ') ?> ₽
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>У вас пока нет заказов.</p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
