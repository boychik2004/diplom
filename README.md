# 🛍️ Каталог спортивной обуви

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

```sql
-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица товаров
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    gender ENUM('male', 'female', 'unisex') NOT NULL,
    purpose VARCHAR(255),
    image VARCHAR(255)
);

-- Таблица размеров
CREATE TABLE sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    size_value VARCHAR(10) NOT NULL
);

-- Связь товаров и размеров
CREATE TABLE product_sizes (
    product_id INT NOT NULL,
    size_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (size_id) REFERENCES sizes(id),
    PRIMARY KEY (product_id, size_id)
);

-- Таблица заказов
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Таблица элементов заказов
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Таблица избранного
CREATE TABLE wishlist (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    PRIMARY KEY (user_id, product_id)
);



