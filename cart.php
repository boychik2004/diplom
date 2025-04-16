<?php
session_start();

// Подключение к базе данных
$host = '127.0.0.1'; // Хост
$port = '3308'; // Порт (по умолчанию 3306)
$dbname = 'alexis222w_shoes'; // Имя базы данных
$username = 'alexis222w_shoes'; // Имя пользователя
$password = 'G2V4PB4P5k*AZW3D'; // Пароль

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Ошибка подключения к базе данных: " . $e->getMessage(), 3, "error.log");
    die("Произошла ошибка при подключении к базе данных. Пожалуйста, попробуйте позже.");
}

// Проверка, есть ли товары в корзине
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Получаем товары из корзины
    $cart_items = $_SESSION['cart'];

    // Получаем информацию о каждом товаре из базы данных
    $product_ids = array_keys($cart_items);
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id IN (' . implode(',', $product_ids) . ')');
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Сумма корзины
    $total_price = 0;
    foreach ($products as $product) {
        $total_price += $product['price'] * $cart_items[$product['id']]['quantity'];
    }

    // Выводим товары корзины
    echo '<ul>';
    foreach ($products as $product) {
        $quantity = $cart_items[$product['id']]['quantity'];
        $total_price_item = $product['price'] * $quantity;
        echo "<li>{$product['name']} (x$quantity) - $total_price_item руб.</li>";
    }
    echo '</ul>';
    echo "<p>Общая сумма: $total_price руб.</p>";
} else {
    echo '<p>Ваша корзина пуста.</p>';
}
?>
