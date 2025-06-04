<?php
session_start();
$pageTitle = "Каталог спортивной обуви";
include 'header.php';

if (!extension_loaded('pdo_mysql')) {
    die("PDO MySQL драйвер не установлен.");
}

// Подключаем БД
require_once 'config/database.php'; // ← Теперь $pdo доступен отсюда

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных");
}

try {
    $stmt = $pdo->query("
        SELECT p.*, GROUP_CONCAT(s.size_value ORDER BY s.size_value) as sizes_list
        FROM products p
        LEFT JOIN product_sizes ps ON p.id = ps.product_id
        LEFT JOIN sizes s ON ps.size_id = s.id
        GROUP BY p.id
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при загрузке товаров");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="css/catalog.css">
</head>
<body>
  <main>
    <section class="search-bar">
      <input type="text" id="searchInput" placeholder="Поиск по каталогу...">
      <button id="searchButton">Найти</button>
    </section>

    <div class="catalog-container">
      <aside class="filters">
        <h2>Фильтры</h2>

        <!-- Новая категория -->
        <div class="filter-group">
          <label for="category">Категория:</label>
          <select id="category">
            <option value="all">Все</option>
            <option value="sneakers">Кроссовки</option>
            <option value="accessories">Аксессуары</option>
          </select>
        </div>

        <!-- Размер -->
        <div class="filter-group">
          <label for="size">Размер:</label>
          <select id="size">
            <option value="all">Все</option>
            <?php for ($i = 36; $i <= 50; $i++): ?>
              <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <!-- Пол -->
        <div class="filter-group">
          <label for="gender">Пол:</label>
          <select id="gender">
            <option value="all">Все</option>
            <option value="male">Мужской</option>
            <option value="female">Женский</option>
          </select>
        </div>

        <!-- Назначение -->
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

        <!-- Тип -->
        <div class="filter-group">
          <label for="type">Тип:</label>
          <select id="type">
            <option value="all">Все</option>
            <option value="shoes">Обувь</option>
            <option value="laces">Шнурки</option>
            <option value="insoles">Стельки</option>
          </select>
        </div>

        <!-- Бренд -->
        <div class="filter-group">
          <label for="brand">Бренд:</label>
          <select id="brand">
            <option value="all">Все</option>
            <option value="adidas">Adidas</option>
            <option value="nike">Nike</option>
            <option value="puma">Puma</option>
            <option value="reebok">Reebok</option>
            <option value="new balance">New Balance</option>
          </select>
        </div>

        <!-- Цена -->
        <div class="filter-group">
          <label>Цена:</label>
          <div class="wrapper">
            <div class="price-input">
              <div class="field">
                <span>Мин</span>
                <input type="number" class="input-min" value="0" min="0" max="200000">
              </div>
              <div class="separator">-</div>
              <div class="field">
                <span>Макс</span>
                <input type="number" class="input-max" value="200000" min="0" max="200000">
              </div>
            </div>
            <div class="range-input">
              <input type="range" class="range-min" min="0" max="200000" value="0" step="100">
              <input type="range" class="range-max" min="0" max="200000" value="200000" step="100">
            </div>
          </div>
        </div>

        <button id="applyFilters" class="btn">Применить фильтры</button>
      </aside>

      <!-- Список товаров -->
      <section class="product-list">
        <?php foreach ($products as $product): 
            // Определяем категорию товара
            $category = '';
            if ($product['type'] === 'shoes') {
                $category = 'shoes';
            } elseif ($product['type'] === 'laces' || $product['type'] === 'insoles') {
                $category = 'accessories';
            }
        ?>
          <div class="product-item"
               data-sizes="<?= htmlspecialchars($product['sizes_list']) ?>"
               data-gender="<?= htmlspecialchars($product['gender']) ?>"
               data-purpose="<?= htmlspecialchars($product['purpose']) ?>"
               data-type="<?= htmlspecialchars($product['type']) ?>"
               data-category="<?= htmlspecialchars($category) ?>"
               data-price="<?= htmlspecialchars($product['price']) ?>">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p class="price"><?= number_format((float)$product['price'], 2, ',', ' ') ?> ₽</p>
            <div class="actions">
              <a href="product.php?id=<?= urlencode($product['id']) ?>" class="btn">Посмотреть</a>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    </div>
  </main>

  <script>
    const rangeInput = document.querySelectorAll(".range-input input"),
          priceInput = document.querySelectorAll(".price-input input"),
          applyFiltersButton = document.getElementById("applyFilters"),
          productItems = document.querySelectorAll(".product-item");

    let priceGap = 1000;

    // Логика слайдера цены
    priceInput.forEach(input => {
      input.addEventListener("input", e => {
        let minPrice = parseInt(priceInput[0].value),
            maxPrice = parseInt(priceInput[1].value);

        if (maxPrice - minPrice >= priceGap && maxPrice <= 200000) {
          if (e.target.classList.contains("input-min")) {
            rangeInput[0].value = minPrice;
          } else {
            rangeInput[1].value = maxPrice;
          }
        }
      });
    });

    rangeInput.forEach(input => {
      input.addEventListener("input", e => {
        let minVal = parseInt(rangeInput[0].value),
            maxVal = parseInt(rangeInput[1].value);
        if (maxVal - minVal < priceGap) {
          if (e.target.classList.contains("range-min")) {
            rangeInput[0].value = maxVal - priceGap;
          } else {
            rangeInput[1].value = minVal + priceGap;
          }
        } else {
          priceInput[0].value = minVal;
          priceInput[1].value = maxVal;
        }
      });
    });

    // Применение всех фильтров
    applyFiltersButton.addEventListener("click", () => {
      const sizeFilter = document.getElementById('size').value;
      const genderFilter = document.getElementById('gender').value;
      const purposeFilter = document.getElementById('purpose').value;
      const typeFilter = document.getElementById('type').value;
      const brandFilter = document.getElementById('brand').value.toLowerCase();
      const categoryFilter = document.getElementById('category').value;
      const minVal = parseInt(priceInput[0].value);
      const maxVal = parseInt(priceInput[1].value);

      productItems.forEach(item => {
        const sizes = item.getAttribute('data-sizes')?.split(',') || [];
        const gender = item.getAttribute('data-gender');
        const purpose = item.getAttribute('data-purpose');
        const type = item.getAttribute('data-type');
        const category = item.getAttribute('data-category');
        const price = parseFloat(item.getAttribute('data-price'));
        const name = item.querySelector('h2').textContent.toLowerCase();

        let show = true;

        if (sizeFilter !== 'all' && !sizes.includes(sizeFilter)) show = false;
        if (genderFilter !== 'all' && gender !== genderFilter) show = false;
        if (purposeFilter !== 'all' && purpose !== purposeFilter) show = false;
        if (typeFilter !== 'all' && type !== typeFilter) show = false;
        if (brandFilter !== 'all' && !name.includes(brandFilter)) show = false;
        if (price < minVal || price > maxVal) show = false;

        // Дополнительная проверка категории
        if (categoryFilter !== 'all') {
          if (categoryFilter === 'sneakers' && type !== 'shoes') show = false;
          if (categoryFilter === 'female' && gender !== 'female') show = false;
          if (categoryFilter === 'male' && gender !== 'male') show = false;
          if (categoryFilter === 'accessories' && type !== 'laces' && type !== 'insoles') show = false;
          if (categoryFilter === 'shoes' && type !== 'shoes') show = false;
        }

        item.style.display = show ? 'block' : 'none';
      });
    });

    // Поиск по названию и описанию
    document.getElementById('searchInput').addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();
      productItems.forEach(item => {
        const name = item.querySelector('h2').textContent.toLowerCase();
        const description = item.querySelector('p').textContent.toLowerCase();
        item.style.display = (name.includes(query) || description.includes(query)) ? 'block' : 'none';
      });
    });
  </script>
</body>
</html>