document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const searchButton = document.getElementById("searchButton");
    const applyFiltersButton = document.getElementById("applyFilters");
    const productList = document.querySelector(".product-list");

    function updateProductList(products) {
        productList.innerHTML = "";

        if (products.length > 0) {
            products.forEach(product => {
                const item = document.createElement("div");
                item.className = "product-item";
                item.innerHTML = `
                    <img src="${product.image}" alt="${product.name}">
                    <h2>${product.name}</h2>
                    <p>${product.description}</p>
                    <p class="price">${product.price} руб.</p>
                    <div class="actions">
                        <a href="product.php?id=${product.id}" class="btn">Посмотреть</a>
                        <button class="add-to-cart-btn" data-product-id="${product.id}">+</button>
                    </div>
                `;
                productList.appendChild(item);
            });
            attachAddToCartHandlers();
        } else {
            productList.innerHTML = "<p>Товары не найдены.</p>";
        }
    }

    function fetchProducts(url) {
        fetch(url)
            .then(res => res.json())
            .then(data => data.error ? alert(data.error) : updateProductList(data))
            .catch(err => {
                console.error(err);
                alert("Ошибка при получении товаров.");
            });
    }

    if (searchButton && searchInput) {
        searchButton.addEventListener("click", function () {
            const query = searchInput.value.trim();
            const size = document.getElementById("size").value;
            const gender = document.getElementById("gender").value;
            const purpose = document.getElementById("purpose").value;
            const price = document.getElementById("price").value;

            const url = `catalog.php?query=${encodeURIComponent(query)}&size=${size}&gender=${gender}&purpose=${purpose}&price=${price}`;
            fetchProducts(url);
        });

        searchInput.addEventListener("keypress", function (event) {
            if (event.key === "Enter") searchButton.click();
        });
    }

    if (applyFiltersButton) {
        applyFiltersButton.addEventListener("click", function () {
            const size = document.getElementById("size").value;
            const gender = document.getElementById("gender").value;
            const purpose = document.getElementById("purpose").value;
            const price = document.getElementById("price").value;

            const url = `catalog.php?size=${size}&gender=${gender}&purpose=${purpose}&price=${price}`;
            fetchProducts(url);
        });
    }

    function attachAddToCartHandlers() {
        document.querySelectorAll(".add-to-cart-btn").forEach(button => {
            button.addEventListener("click", function () {
                const id = this.dataset.productId;
                fetch(`add_to_cart.php?product_id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        alert(data.status === "success" ? "Товар добавлен в корзину!" : "Ошибка при добавлении.");
                        updateCartCount();
                    })
                    .catch(() => alert("Ошибка добавления в корзину."));
            });
        });
    }

    function updateCartCount() {
        fetch("get_cart_count.php")
            .then(res => res.json())
            .then(data => {
                const countEl = document.getElementById("cartCount");
                if (countEl) countEl.textContent = data.count;
            });
    }

    // --- Слайдер преимуществ ---
    const features = document.querySelectorAll(".feature-item");
    const dots = document.querySelectorAll(".feature-dot");
    const prev = document.querySelector(".feature-arrow.prev");
    const next = document.querySelector(".feature-arrow.next");

    if (features.length && dots.length) {
        let index = 0;

        function showFeature(i) {
            features.forEach((el, j) => {
                el.style.display = j === i ? "flex" : "none";
            });
            dots.forEach((dot, j) => {
                dot.classList.toggle("active", j === i);
            });
            index = i;
        }

        function changeFeature(n) {
            const newIndex = (index + n + features.length) % features.length;
            showFeature(newIndex);
        }

        prev?.addEventListener("click", () => changeFeature(-1));
        next?.addEventListener("click", () => changeFeature(1));

        dots.forEach((dot, i) => {
            dot.addEventListener("click", () => showFeature(i));
        });

        showFeature(index);
    }
});
