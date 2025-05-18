<?php
session_start();
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    // Пользователь не авторизован
    header('Location: login.php');
    exit();
}
// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Неправильный метод запроса. Используйте POST.']);
    exit;
}

// Получение и проверка входных данных
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Неверный формат JSON данных']);
    exit;
}

// Обязательные поля
$requiredFields = ['product_id', 'quantity', 'size'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Не указано поле: {$field}"]);
        exit;
    }
}

// Валидация данных
$productId = filter_var($data['product_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
$size = filter_var($data['size'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 36, 'max_range' => 50]]);

if ($productId === false || $quantity === false || $size === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Некорректные данные',
        'errors' => [
            'product_id' => $productId === false ? 'Должно быть целым числом' : null,
            'quantity' => $quantity === false ? 'Должно быть числом от 1 до 10' : null,
            'size' => $size === false ? 'Должно быть размером от 36 до 50' : null
        ]
    ]);
    exit;
}

// Подключение к базе данных для проверки существования товара и размера
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'G2V4PB4P5k*AZW3D';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверка существования товара и доступности размера
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM products p
        JOIN product_sizes ps ON p.id = ps.product_id
        JOIN sizes s ON ps.size_id = s.id
        WHERE p.id = ? AND s.size_value = ?
    ");
    $stmt->execute([$productId, $size]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Товар с указанным размером не найден']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при проверке товара: ' . $e->getMessage()]);
    exit;
}

// Инициализация корзины в сессии, если её нет
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ключ корзины - комбинация ID товара и размера
$cartKey = "{$productId}_{$size}";

// Добавление товара в корзину
if (isset($_SESSION['cart'][$cartKey])) {
    $_SESSION['cart'][$cartKey] += $quantity;
} else {
    $_SESSION['cart'][$cartKey] = $quantity;
}

// Возвращаем обновленные данные корзины
$totalItems = array_sum($_SESSION['cart']);

echo json_encode([
    'success' => true, 
    'message' => 'Товар добавлен в корзину',
    'cart' => [
        'total_items' => $totalItems,
        'items' => $_SESSION['cart']
    ]
]);
?>