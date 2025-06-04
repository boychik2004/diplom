<?php
session_start();
// Подключение к базе данных
include 'db_connect.php';

$error = ''; // Инициализируем переменную ошибки

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Обработка входа
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Успешный вход
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: catalog.php');
            exit;
        } else {
            $error = "Неверное имя пользователя или пароль.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <header>
        <h1>Добро пожаловать в наш каталог</h1>
        <nav class="nav">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="cart.php">Корзина</a>
            <a href="izbrannoe.php">Избранное</a>
        </nav>
    </header>

    <main>
        <section class="auth-forms">
            <div class="form-container">
                <form method="post" class="auth-form">
                    <h2>Вход в аккаунт</h2>
                    <?php if (!empty($error)): ?>
                        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>

                    <!-- Блок с двумя кнопками -->
                    <div class="button-container">
                        <button type="submit" name="login" class="btn">Войти</button>
                        <a href="register.php" class="btn">Зарегистрироваться</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>