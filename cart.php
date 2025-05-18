<?php
session_start();

$pageTitle = "Корзина покупок";
include 'header.php';

// Подключение к БД
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

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $itemKey = $_POST['item_key'];
        unset($_SESSION['cart'][$itemKey]);
        header("Location: cart.php?msg=" . urlencode("Товар удалён из корзины."));
        exit();
    } elseif (isset($_POST['update_quantity'])) {
        $itemKey = $_POST['item_key'];
        $quantity = intval($_POST['quantity']);
        if ($quantity > 0) {
            $_SESSION['cart'][$itemKey] = $quantity;
        } else {
            unset($_SESSION['cart'][$itemKey]);
        }
        header("Location: cart.php?msg=" . urlencode("Количество обновлено."));
        exit();
    } elseif (isset($_POST['apply_coupon'])) {
        $coupon = strtoupper(trim($_POST['coupon_code']));
        $coupons = [
            'SUMMER20' => 20,
            'WELCOME10' => 10,
            'DISCOUNT5' => 5,
        ];

        if (!empty($coupon)) {
            if (array_key_exists($coupon, $coupons)) {
                $_SESSION['coupon'] = $coupon;
                $_SESSION['discount_percent'] = $coupons[$coupon];
                header("Location: cart.php?msg=" . urlencode("Скидка {$_SESSION['discount_percent']}% применена!"));
                exit();
            } else {
                header("Location: cart.php?msg=" . urlencode("Неверный промокод."));
                exit();
            }
        } else {
            unset($_SESSION['coupon'], $_SESSION['discount_percent']);
            header("Location: cart.php");
            exit();
        }
    } elseif (isset($_POST['remove_coupon'])) {
        unset($_SESSION['coupon'], $_SESSION['discount_percent']);
        header("Location: cart.php");
        exit();
    }
}

// Получаем товары из корзины
$cartItems = [];
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $itemKey => $quantity) {
        list($productId, $size) = explode('_', $itemKey);

        try {
            $stmt = $pdo->prepare("
                SELECT p.*, s.size_value 
                FROM products p
                JOIN sizes s ON s.size_value = ?
                WHERE p.id = ?
            ");
            $stmt->execute([$size, $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $product['quantity'] = $quantity;
                $product['total_price'] = $product['price'] * $quantity;
                $totalPrice += $product['total_price'];
                $cartItems[] = $product;
            }
        } catch (PDOException $e) {
            continue;
        }
    }
}

// Применение скидки
$discountPercent = $_SESSION['discount_percent'] ?? 0;
$appliedCoupon = $_SESSION['coupon'] ?? '';
$discountAmount = $totalPrice * ($discountPercent / 100);
$finalPrice = $totalPrice - $discountAmount;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <main class="cart-page">
        <h1>Ваша корзина</h1>

        <!-- Сообщения -->
        <?php if (!empty($_GET['msg'])): ?>
            <div class="alert"><?= htmlspecialchars(urldecode($_GET['msg'])) ?></div>
        <?php endif; ?>

        <!-- Форма ввода промокода -->
        <form method="post" class="coupon-form">
            <label for="coupon_code">Введите промокод:</label>
            <input type="text" id="coupon_code" name="coupon_code" placeholder="например: SUMMER20" value="<?= $appliedCoupon ?>">
            <button type="submit" name="apply_coupon" class="btn small">Применить</button>
            <?php if ($appliedCoupon): ?>
                <button type="submit" name="remove_coupon" class="btn small remove-coupon">Удалить промокод</button>
            <?php endif; ?>
        </form>

        <?php if ($appliedCoupon): ?>
            <p><strong>Активный промокод:</strong> <?= $appliedCoupon ?> (<?= $discountPercent ?>%)</p>
        <?php endif; ?>

        <!-- Список товаров -->
        <?php if (!empty($cartItems)): ?>
            <section class="cart-items">
                <?php foreach ($cartItems as $item): 
                    $itemKey = $item['id'] . '_' . $item['size_value'];
                ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>Размер: <?= htmlspecialchars($item['size_value']) ?></p>
                            <p>Цена: <?= number_format($item['price'], 2) ?> ₽</p>

                            <!-- Изменение количества -->
                            <form method="post" style="display:inline-block;">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1"  max="10" style="width: 60px;">
                                <input type="hidden" name="item_key" value="<?= $itemKey ?>">
                                <button type="submit" name="update_quantity" class="btn small">Обновить</button>
                            </form>

                            <!-- Удаление товара -->
                            <form method="post" style="display:inline-block; margin-left: 10px;">
                                <input type="hidden" name="item_key" value="<?= $itemKey ?>">
                                <button type="submit" name="remove_item" class="btn small" onclick="return confirm('Вы уверены, что хотите удалить этот товар?')">Удалить</button>
                            </form>

                            <p><strong>Итого:</strong> <?= number_format($item['total_price'], 2) ?> ₽</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Итоговая цена -->
            <section class="cart-summary">
                <h2>Итог заказа</h2>
                <ul>
                    <li><strong>Общая сумма:</strong> <?= number_format($totalPrice, 2) ?> ₽</li>
                    <?php if ($discountPercent > 0): ?>
                        <li><strong>Скидка (<?= $discountPercent ?>%):</strong> -<?= number_format($discountAmount, 2) ?> ₽</li>
                    <?php endif; ?>
                    <li><strong>К оплате:</strong> <?= number_format($finalPrice, 2) ?> ₽</li>
                </ul>
                <a href="checkout.php" class="btn checkout-btn">Оформить заказ</a>
                <a href="catalog.php" class="btn continue-shopping">Продолжить покупки</a>
            </section>

        <?php else: ?>
            <p>Ваша корзина пуста</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
