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
$password = 'UHABHc8Z9Y6F8X#2';

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

    // Обработка удаления заказа
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'], $_POST['order_id'])) {
        $orderId = (int)$_POST['order_id'];

        // Проверяем, принадлежит ли заказ пользователю
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            try {
                // Удаляем товары из заказа
                $pdo->beginTransaction();
                $pdo->exec("DELETE FROM order_items WHERE order_id = $orderId");
                // Удаляем сам заказ
                $pdo->exec("DELETE FROM orders WHERE id = $orderId");
                $pdo->commit();

                // Перезагружаем страницу, чтобы обновить список
                header("Location: account.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<p style='color:red;'>Ошибка при удалении заказа: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Заказ не найден или доступ запрещён.</p>";
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="account.css">
</head>
<body>

<main class="account-main">
    <div class="container">

        <!-- Информация о пользователе -->
        <section class="user-info">
            <div class="user-header">
                <h2>Привет, <?= htmlspecialchars($user['username']) ?></h2>
                <a href="logout.php" class="logout-btn">Выход</a>
            </div>
            <div class="user-details">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Дата регистрации:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
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
                    <div class="order-block">
                        <div class="order-header">
                            <h3>Заказ #<?= $order['id'] ?> — <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></h3>
                            <p><strong>Статус:</strong> 
                                <span class="status <?= getStatusClass($order['status']) ?>">
                                    <?= getStatusText($order['status']) ?>
                                </span>
                            </p>

                            <!-- Форма удаления -->
                            <form method="POST" class="delete-form">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" name="delete_order" class="delete-btn" onclick="return confirm('Вы уверены?');">
                                    Удалить заказ
                                </button>
                            </form>
                        </div>

                        <!-- Таблица товаров в заказе -->
                        <table class="orders-table">
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
                                            <td data-label="Фото">
                                                <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" width="80">
                                            </td>
                                            <td data-label="Название"><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td data-label="Цена"><?= number_format($item['price'], 2, ',', ' ') ?> ₽</td>
                                            <td data-label="Количество"><?= $item['quantity'] ?></td>
                                            <td data-label="Сумма"><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> ₽</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">Нет товаров</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Общая сумма -->
                        <div class="order-total">
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