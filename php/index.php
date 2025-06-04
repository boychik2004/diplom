<?php
session_start();
$pageTitle = "SportStyle - Будь в движении!";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>

    <!-- Блок с баннерами брендов -->
    <section class="brands-section">
        <h2>Наши партнёры и любимые бренды</h2>
        <p class="brands-subtitle">Мы работаем только с проверенными и качественными производителями спортивной обуви.</p>

        <!-- Список брендов с текстом под каждым -->
        <div class="brands-list">
            <div class="brand-item">
                <img src="images/brand-nike.png" alt="Nike">
                <p><strong>Nike</strong> — лидер в мире спортивных технологий. Известна своими инновационными решениями и стильным дизайном.</p>
            </div>
            <div class="brand-item">
                <img src="images/brand-adidas.png" alt="Adidas">
                <p><strong>Adidas</strong> — сочетает спорт и моду, предлагая комфортную обувь как для тренировок, так и для повседневной носки.</p>
            </div>
            <div class="brand-item">
                <img src="images/brand-puma.png" alt="Puma">
                <p><strong>Puma</strong> — воплощение стиля и скорости. Отличный выбор для тех, кто следит за модой и активно проводит время.</p>
            </div>
            <div class="brand-item">
                <img src="images/brand-reebok.png" alt="Reebok">
                <p><strong>Reebok</strong> — фокус на функциональности и здоровом образе жизни. Подходит для силовых и кардио тренировок.</p>
            </div>
            <div class="brand-item">
                <img src="images/brand-newbalance.png" alt="New Balance">
                <p><strong>New Balance</strong> — эталон комфорта и анатомической поддержки. Идеальны для длительного ношения и прогулок.</p>
            </div>
        </div>
    </section>

    <!-- Основной блок с описанием каталога -->
    <section class="catalog-preview">
        <h2>Наш каталог – ваш идеальный выбор для активной жизни!</h2>
        <p class="subtitle">Мы предлагаем обувь, которая подойдет для любых целей и стилей.</p>

        <!-- Слайдер фич -->
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

            <!-- Навигация по слайдам -->
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

<!-- JS для автоматического слайдера -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.feature-item');
    const dots = document.querySelectorAll('.feature-dot');
    let currentIndex = 0;
    const slideInterval = 3000;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    function autoSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    }

    setInterval(autoSlide, slideInterval);

    // Кнопки управления
    document.querySelector('.feature-arrow.next')?.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    });

    document.querySelector('.feature-arrow.prev')?.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(currentIndex);
    });

    // Точки навигации
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            showSlide(currentIndex);
        });
    });
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>