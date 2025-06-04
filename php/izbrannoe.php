<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Избранное";

try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.price, p.image 
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка загрузки избранного: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Избранное</title>
    <link rel="stylesheet" href="wishlist.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="wishlist-page">
    <h1 class="page-title">Избранное</h1>

    <?php if (empty($wishlistItems)): ?>
        <p class="empty-message">Ваш список избранного пуст.</p>
    <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlistItems as $item): 
                $price = number_format($item['price'], 0, '', ' ');
            ?>
                <div class="wishlist-item">
                    <a href="product.php?id=<?= $item['id'] ?>">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="price"><?= $price ?> руб.</p>
                        </div>
                    </a>
                    <button class="btn btn-remove-wishlist" data-product-id="<?= $item['id'] ?>">Удалить из избранного</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');

            fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, action: 'remove' })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      location.reload(); // Обновляем страницу
                  } else {
                      alert('Ошибка: ' + data.message);
                  }
              });
        });
    });
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>