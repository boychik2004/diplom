# 🛍️ Каталог спортивной обуви 
# Ссылка:https://sportstyle.akrapov1c.ru/


Это интернет-каталог спортивной обуви. Проект реализован в рамках дипломной работы по специальности **09.02.07 Информационные системы и программирование**.

## 📌 Описание

Каталог позволяет:
- Просматривать товары с фильтрацией по размерам, полу и назначению.
- Добавлять товары в корзину, избранное и оформлять заказы.
- Управлять аккаунтом пользователя (просмотр истории заказов).
- Работать с админ-панелью (добавление/редактирование товаров и заказов).

## 🧩 Технологии

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **СУБД:** MySQL + phpMyAdmin

## 🗂 Структура проекта

## 📃 ERD-дииограмм
![22](https://github.com/user-attachments/assets/8391a20d-a0b4-4baf-9f77-95292787ee5e)

На основе твоего описания таблиц (`products`, `orders`, `users` и т.д.), можно создать SQL-скрипт:

## 1. Таблица users — пользователи
Содержит данные о пользователях (имя, email, пароль, дата регистрации).
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 2. Таблица products — товары (обувь, шнурки, стельки)
Содержит информацию о товарах: название, описание, цена, пол, назначение, изображение.
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    gender ENUM('male', 'female', 'unisex') NOT NULL,
    purpose ENUM('running', 'training', 'casual', 'professional') NOT NULL,
    color VARCHAR(50) NOT NULL DEFAULT 'black',
    type ENUM('shoes', 'laces', 'insoles') NOT NULL DEFAULT 'shoes',
    image VARCHAR(255)
);
```

## 3. Таблица sizes — размеры обуви
Список всех доступных размеров (например: 36, 37, 40 и т.д.).
```sql
CREATE TABLE sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    size_value VARCHAR(10) NOT NULL
);
```

## 4. Таблица product_sizes — связь товаров с размерами
Реализует связь "многие ко многим" между товарами и размерами.
```sql
CREATE TABLE product_sizes (
    product_id INT NOT NULL,
    size_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (size_id) REFERENCES sizes(id),
    PRIMARY KEY (product_id, size_id)
);
```

## 5. Таблица orders — заказы
Содержит основную информацию о заказах: пользователь, сумма, статус, дата.
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 6. Таблица order_items — элементы заказов
Содержит детали каждого товара в заказе: какой товар, сколько, цена на момент покупки.
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

## 7. Таблица wishlist — избранное
Хранит связи между пользователями и товарами, добавленными в "избранное".
```sql
CREATE TABLE wishlist (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    PRIMARY KEY (user_id, product_id)
);
```
## Частички кода 

## 1. Добавление товара в корзину 
```sql
<h2><?= htmlspecialchars($product['name']) ?></h2>
<p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
<p><strong>Цена:</strong> <?= number_format($product['price'], 2, ',', ' ') ?> ₽</p>

<input type="number" class="quantity-input" value="1" min="1">
<select id="size-select">
    <option value="">Выберите размер</option>
    <?php foreach ($availableSizes as $size): ?>
        <option value="<?= $size ?>"><?= $size ?></option>
    <?php endforeach; ?>
</select>

<button id="addToCartBtn" data-product-id="<?= $product['id'] ?>">
    Добавить в корзину
</button>
```

## С помощью JavaScript, собираются данные о товаре: его идентификатор (через атрибут data-product-id), количество (из поля ввода) и размер (пользователь должен его выбрать). Перед добавлением проверяется, указан ли размер — если нет, выводится предупреждение.

## 2. Добавление товара в избранное 
```sql
addToCartBtn.addEventListener('click', function (e) {
    const productId = this.getAttribute('data-product-id');
    const size = selectedSizeInput.value;
    fetch('add_to_cart.php', {
        method: 'POST',
        body: JSON.stringify({ product_id: productId, quantity: 1, size: size })
    });
});
```

## Извлекается уникальный идентификатор товара из пользовательского атрибута data-product-id. Далее осуществляется отправка асинхронного POST-запроса на серверный обработчик add_to_wishlist.php с помощью метода fetch, в теле которого передаётся ID товара.
