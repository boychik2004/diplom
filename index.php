<?php
session_start();
$pageTitle = "SportStyle - Будь в движении!";
include 'header.php';
?>
<main>
    <section class="catalog-preview">
        <h2>Наш каталог – ваш идеальный выбор для активной жизни!</h2>
        <p class="subtitle">Мы предлагаем обувь, которая подойдет для любых целей и стилей.</p>

        <div class="features-slider">
            <div class="feature-arrow prev">&#10094;</div>
            
            <div class="features-container">
                <div class="feature-item active">
                    <h3>Легкие беговые кроссовки</h3>
                    <p>Для тех, кто ценит скорость и комфорт.</p>
                    <img src="images/2.png" alt="Легкие беговые кроссовки">
                </div>
                <div class="feature-item">
                    <h3>Тренировочные модели с поддержкой</h3>
                    <p>Для эффективных тренировок и максимальной устойчивости.</p>
                    <img src="images/5.png" alt="Тренировочные кроссовки">
                </div>
                <div class="feature-item">
                    <h3>Универсальная обувь для города</h3>
                    <p>Стиль, который сочетается с любым образом жизни.</p>
                    <img src="images/6.png" alt="Обувь для города">
                </div>
                <div class="feature-item">
                    <h3>Специальные модели для профессионалов</h3>
                    <p>Для тех, кто требует от обуви большего.</p>
                    <img src="images/3.png" alt="Специальные модели для профессионалов">
                </div>
            </div>

            <div class="feature-arrow next">&#10095;</div>

            <div class="feature-nav">
                <span class="feature-dot active"></span>
                <span class="feature-dot"></span>
                <span class="feature-dot"></span>
                <span class="feature-dot"></span>
            </div>
        </div>

        <p class="cta-text">Мы тщательно отбираем каждую модель, чтобы вы могли наслаждаться качеством, удобством и стилем.</p>
        <a href="catalog.php" class="btn">Перейти в каталог</a>
    </section>
</main>

<script src="script.js" defer></script>
<?php include 'footer.php'; ?>
