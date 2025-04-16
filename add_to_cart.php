<?php
session_start();

// Проверяем, что параметр product_id передан
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Если корзина еще не существует в сессии, создаем ее
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Если товар уже есть в корзине, увеличиваем его количество
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity']++;
    } else {
        // Если товара еще нет в корзине, добавляем его с количеством 1
        $_SESSION['cart'][$product_id] = ['quantity' => 1];
    }

    // Возвращаем успешный ответ
    echo json_encode(['status' => 'success']);
} else {
    // Если параметр не был передан, выводим ошибку
    echo json_encode(['status' => 'error', 'message' => 'Не указан идентификатор товара']);
}
?>
