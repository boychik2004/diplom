<?php
session_start(); // Старт сессии

$pageTitle = "Каталог спортивной обуви";
include 'header.php'; // Подключение общего хедера

// Проверка наличия драйвера PDO для MySQL
if (!extension_loaded('pdo_mysql')) {
    die("Ошибка: PDO MySQL драйвер не установлен. Пожалуйста, установите его для продолжения работы.");
}

// Подключение к базе данных
$host = '127.0.0.1'; // Хост
$port = '3308'; // Порт (по умолчанию 3306)
$dbname = 'alexis222w_shoes'; // Имя базы данных
$username = 'alexis222w_shoes'; // Имя пользователя
$password = 'G2V4PB4P5k*AZW3D'; // Пароль

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    error_log("Ошибка подключения к базе данных: " . $e->getMessage(), 3, "error.log");
    die("Произошла ошибка при подключении к базе данных. Пожалуйста, попробуйте позже.");
}

// Получение товаров из базы данных
try {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Ошибка при получении данных из базы данных: " . $e->getMessage(), 3, "error.log");
    die("Произошла ошибка при загрузке каталога. Пожалуйста, попробуйте позже.");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="catalog.css">
</head>
<body>
    <main>
        <!-- Поиск сверху -->
        <section class="search-bar">
            <input type="text" id="searchInput" placeholder="Поиск по каталогу...">
            <button id="searchButton">Найти</button>
        </section>

        <div class="catalog-container">
            <!-- Фильтры (справа) -->
            <aside class="filters">
                <h2>Фильтры</h2>
                <div class="filter-group">
                    <label for="size">Размер:</label>
                    <select id="size">
                        <option value="all">Все</option>
                        <option value="36">36</option>
                        <option value="37">37</option>
                        <option value="38">38</option>
                        <option value="39">39</option>
                        <option value="40">40</option>
                        <option value="41">41</option>
                        <option value="42">42</option>
                        <option value="43">43</option>
                        <option value="44">44</option>
                        <option value="45">45</option>
                        <option value="46">46</option>
                        <option value="47">47</option>
                        <option value="48">48</option>
                        <option value="49">49</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="gender">Пол:</label>
                    <select id="gender">
                        <option value="all">Все</option>
                        <option value="male">Мужская</option>
                        <option value="female">Женская</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="purpose">Назначение:</label>
                    <select id="purpose">
                        <option value="all">Все</option>
                        <option value="running">Для бега</option>
                        <option value="training">Для тренировок</option>
                        <option value="casual">Повседневная</option>
                        <option value="professional">Профессиональная</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="price">Цена:</label>
                    <select id="price">
                        <option value="all">Все</option>
                        <option value="0-3000">До 3000 руб.</option>
                        <option value="3000-5000">3000–5000 руб.</option>
                        <option value="5000-7000">5000–7000 руб.</option>
                        <option value="7000+">От 7000 руб.</option>
                    </select>
                </div>
                <button id="applyFilters" class="btn">Применить фильтры</button>
            </aside>

            <!-- Карточки товара (основная часть) -->
            <section class="product-list">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-item" data-size="<?= $product['size'] ?>" data-gender="<?= $product['gender'] ?>" data-purpose="<?= $product['purpose'] ?>" data-price="<?= $product['price'] ?>">
                            <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                            <h2><?= $product['name'] ?></h2>
                            <p><?= $product['description'] ?></p>
                            <p class="price"><?= $product['price'] ?> руб.</p>
                            <div class="actions">
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn">Посмотреть</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Товары не найдены.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- JavaScript для динамического поиска и фильтрации -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const applyFiltersButton = document.getElementById('applyFilters');
            const productList = document.querySelector('.product-list');
            
            // Функция для фильтрации товаров
            function filterProducts() {
                const sizeFilter = document.getElementById('size').value;
                const genderFilter = document.getElementById('gender').value;
                const purposeFilter = document.getElementById('purpose').value;
                const priceFilter = document.getElementById('price').value;
                
                const productItems = document.querySelectorAll('.product-item');
                
                productItems.forEach(item => {
                    const size = item.getAttribute('data-size');
                    const gender = item.getAttribute('data-gender');
                    const purpose = item.getAttribute('data-purpose');
                    const price = parseFloat(item.getAttribute('data-price'));
                    
                    let showItem = true;
                    
                    // Применяем фильтры
                    if (sizeFilter !== 'all' && size !== sizeFilter) {
                        showItem = false;
                    }
                    
                    if (genderFilter !== 'all' && gender !== genderFilter) {
                        showItem = false;
                    }
                    
                    if (purposeFilter !== 'all' && purpose !== purposeFilter) {
                        showItem = false;
                    }
                    
                    if (priceFilter !== 'all') {
                        if (priceFilter === '0-3000' && price > 3000) {
                            showItem = false;
                        } else if (priceFilter === '3000-5000' && (price <= 3000 || price > 5000)) {
                            showItem = false;
                        } else if (priceFilter === '5000-7000' && (price <= 5000 || price > 7000)) {
                            showItem = false;
                        } else if (priceFilter === '7000+' && price <= 7000) {
                            showItem = false;
                        }
                    }
                    
                    // Показываем/скрываем товар в зависимости от фильтров
                    item.style.display = showItem ? 'block' : 'none';
                });
            }
            
            // Обработчик для кнопки "Применить фильтры"
            applyFiltersButton.addEventListener('click', filterProducts);
            
            // Функция для поиска товаров
            function searchProducts() {
                const query = searchInput.value.trim().toLowerCase();
                
                if (query.length >= 2) {
                    const productItems = document.querySelectorAll('.product-item');
                    
                    productItems.forEach(item => {
                        const name = item.querySelector('h2').textContent.toLowerCase();
                        const description = item.querySelector('p').textContent.toLowerCase();
                        
                        if (name.includes(query) || description.includes(query)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                } else {
                    // Если запрос слишком короткий, показываем все товары
                    const productItems = document.querySelectorAll('.product-item');
                    productItems.forEach(item => {
                        item.style.display = 'block';
                    });
                }
            }
            
            // Обработчики событий для поиска
            searchInput.addEventListener('input', searchProducts);
            searchButton.addEventListener('click', searchProducts);
        });
    </script>
</body>
</html>