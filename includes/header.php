<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1><?php echo $pageTitle; ?></h1>
    <div class="nav">
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="cart.php">Корзина</a>
        <a href="izbrannoe.php">Избранное</a>
    </div>
    <?php if (isset($_SESSION['username'])): ?>
        <a href="account.php" class="login-btn">Аккаунт</a>
    <?php else: ?>
        <a href="login.php" class="login-btn">Вход в аккаунт</a>
    <?php endif; ?>
</header>