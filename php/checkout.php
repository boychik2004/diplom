<?php
session_start();

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$pageTitle = "Оформление заказа";
include 'header.php';

// Подключаем БД
require_once 'config/database.php'; // ← Теперь $pdo доступен отсюда

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Получаем данные о пользователе
if (!isset($_SESSION['user_id'])) {
    die("Вы должны быть авторизованы для оформления заказа.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Получаем информацию о товарах в корзине
$cartItems = [];
$totalPrice = 0;

foreach ($_SESSION['cart'] as $itemKey => $quantity) {
    list($productId, $size) = explode('_', $itemKey);

    try {
        $stmt = $pdo->prepare("
            SELECT p.*, s.size_value 
            FROM products p
            JOIN sizes s ON s.size_value = ?
            WHERE p.id = ?
        ");
        $stmt->execute([$size, $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['quantity'] = $quantity;
            $product['total_price'] = $product['price'] * $quantity;
            $totalPrice += $product['total_price'];
            $cartItems[] = $product;
        }
    } catch (PDOException $e) {
        continue;
    }
}

// Применение скидки
$discountPercent = $_SESSION['discount_percent'] ?? 0;
$finalPrice = $totalPrice - ($totalPrice * ($discountPercent / 100));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>

<main class="checkout-page">
    <h1>Оформление заказа</h1>

    <div class="checkout-container">
        <!-- Корзина -->
        <div class="order-summary">
            <h3>Ваш заказ</h3>
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div>
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p>Размер: <?= htmlspecialchars($item['size_value']) ?></p>
                        <p>Количество: <?= $item['quantity'] ?></p>
                        <p class="price"><?= number_format($item['total_price'], 2) ?> ₽</p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Итоговая сумма -->
            <div class="order-total">
                <p>Цена без скидки: <span><?= number_format($totalPrice, 2) ?> ₽</span></p>
                <?php if ($discountPercent > 0): ?>
                    <p>Скидка (<?= $discountPercent ?>%): -<?= number_format($totalPrice * ($discountPercent / 100), 2) ?> ₽</p>
                <?php endif; ?>
                <p><strong>К оплате:</strong> <span><?= number_format($finalPrice, 2) ?> ₽</span></p>
            </div>
        </div>

        <!-- Форма оформления -->
        <div class="checkout-form">
            <form method="post" action="confirm_order.php" id="checkoutForm">
                <h3>Контактная информация</h3>
                
                <div class="form-group">
                    <label for="name">ФИО*</label>
                    <input type="text" id="name" name="name"
                           pattern="[А-ЯЁ][а-яё]+\s[А-ЯЁ][а-яё]+(\s[А-ЯЁ][а-яё]+)?"
                           title="Введите ФИО полностью, начиная с заглавных букв"
                           required>
                </div>
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон*</label>
                    <input type="tel" id="phone" name="phone"
                           pattern="\+7\d{10}"
                           placeholder="+7XXXXXXXXXX"
                           title="Введите телефон в формате +7XXXXXXXXX"
                           required>
                </div>
                <div class="form-group">
                    <label for="city">Город*</label>
                    <input type="text" id="city" name="city"
                           pattern="[А-ЯЁ][а-яё]+\s?[А-ЯЁ]?[а-яё]*"
                           title="Введите название города на русском языке"
                           required>
                </div>
                <div class="form-group">
                    <label for="address">Адрес доставки*</label>
                    <textarea id="address" name="address" required minlength="10"></textarea>
                </div>
                <div class="form-group">
                    <label for="comment">Комментарий к заказу</label>
                    <textarea id="comment" name="comment" maxlength="500"></textarea>
                </div>

                <button type="submit" class="submit-order">Подтвердить заказ</button>
            </form>
        </div>
    </div>
</main>

<script>
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        let phoneInput = document.getElementById('phone').value.trim();
        let cityInput = document.getElementById('city').value.trim();
        let addressInput = document.getElementById('address').value.trim();
        let phonePattern = /^\+7\d{10}$/;

        if (!phonePattern.test(phoneInput)) {
            alert('Пожалуйста, введите телефон в формате +7XXXXXXXXX');
            e.preventDefault();
            return false;
        }

        if (cityInput.length < 2 || !/^[А-ЯЁ][а-яё\s\-]+$/i.test(cityInput)) {
            alert('Пожалуйста, введите корректное название города на русском.');
            e.preventDefault();
            return false;
        }

        if (addressInput.length < 10) {
            alert('Адрес должен содержать не менее 10 символов.');
            e.preventDefault();
            return false;
        }

        return true;
    });
</script>

</body>
</html>