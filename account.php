<?php
session_start();
include 'db_connect.php';

// Проверка, что пользователь залогинен
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получаем информацию о пользователе из базы данных
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Если пользователь не найден, редирект на страницу входа
if (!$user) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ваш аккаунт</title>
    <link rel="stylesheet" href="account.css">
</head>
<body>
    <header>
        <h1>Ваш аккаунт</h1>
        <nav class="nav">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="cart.php">Корзина</a>
            <a href="logout.php" class="logout-btn">Выход</a>
        </nav>
    </header>

    <main>
        <section class="account-info">
            <h2>Добро пожаловать, <?= htmlspecialchars($user['username']); ?>!</h2>
            <div class="info-details">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                <p><strong>Фамилия:</strong> <?= htmlspecialchars($user['last_name']); ?></p>
                <p><strong>Город:</strong> <?= htmlspecialchars($user['city']); ?></p>
                <p><strong>Дата регистрации:</strong> <?= $user['created_at']; ?></p>
            </div>
            <div class="action-btn">
                <a href="edit_profile.php" class="btn">Редактировать профиль</a>
            </div>
        </section>
    </main>
</body>
</html>
