<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$pageTitle = "Редактировать товар";
include 'header.php';

// Подключение к базе данных
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';

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

// Получение данных товара
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin.php");
    exit;
}

try {
    // Получаем информацию о товаре
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header("Location: admin.php");
        exit;
    }
    
    // Получаем выбранные размеры для этого товара
    $stmt = $pdo->prepare("SELECT size_id FROM product_sizes WHERE product_id = ?");
    $stmt->execute([$id]);
    $selectedSizes = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    die("Ошибка при получении товара: " . $e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $gender = $_POST['gender'];
    $purpose = $_POST['purpose'];
    $newSelectedSizes = $_POST['sizes'] ?? [];
    $currentImage = $product['image'];
    
    // Обработка загрузки нового изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Удаляем старое изображение, если оно существует
        if ($currentImage && file_exists($currentImage)) {
            unlink($currentImage);
        }
        
        $fileName = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . uniqid() . '_' . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $currentImage = $targetPath;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // Обновляем товар
        $size = !empty($newSelectedSizes) ? $newSelectedSizes[0] : NULL; // Для совместимости
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, size = ?, gender = ?, purpose = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $size, $gender, $purpose, $currentImage, $id]);
        
        // Удаляем старые размеры
        $stmt = $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // Добавляем новые размеры
        foreach ($newSelectedSizes as $sizeId) {
            $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id) VALUES (?, ?)");
            $stmt->execute([$id, $sizeId]);
        }
        
        $pdo->commit();
        
        $_SESSION['message'] = "Товар успешно обновлен";
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при обновлении товара: " . $e->getMessage());
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
            <h1>Редактировать товар</h1>
            
            <form action="admin_edit.php?id=<?= $id ?>" method="post" enctype="multipart/form-data" class="product-form">
                <div class="form-group">
                    <label for="name">Название:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Цена (руб.):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="gender">Пол:</label>
                    <select id="gender" name="gender" required>
                        <option value="male" <?= $product['gender'] == 'male' ? 'selected' : '' ?>>Мужская</option>
                        <option value="female" <?= $product['gender'] == 'female' ? 'selected' : '' ?>>Женский</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="purpose">Назначение:</label>
                    <select id="purpose" name="purpose" required>
                        <option value="running" <?= $product['purpose'] == 'running' ? 'selected' : '' ?>>Для бега</option>
                        <option value="training" <?= $product['purpose'] == 'training' ? 'selected' : '' ?>>Для тренировок</option>
                        <option value="casual" <?= $product['purpose'] == 'casual' ? 'selected' : '' ?>>Повседневная</option>
                        <option value="professional" <?= $product['purpose'] == 'professional' ? 'selected' : '' ?>>Профессиональная</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Изображение:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($product['image']): ?>
                        <div class="current-image">
                            <p>Текущее изображение:</p>
                            <img src="<?= $product['image'] ?>" alt="Текущее изображение" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Доступные размеры:</label>
                    <div class="size-checkboxes">
                        <?php foreach ($allSizes as $size): ?>
                            <div class="size-checkbox">
                                <input type="checkbox" id="size_<?= $size['id'] ?>" name="sizes[]" value="<?= $size['id'] ?>"
                                    <?= in_array($size['id'], $selectedSizes) ? 'checked' : '' ?>>
                                <label for="size_<?= $size['id'] ?>"><?= $size['size_value'] ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить изменения</button>
                    <a href="admin.php" class="btn cancel">Отмена</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
