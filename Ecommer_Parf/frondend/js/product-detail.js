// ===== Product Detail Page Logic =====

let currentProduct = null;
let wishlist = {};

// ===== Initialize Page =====
document.addEventListener('DOMContentLoaded', () => {
    loadWishlistFromLocalStorage();
    loadProductFromURL();
});

// ===== Get Product ID from URL =====
function getProductIdFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get('id'));
}

// ===== Load Product from URL =====
function loadProductFromURL() {
    const productId = getProductIdFromURL();
    
    if (!productId || !products) {
        window.location.href = 'index.html';
        return;
    }
    
    currentProduct = products.find(p => p.id === productId);
    
    if (!currentProduct) {
        window.location.href = 'index.html';
        return;
    }
    
    displayProduct(currentProduct);
    loadRelatedProducts(currentProduct);
}

// ===== Display Product Details =====
function displayProduct(product) {
    // Breadcrumb
    document.getElementById('breadcrumb-product').textContent = product.name;
    
    // Badge
    const badgeContainer = document.getElementById('product-badge-container');
    if (product.tags.includes('offres')) {
        badgeContainer.innerHTML = '<span class="product-badge" style="position: absolute; top: 1rem; right: 1rem; z-index: 10;">Promo</span>';
    } else if (product.tags.includes('nouveautes')) {
        badgeContainer.innerHTML = '<span class="product-badge new" style="position: absolute; top: 1rem; right: 1rem; z-index: 10;">Nouveau</span>';
    }
    
    // Image
    document.getElementById('product-image').src = product.image;
    document.getElementById('product-image').alt = product.name;
    
    // Category
    const categoryLabel = getCategoryLabel(product.category);
    document.getElementById('product-category').textContent = categoryLabel;
    
    // Name
    document.getElementById('product-name').textContent = product.name;
    
    // Description
    document.getElementById('product-description').textContent = product.description;
    
    // Price
    document.getElementById('product-price').textContent = product.price;
    
    // Old Price
    const oldPriceEl = document.getElementById('product-old-price');
    if (product.oldPrice) {
        oldPriceEl.textContent = product.oldPrice + ' DH';
        oldPriceEl.classList.remove('d-none');
    }
    
    // Update page title
    document.title = `${product.name} - Quartier d'Arômes`;
}

// ===== Load Related Products =====
function loadRelatedProducts(product) {
    const relatedContainer = document.getElementById('related-products');
    
    // Get products from same category, excluding current product
    const related = products
        .filter(p => p.category === product.category && p.id !== product.id)
        .slice(0, 3);
    
    if (related.length === 0) {
        // If no products from same category, get random products
        related.push(...products.filter(p => p.id !== product.id).slice(0, 3));
    }
    
    let html = '';
    related.forEach(p => {
        let badge = '';
        if (p.tags.includes('offres')) {
            badge = '<span class="product-badge">Promo</span>';
        } else if (p.tags.includes('nouveautes')) {
            badge = '<span class="product-badge new">Nouveau</span>';
        }
        
        let priceHTML = `<div class="product-price">${p.price} DH</div>`;
        if (p.oldPrice) {
            priceHTML = `
                <div class="product-price">
                    ${p.price} DH
                    <span class="product-old-price">${p.oldPrice} DH</span>
                </div>
            `;
        }
        
        html += `
            <div class="col-lg-4 col-md-6">
                <div class="product-card">
                    ${badge}
                    <img src="${p.image}" alt="${p.name}" class="product-image">
                    <div class="product-body">
                        <div>
                            <div class="product-category">${getCategoryLabel(p.category)}</div>
                            <h3 class="product-name">${p.name}</h3>
                            <p class="product-description">${p.description}</p>
                            ${priceHTML}
                        </div>
                        <div class="d-grid gap-2">
                            <a href="product-detail.html?id=${p.id}" class="btn btn-view-details">
                                <i class="fas fa-eye me-2"></i>Voir détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    relatedContainer.innerHTML = html;
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

// ===== Change Quantity =====
function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    let currentQty = parseInt(quantityInput.value) || 1;
    let newQty = currentQty + delta;
    
    if (newQty < 1) newQty = 1;
    if (newQty > 10) newQty = 10;
    
    quantityInput.value = newQty;
}

// ===== Add to Cart from Page =====
function addToCartFromPage() {
    if (!currentProduct) return;
    
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    if (cart[currentProduct.id]) {
        cart[currentProduct.id] += quantity;
    } else {
        cart[currentProduct.id] = quantity;
    }
    
    saveCartToLocalStorage();
    updateCartUI();
    showNotification(`${currentProduct.name} ajouté au panier (×${quantity})`, 'success');
}

// ===== Add to Wishlist from Page =====
function addToWishlistFromPage() {
    if (!currentProduct) return;
    
    if (wishlist[currentProduct.id]) {
        delete wishlist[currentProduct.id];
        showNotification(`${currentProduct.name} retiré des favoris`, 'info');
    } else {
        wishlist[currentProduct.id] = true;
        showNotification(`${currentProduct.name} ajouté aux favoris`, 'success');
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

// ===== Get Product by ID =====
function getProductById(productId) {
    return products.find(p => p.id === parseInt(productId));
}
