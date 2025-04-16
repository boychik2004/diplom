<?php
session_start();

// Проверяем, есть ли корзина в сессии
if (isset($_SESSION['cart'])) {
    // Считаем общее количество товаров в корзине
    $totalItems = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalItems += $item['quantity'];
    }

    // Отправляем количество товаров в ответе
    echo json_encode(['count' => $totalItems]);
} else {
    echo json_encode(['count' => 0]);
}
?>
