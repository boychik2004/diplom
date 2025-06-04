<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$pageTitle = "Добавить товар";
include 'header.php';

// Подключаем БД
require_once 'config/database.php'; // ← Теперь $pdo доступен отсюда

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Получаем все возможные размеры
try {
    $sizesStmt = $pdo->query("SELECT * FROM sizes ORDER BY size_value");
    $allSizes = $sizesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении размеров: " . $e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $gender = $_POST['gender'];
    $purpose = $_POST['purpose'];
    $selectedSizes = $_POST['sizes'] ?? [];
    
    // Обработка загрузки изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . uniqid() . '_' . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $targetPath;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // Добавляем товар (сохраняем для совместимости поле size, но оно будет необязательным)
        $size = !empty($selectedSizes) ? $selectedSizes[0] : NULL; // Первый выбранный размер для совместимости
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, size, gender, purpose, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $size, $gender, $purpose, $image]);
        $productId = $pdo->lastInsertId();
        
        // Добавляем выбранные размеры
        foreach ($selectedSizes as $sizeId) {
            $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id) VALUES (?, ?)");
            $stmt->execute([$productId, $sizeId]);
        }
        
        $pdo->commit();
        
        $_SESSION['message'] = "Товар успешно добавлен";
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при добавлении товара: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .size-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .size-checkbox {
            display: flex;
            align-items: center;
        }
        .size-checkbox input {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Меню</h2>
            <ul>
                <li><a href="admin.php">Товары</a></li>
                <li><a href="admin_orders.php">Заказы</a></li>
                <li><a href="admin_logout.php">Выйти</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <h1>Добавить новый товар</h1>
            
            <form action="admin_add.php" method="post" enctype="multipart/form-data" class="product-form">
                <div class="form-group">
                    <label for="name">Название:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Цена (руб.):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="gender">Пол:</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Мужская</option>
                        <option value="female">Женский</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="purpose">Назначение:</label>
                    <select id="purpose" name="purpose" required>
                        <option value="running">Для бега</option>
                        <option value="training">Для тренировок</option>
                        <option value="casual">Повседневная</option>
                        <option value="professional">Профессиональная</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Изображение:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label>Доступные размеры:</label>
                    <div class="size-checkboxes">
                        <?php foreach ($allSizes as $size): ?>
                            <div class="size-checkbox">
                                <input type="checkbox" id="size_<?= $size['id'] ?>" name="sizes[]" value="<?= $size['id'] ?>">
                                <label for="size_<?= $size['id'] ?>"><?= $size['size_value'] ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Добавить товар</button>
                    <a href="admin.php" class="btn cancel">Отмена</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>