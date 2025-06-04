<?php
session_start();
$pageTitle = "Избранное";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="css/catalog.css">
</head>
<body>
    <main>
        <h1>Избранное</h1>
        <section class="product-list" id="favorites-list">
            <p>Нет товаров в избранном</p>
        </section>
    </main>

    <script>
        function loadFavorites() {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '{}');
            const container = document.getElementById('favorites-list');
            container.innerHTML = '';

            if (Object.keys(favorites).length === 0) {
                container.innerHTML = '<p>В избранном пока ничего нет</p>';
                return;
            }

            Object.entries(favorites).forEach(([id, item]) => {
                const div = document.createElement('div');
                div.className = 'product-item';
                div.innerHTML = `
                    <img src="${item.image}" alt="${item.name}">
                    <h2>${item.name}</h2>
                    <p class="price">${item.price} руб.</p>
                    <div class="actions">
                        <a href="product.php?id=${id}" class="btn">Посмотреть</a>
                        <button class="btn remove-from-favorites" data-product-id="${id}">Удалить</button>
                    </div>
                `;
                container.appendChild(div);
            });

            // Обработчики удаления
            document.querySelectorAll('.remove-from-favorites').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.getAttribute('data-product-id');
                    const favorites = JSON.parse(localStorage.getItem('favorites') || '{}');
                    delete favorites[productId];
                    localStorage.setItem('favorites', JSON.stringify(favorites));
                    loadFavorites();
                });
            });
        }

        window.onload = loadFavorites;
    </script>
</body>
</html>