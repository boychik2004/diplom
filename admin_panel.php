<?php
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Подключение к базе данных
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Обработка добавления нового товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $gender = $_POST['gender'];
    $purpose = $_POST['purpose'];
    
    // Обработка загрузки изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $targetPath;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, size, gender, purpose, image) 
                              VALUES (:name, :description, :price, :size, :gender, :purpose, :image)");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':size' => $size,
            ':gender' => $gender,
            ':purpose' => $purpose,
            ':image' => $image
        ]);
        $_SESSION['success'] = "Товар успешно добавлен!";
        header('Location: admin_panel.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при добавлении товара: " . $e->getMessage();
        header('Location: admin_panel.php');
        exit;
    }
}

// Обработка удаления товара
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        // Сначала получаем информацию о изображении, чтобы удалить файл
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && !empty($product['image']) && file_exists($product['image'])) {
            unlink($product['image']);
        }
        
        // Затем удаляем запись из базы данных
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['success'] = "Товар успешно удален!";
        header('Location: admin_panel.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при удалении товара: " . $e->getMessage();
        header('Location: admin_panel.php');
        exit;
    }
}

// Получение списка всех товаров
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении списка товаров: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Управление товарами</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="admin-nav">
                <h1>Админ-панель</h1>
                <div>
                    <a href="admin_panel.php">Управление товарами</a>
                    <a href="logout.php">Выйти</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <h2>Добавить новый товар</h2>
        <form method="post" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Название:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Цена:</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="size">Размер:</label>
                <input type="number" id="size" name="size" min="36" max="50" required>
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
            
            <button type="submit" name="add_product" class="btn">Добавить товар</button>
        </form>

        <h2>Список товаров</h2>
        <div class="product-list">
            <?php if (!empty($products)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Цена</th>
                            <th>Размер</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" width="50">
                                    <?php endif; ?>
                                </td>
                                <td><?= $product['name'] ?></td>
                                <td><?= number_format($product['price'], 2) ?> руб.</td>
                                <td><?= $product['size'] ?></td>
                                <td>
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-edit">Редактировать</a>
                                    <a href="admin_panel.php?delete=<?= $product['id'] ?>" class="btn btn-delete" onclick="return confirm('Вы уверены, что хотите удалить этот товар?')">Удалить</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Товары не найдены.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
