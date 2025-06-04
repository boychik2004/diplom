<?php
session_start();
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: catalog.php");
    exit();
}
$productId = intval($_GET['id']);
$pageTitle = "Страница товара";

// Подключение к БД
$host = '127.0.0.1';
$port = '3308';
$dbname = 'alexis222w_shoes';
$username = 'alexis222w_shoes';
$password = 'UHABHc8Z9Y6F8X#2';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Получаем данные о товаре
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: catalog.php");
        exit();
    }

    // Форматируем цену
    $product['formatted_price'] = number_format($product['price'], 0, '', ' ');

    // Переводим enum в читабельный вид
    $product['gender_text'] = $product['gender'] == 'male' ? 'Мужская' : 'Женская';
    switch ($product['purpose']) {
        case 'running': $product['purpose_text'] = 'Для бега'; break;
        case 'training': $product['purpose_text'] = 'Для тренировок'; break;
        case 'casual': $product['purpose_text'] = 'Повседневная'; break;
        case 'professional': $product['purpose_text'] = 'Профессиональная'; break;
        default: $product['purpose_text'] = $product['purpose'];
    }

    // Получаем доступные размеры
    $stmt = $pdo->prepare("
        SELECT s.size_value 
        FROM product_sizes ps
        JOIN sizes s ON ps.size_id = s.id
        WHERE ps.product_id = ?
        ORDER BY s.size_value
    ");
    $stmt->execute([$productId]);
    $availableSizes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Проверяем, есть ли товар в избранном у пользователя
    $inWishlist = false;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        $inWishlist = $stmt->fetchColumn() > 0;
    }
} catch (PDOException $e) {
    die("Ошибка при получении данных: " . $e->getMessage());
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="tovar.css">
</head>
<body>

<!-- Уведомления -->
<div id="cart-notification" class="notification">Товар добавлен в корзину!</div>
<div id="wishlist-notification" class="notification">Товар <?= $inWishlist ? 'удалён из' : 'добавлен в' ?> избранное!</div>

<main class="product-page">

    <!-- Хлебные крошки -->
    <nav class="breadcrumbs">
        <a href="index.php">Главная</a> >
        <a href="catalog.php">Каталог</a> >
        <a href="izbrannoe.php">Избранное</a>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Основная информация о товаре -->
    <section class="product-detail">
        <div class="product-gallery">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="product-info">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price"><?= $product['formatted_price'] ?> руб.</p>

            <!-- Кнопка Избранное -->
            <button class="btn btn-wishlist <?= $inWishlist ? 'in-wishlist' : '' ?>" id="wishlist-button" data-product-id="<?= $productId ?>">
                <?= $inWishlist ? '❤ Удалить из избранного' : '♡ Добавить в избранное' ?>
            </button>

            <div class="product-meta">
                <p><strong>Пол:</strong> <?= $product['gender_text'] ?></p>
                <p><strong>Назначение:</strong> <?= $product['purpose_text'] ?></p>
            </div>

            <!-- Выбор размера -->
            <div class="size-selector">
                <strong>Размер:</strong>
                <?php if (!empty($availableSizes)): ?>
                    <div class="size-options">
                        <?php foreach ($availableSizes as $size): ?>
                            <div class="size-option" data-size="<?= htmlspecialchars($size) ?>">
                                <?= htmlspecialchars($size) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selected-size" name="size" value="">
                <?php else: ?>
                    <p class="no-sizes">Нет доступных размеров</p>
                <?php endif; ?>
            </div>

            <!-- Описание -->
            <div class="product-description">
                <h3>Описание</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <!-- Количество и кнопка -->
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="quantity-btn minus">−</button>
                    <input type="number" class="quantity-input" value="1" min="1" max="10">
                    <button class="quantity-btn plus">+</button>
                </div>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn add-to-cart" title="Войдите в аккаунт, чтобы купить">Войдите</a>
                <?php else: ?>
                    <button class="btn add-to-cart" data-product-id="<?= $productId ?>" <?= empty($availableSizes) ? 'disabled' : '' ?>>
                        Добавить в корзину
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Похожие товары -->
    <section class="related-products">
        <h2>Похожие товары</h2>
        <div class="related-grid">
            <?php
            try {
                // Показываем до 8 похожих товаров
                $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE purpose = ? AND id != ? LIMIT 8");
                $stmt->execute([$product['purpose'], $productId]);
                $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($relatedProducts) {
                    foreach ($relatedProducts as $related) {
                        $related['formatted_price'] = number_format($related['price'], 0, '', ' ');
                        echo '
                        <div class="related-item">
                            <a href="product.php?id=' . $related['id'] . '">
                                <img src="' . htmlspecialchars($related['image']) . '" alt="' . htmlspecialchars($related['name']) . '">
                                <h3>' . htmlspecialchars($related['name']) . '</h3>
                                <p class="price">' . $related['formatted_price'] . ' руб.</p>
                            </a>
                        </div>';
                    }
                } else {
                    echo '<p class="no-related">Нет похожих товаров</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="no-related">Не удалось загрузить похожие товары</p>';
            }
            ?>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sizeOptions = document.querySelectorAll('.size-option');
    const selectedSizeInput = document.getElementById('selected-size');
    const addToCartBtn = document.querySelector('.add-to-cart');
    const wishlistBtn = document.getElementById('wishlist-button');

    // Выбор размера
    sizeOptions.forEach(option => {
        option.addEventListener('click', function () {
            sizeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedSizeInput.value = this.getAttribute('data-size');
            if (addToCartBtn && addToCartBtn.tagName === 'BUTTON') {
                addToCartBtn.disabled = false;
            }
        });
    });

    // Изменение количества
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.quantity-selector').querySelector('.quantity-input');
            let value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max')) || 10;
            const min = parseInt(input.getAttribute('min')) || 1;
            if (this.classList.contains('minus') && value > min) {
                input.value = value - 1;
            } else if (this.classList.contains('plus') && value < max) {
                input.value = value + 1;
            }
        });
    });

    // Добавление в корзину
    if (addToCartBtn && addToCartBtn.tagName === 'BUTTON') {
        addToCartBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(document.querySelector('.quantity-input').value);
            const size = selectedSizeInput.value;

            if (!size) {
                alert('Пожалуйста, выберите размер');
                return;
            }

            const notification = document.getElementById('cart-notification');
            notification.textContent = 'Товар добавлен в корзину!';
            notification.classList.add('show');
            setTimeout(() => notification.classList.remove('show'), 2000);

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: quantity, size: size })
            }).then(response => response.json())
              .then(data => {
                  if (!data.success) {
                      alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                  }
              });
        });
    }

    // Добавление/удаление из избранного
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const isAlreadyInWishlist = this.classList.contains('in-wishlist');

            fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, action: isAlreadyInWishlist ? 'remove' : 'add' })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      const notification = document.getElementById('wishlist-notification');
                      notification.textContent = 'Товар ' + (isAlreadyInWishlist ? 'удалён из' : 'добавлен в') + ' избранное!';
                      notification.classList.add('show');
                      setTimeout(() => notification.classList.remove('show'), 2000);
                      if (isAlreadyInWishlist) {
                          wishlistBtn.textContent = '♡ Добавить в избранное';
                          wishlistBtn.classList.remove('in-wishlist');
                      } else {
                          wishlistBtn.textContent = '❤ Удалить из избранного';
                          wishlistBtn.classList.add('in-wishlist');
                      }
                  } else {
                      alert('Ошибка: ' + (data.message || 'Не удалось изменить статус избранного.'));
                  }
              });
        });
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>