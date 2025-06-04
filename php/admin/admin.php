<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$pageTitle = "Административная панель";
include 'header.php';

// Подключаем БД
require_once 'config/database.php'; // ← Теперь $pdo доступен отсюда

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Обработка удаления товара
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Товар успешно удален";
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        die("Ошибка при удалении: " . $e->getMessage());
    }
}

// Получение списка товаров
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении товаров: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-container">
    <aside class="admin-sidebar">
        <h2>Меню</h2>
        <ul>
            <li><a href="admin.php" class="active">Товары</a></li>
            <li><a href="admin_orders.php">Заказы</a></li>
            <li><a href="admin_logout.php">Выйти</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <h1>Управление товарами</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <!-- Кнопка добавления товара -->
        <div class="admin-actions">
            <a href="admin_add.php" class="btn">Добавить товар</a>
            <a href="admin_orders.php" class="btn">Перейти к заказам</a>
        </div>

        <!-- Таблица товаров -->
        <table class="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Изображение</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Размер</th>
                    <th>Пол</th>
                    <th>Назначение</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="product-thumb"></td>
                    <td><?= $product['name'] ?></td>
                    <td><?= number_format($product['price'], 2) ?> руб.</td>
                    <td><?= $product['size'] ?></td>
                    <td><?= $product['gender'] == 'male' ? 'Мужская' : 'Женская' ?></td>
                    <td>
                        <?php 
                        $purpose = $product['purpose'];
                        if ($purpose == 'running') echo 'Для бега';
                        elseif ($purpose == 'training') echo 'Для тренировок';
                        elseif ($purpose == 'casual') echo 'Повседневная';
                        elseif ($purpose == 'professional') echo 'Профессиональная';
                        ?>
                    </td>
                    <td class="actions">
                        <a href="admin_edit.php?id=<?= $product['id'] ?>" class="btn edit">Редактировать</a>
                        <a href="admin.php?delete=<?= $product['id'] ?>" class="btn delete" onclick="return confirm('Вы уверены?')">Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

<?php include 'footer.php'; ?>
</body>
</html>