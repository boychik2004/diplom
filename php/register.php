<?php
session_start();
include 'db_connect.php';

// Проверка, если форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы и очистка от пробелов
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $city = trim($_POST['city']);

    // Проверка пароля
    if (strlen($password) < 8) {
        $error = "Пароль должен быть не менее 8 символов.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        // Проверка имени пользователя
        $error = "Имя пользователя должно быть от 3 до 20 символов.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        // Проверка на допустимые символы в имени пользователя
        $error = "Имя пользователя может содержать только буквы, цифры и подчеркивания.";
    } elseif (!preg_match("/^[а-яА-ЯёЁ]+$/u", $first_name)) {
        // Проверка имени на кириллицу (только русские буквы)
        $error = "Имя может содержать только русские буквы.";
    } elseif (!preg_match("/^[а-яА-ЯёЁ]+$/u", $last_name)) {
        // Проверка фамилии на кириллицу (только русские буквы)
        $error = "Фамилия может содержать только русские буквы.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Проверка валидности email
        $error = "Неверный формат email.";
    } elseif (!preg_match("/@(gmail\.com|yandex\.ru|mail\.ru|icloud\.com|colinsblare\.ru|akrapov1c\.ru)$/", $email)) {
        // Проверка домена email (разрешенные домены)
        $error = "Разрешены только почтовые адреса с доменами gmail.com, yandex.ru, mail.ru, icloud.com, colinsblare.ru, или akrapov1c.ru.";
    } else {
        // Проверка уникальности email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $existingEmail = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingEmail) {
            $error = "Этот email уже зарегистрирован.";
        } else {
            // Проверка уникальности username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $existingUsername = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUsername) {
                $error = "Это имя пользователя уже занято.";
            } else {
                // Хэширование пароля
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Добавление пользователя в базу данных
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, city) 
                                       VALUES (:username, :password, :email, :first_name, :last_name, :city)");
                if ($stmt->execute([ 
                    'username' => $username,
                    'password' => $hashedPassword,
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'city' => $city
                ])) {
                    // Получение ID пользователя после добавления в базу данных
                    $userId = $pdo->lastInsertId();

                    // Устанавливаем идентификатор пользователя в сессии
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;

                    // Перенаправляем на страницу аккаунта
                    header('Location: account.php');
                    exit;
                } else {
                    $error = "Ошибка при регистрации. Попробуйте снова.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <header>
        <h1>Регистрация</h1>
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
                    <h2>Создайте новый аккаунт</h2>
                    <?php if (isset($error)): ?>
                        <p style="color:red;"><?= $error ?></p>
                    <?php endif; ?>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="first_name">Имя:</label>
                    <input type="text" id="first_name" name="first_name" required>
                    <label for="last_name">Фамилия:</label>
                    <input type="text" id="last_name" name="last_name" required>
                    <label for="city">Город:</label>
                    <input type="text" id="city" name="city" required>
                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" required>
                    <label for="password">Пароль (от 8 символов):</label>
                    <input type="password" id="password" name="password" required>
                    <button type="submit" name="register" class="btn">Зарегистрироваться</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
