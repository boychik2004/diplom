<?php
header('Content-Type: application/json; charset=utf-8'); // Устанавливаем кодировку ответа

// Подключаем БД
require_once 'config/database.php'; // ← Теперь $pdo доступен отсюда

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    die(json_encode(['error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]));
}

// Получение поискового запроса
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($searchQuery)) {
    // Если запрос пустой, возвращаем все товары
    try {
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products, JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Ошибка при загрузке товаров: ' . $e->getMessage()]));
    }
} else {
    // Поиск товаров в базе данных
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :query OR description LIKE :query");
        $stmt->execute(['query' => "%$searchQuery%"]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products, JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Ошибка при поиске товаров: ' . $e->getMessage()]));
    }
}
?>