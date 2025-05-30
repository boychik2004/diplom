<?php
session_start();

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Подключение к БД
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Проверка пользователя
if (!isset($_SESSION['user_id'])) {
    die("Пользователь не авторизован");
}

// Сбор данных из формы
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$comment = trim($_POST['comment'] ?? '');

// Валидация
$errors = [];

if (empty($name)) $errors[] = "Введите ваше имя";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Укажите корректный email";
if (empty($phone)) $errors[] = "Укажите телефон";
if (empty($address)) $errors[] = "Укажите адрес доставки";

if (!empty($errors)) {
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "<a href='javascript:history.back()'>Назад</a>";
    exit;
}

// Подсчёт общей суммы
$totalAmount = 0;
foreach ($_SESSION['cart'] as $itemKey => $quantity) {
    list($productId, $size) = explode('_', $itemKey);
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $totalAmount += $product['price'] * $quantity;
    }
}

// Применение скидки (если есть)
$discountPercent = $_SESSION['discount_percent'] ?? 0;
$finalAmount = $totalAmount - ($totalAmount * ($discountPercent / 100));

// Вставляем заказ
$stmt = $pdo->prepare("
    INSERT INTO orders (
        user_id,
        customer_name,
        email,
        phone,
        address,
        comment,
        total_amount,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
  $_SESSION['user_id'],
  $name,
  $email,
  $phone,
  $address,
  $comment,
  $finalAmount,
  'pending'
]);

$orderId = $pdo->lastInsertId();

// Добавляем товары в заказ
foreach ($_SESSION['cart'] as $itemKey => $quantity) {
    list($productId, $size) = explode('_', $itemKey);
    $stmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            size,
            quantity,
            price
        ) VALUES (?, ?, ?, ?, (SELECT price FROM products WHERE id = ?))
    ");
    $stmt->execute([$orderId, $productId, $size, $quantity, $productId]);
}

// Очищаем корзину
unset($_SESSION['cart']);
unset($_SESSION['coupon']);
unset($_SESSION['discount_percent']);

// Перенаправляем на страницу успеха
header("Location: order_success.php?order_id=$orderId");
exit;
?>
