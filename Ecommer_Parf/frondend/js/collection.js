// ===== Collection Page Logic =====

let currentCategory = 'all';
let wishlist = {};

// ===== Category Information =====
const categoryInfo = {
    all: {
        title: 'Nos Collections',
        subtitle: 'Découvrez tous nos parfums de luxe',
        icon: 'fas fa-th-large'
    },
    homme: {
        title: 'Parfums pour Homme',
        subtitle: 'Élégance et sophistication masculine',
        icon: 'fas fa-user-tie'
    },
    femme: {
        title: 'Parfums pour Femme',
        subtitle: 'Fragrances féminines raffinées',
        icon: 'fas fa-user'
    },
    mixte: {
        title: 'Parfums Mixtes',
        subtitle: 'Collections unisexes pour tous',
        icon: 'fas fa-users'
    },
    nouveautes: {
        title: 'Nouveautés',
        subtitle: 'Nos dernières créations parfumées',
        icon: 'fas fa-star'
    },
    offres: {
        title: 'Offres Spéciales',
        subtitle: 'Profitez de nos meilleures promotions',
        icon: 'fas fa-tag'
    }
};

// ===== Initialize Page =====
document.addEventListener('DOMContentLoaded', () => {
    loadWishlistFromLocalStorage();
    loadCategoryFromURL();
    setupCategoryButtons();
});

// ===== Get Category from URL =====
function getCategoryFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('category') || 'all';
}

// ===== Load Category from URL =====
function loadCategoryFromURL() {
    currentCategory = getCategoryFromURL();
    updatePageTitle(currentCategory);
    displayProducts(currentCategory);
    setActiveCategoryButton(currentCategory);
}

// ===== Update Page Title =====
function updatePageTitle(category) {
    const info = categoryInfo[category] || categoryInfo.all;
    
    document.getElementById('collection-title').textContent = info.title;
    document.getElementById('collection-subtitle').textContent = info.subtitle;
    document.getElementById('collection-icon').className = info.icon + ' me-3';
    document.title = `${info.title} - Quartier d'Arômes`;
}

// ===== Setup Category Buttons =====
function setupCategoryButtons() {
    const categoryBtns = document.querySelectorAll('.category-btn');
    
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.dataset.category;
            
            // Update URL
            const newUrl = `collection.html?category=${category}`;
            window.history.pushState({category}, '', newUrl);
            
            // Update page
            currentCategory = category;
            updatePageTitle(category);
            displayProducts(category);
            setActiveCategoryButton(category);
        });
    });
}

// ===== Set Active Category Button =====
function setActiveCategoryButton(category) {
    const categoryBtns = document.querySelectorAll('.category-btn');
    
    categoryBtns.forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// ===== Display Products =====
function displayProducts(category) {
    const container = document.getElementById('products-container');
    const noProducts = document.getElementById('no-products');
    const productCount = document.getElementById('product-count');
    
    container.innerHTML = '';
    
    // Filter products
    let filteredProducts = products;
    
    if (category !== 'all') {
        if (category === 'nouveautes' || category === 'offres') {
            filteredProducts = products.filter(p => p.tags.includes(category));
        } else {
            filteredProducts = products.filter(p => p.category === category);
        }
    }
    
    // Update product count
    productCount.textContent = filteredProducts.length;
    
    // Check if no products
    if (filteredProducts.length === 0) {
        noProducts.classList.remove('d-none');
        return;
    }
    
    noProducts.classList.add('d-none');
    
    // Render each product
    filteredProducts.forEach(product => {
        const productCard = createProductCard(product);
        container.appendChild(productCard);
    });
}

// ===== Create Product Card =====
function createProductCard(product) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6';
    
    // Determine badge
    let badge = '';
    if (product.tags.includes('offres')) {
        badge = '<span class="product-badge">Promo</span>';
    } else if (product.tags.includes('nouveautes')) {
        badge = '<span class="product-badge new">Nouveau</span>';
    }
    
    // Price display
    let priceHTML = `<div class="product-price">${product.price} DH</div>`;
    if (product.oldPrice) {
        priceHTML = `
            <div class="product-price">
                ${product.price} DH
                <span class="product-old-price">${product.oldPrice} DH</span>
            </div>
        `;
    }
    
    col.innerHTML = `
        <div class="product-card">
            ${badge}
            <img src="${product.image}" alt="${product.name}" class="product-image">
            <div class="product-body">
                <div>
                    <div class="product-category">${getCategoryLabel(product.category)}</div>
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    ${priceHTML}
                </div>
                <div class="d-grid gap-2">
                    <a href="product-detail.html?id=${product.id}" class="btn btn-view-details">
                        <i class="fas fa-eye me-2"></i>Voir détails
                    </a>
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-add-cart" data-product-id="${product.id}">
                            <i class="fas fa-cart-plus me-2"></i>Panier
                        </button>
                        <button class="btn btn-add-wishlist" data-product-id="${product.id}">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add event listeners
    const addBtn = col.querySelector('.btn-add-cart');
    addBtn.addEventListener('click', () => addToCart(product.id));
    
    const wishlistBtn = col.querySelector('.btn-add-wishlist');
    wishlistBtn.addEventListener('click', () => addToWishlist(product.id));
    
    return col;
}

// ===== Get Category Label =====
function getCategoryLabel(category) {
    const labels = {
        'homme': 'Homme',
        'femme': 'Femme',
        'mixte': 'Mixte'
    };
    return labels[category] || category;
}

// ===== Add to Cart =====
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    
    if (cart[productId]) {
        cart[productId]++;
    } else {
        cart[productId] = 1;
    }
    
    saveCartToLocalStorage();
    updateCartUI();
    showNotification(`${product.name} ajouté au panier`, 'success');
}

// ===== Add to Wishlist =====
function addToWishlist(productId) {
    const product = products.find(p => p.id === productId);
    
    if (wishlist[productId]) {
        delete wishlist[productId];
        showNotification(`${product.name} retiré des favoris`, 'info');
    } else {
        wishlist[productId] = true;
        showNotification(`${product.name} ajouté aux favoris`, 'success');
    }
    
    saveWishlistToLocalStorage();
    updateWishlistUI();
}

// ===== Load Wishlist from LocalStorage =====
function loadWishlistFromLocalStorage() {
    const saved = localStorage.getItem('wishlist');
    if (saved) {
        wishlist = JSON.parse(saved);
    }
}

// ===== Save Wishlist to LocalStorage =====
function saveWishlistToLocalStorage() {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// ===== Update Wishlist UI =====
function updateWishlistUI() {
    const wishlistCount = document.getElementById('wishlist-count');
    if (wishlistCount) {
        const count = Object.keys(wishlist).length;
        wishlistCount.textContent = count;
    }
}

// ===== Handle Browser Back/Forward =====
window.addEventListener('popstate', (event) => {
    if (event.state && event.state.category) {
        currentCategory = event.state.category;
        updatePageTitle(currentCategory);
        displayProducts(currentCategory);
        setActiveCategoryButton(currentCategory);
    }
});
