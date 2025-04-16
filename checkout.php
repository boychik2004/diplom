<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Вы не вошли в аккаунт!";
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем товары в корзине
$stmt = $pdo->prepare('SELECT cart.id, products.name, products.price, cart.quantity FROM cart JOIN products ON cart.product_id = products.id WHERE cart.user_id = ?');
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Переносим товары в таблицу покупок
foreach ($cart_items as $item) {
    $stmt = $pdo->prepare('INSERT INTO purchases (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $item['product_id'], $item['quantity'], $item['price'] * $item['quantity']]);
}

// Очищаем корзину
$stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
$stmt->execute([$user_id]);

echo "Покупка успешно оформлена! Общая сумма: " . number_format($total_price, 2, ',', ' ') . " ₽";
?>
