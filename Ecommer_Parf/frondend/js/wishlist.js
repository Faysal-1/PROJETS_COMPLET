// ===== Wishlist Management =====

// Global wishlist object
let wishlist = {};

// ===== Get Product by ID =====
function getProductById(id) {
    return products.find(p => p.id == id);
}

// ===== Get Category Label =====
function getCategoryLabel(category) {
    const labels = {
        'homme': 'HOMME',
        'femme': 'FEMME',
        'mixte': 'MIXTE'
    };
    return labels[category] || category.toUpperCase();
}

// ===== Load Wishlist from LocalStorage =====
function loadWishlistFromLocalStorage() {
    const savedWishlist = localStorage.getItem('wishlist');
    if (savedWishlist) {
        wishlist = JSON.parse(savedWishlist);
    }
}

// ===== Save Wishlist to LocalStorage =====
function saveWishlistToLocalStorage() {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// ===== Add to Wishlist =====
function addToWishlist(productId) {
    if (wishlist[productId]) {
        showNotification('Produit déjà dans les favoris', 'info');
        return;
    }
    
    wishlist[productId] = true;
    saveWishlistToLocalStorage();
    renderWishlist();
    
    const product = getProductById(productId);
    if (product) {
        showNotification(`${product.name} ajouté aux favoris`, 'success');
    }
}

// ===== Remove from Wishlist =====
function removeFromWishlist(productId) {
    delete wishlist[productId];
    saveWishlistToLocalStorage();
    renderWishlist();
    showNotification('Produit retiré des favoris', 'success');
}

// ===== Render Wishlist Page =====
function renderWishlist() {
    const wishlistContent = document.getElementById('wishlist-content');
    const wishlistGrid = document.getElementById('wishlist-grid');
    const wishlistActions = document.getElementById('wishlist-actions');
    
    const wishlistItems = Object.keys(wishlist);
    
    if (wishlistItems.length === 0) {
        wishlistContent.classList.remove('d-none');
        wishlistGrid.classList.add('d-none');
        wishlistActions.classList.add('d-none');
        return;
    }
    
    // Hide empty message
    wishlistContent.classList.add('d-none');
    wishlistGrid.classList.remove('d-none');
    wishlistActions.classList.remove('d-none');
    
    // Render wishlist items
    let html = '';
    wishlistItems.forEach(productId => {
        const product = getProductById(productId);
        if (!product) return;
        
        let priceHTML = `<div class="product-price">${product.price} DH</div>`;
        if (product.oldPrice) {
            priceHTML = `
                <div class="product-price">
                    ${product.price} DH
                    <span class="product-old-price">${product.oldPrice} DH</span>
                </div>
            `;
        }
        
        html += `
            <div class="col-lg-4 col-md-6">
                <div class="product-card">
                    ${product.tags.includes('offres') ? '<span class="product-badge">Promo</span>' : ''}
                    ${product.tags.includes('nouveautes') ? '<span class="product-badge new">Nouveau</span>' : ''}
                    <img src="${product.image}" alt="${product.name}" class="product-image">
                    <div class="product-body">
                        <div class="product-category">${getCategoryLabel(product.category)}</div>
                        <h3 class="product-name">${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        ${priceHTML}
                        <div class="d-grid gap-2">
                            <button class="btn btn-add-cart" onclick="addToCart(${product.id})">
                                <i class="fas fa-cart-plus me-2"></i>Ajouter au Panier
                            </button>
                            <button class="btn" onclick="removeFromWishlist(${product.id})" style="background: white; border: 2px solid var(--dark-color); color: var(--dark-color); padding: 0.8rem; border-radius: 12px; font-weight: 600; transition: var(--transition);">
                                <i class="fas fa-trash me-2"></i>Retirer des Favoris
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    wishlistGrid.innerHTML = html;
}

// ===== Add All to Cart =====
function addAllToCart() {
    const wishlistItems = Object.keys(wishlist);
    
    if (wishlistItems.length === 0) {
        showNotification('Aucun produit dans les favoris', 'info');
        return;
    }
    
    wishlistItems.forEach(productId => {
        addToCart(parseInt(productId), 1);
    });
    
    showNotification(`${wishlistItems.length} produits ajoutés au panier`, 'success');
}

// ===== Clear Wishlist =====
function clearWishlist() {
    if (confirm('Voulez-vous vraiment vider les favoris?')) {
        wishlist = {};
        saveWishlistToLocalStorage();
        renderWishlist();
        showNotification('Favoris vidés', 'success');
    }
}

// ===== Add to Cart Function =====
function addToCart(productId, quantity = 1) {
    let cart = JSON.parse(localStorage.getItem('cart')) || {};
    
    if (cart[productId]) {
        cart[productId] += quantity;
    } else {
        cart[productId] = quantity;
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateWishlistCount();
    
    const product = getProductById(productId);
    if (product) {
        showNotification(`${product.name} ajouté au panier`, 'success');
    }
}

// ===== Show Notification =====
function showNotification(message, type = 'info') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '9999';
    
    const bgColor = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';
    
    toastContainer.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header ${bgColor} text-white">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    
    document.body.appendChild(toastContainer);
    
    setTimeout(() => {
        toastContainer.remove();
    }, 3000);
}

// ===== Update Wishlist Count =====
function updateWishlistCount() {
    const wishlistCount = document.getElementById('wishlist-count');
    const count = Object.keys(wishlist).length;
    if (wishlistCount) {
        wishlistCount.textContent = count;
    }
}

// ===== Initialize Wishlist Page =====
document.addEventListener('DOMContentLoaded', () => {
    loadWishlistFromLocalStorage();
    renderWishlist();
    updateWishlistCount();
    
    // Setup event listeners
    const addAllBtn = document.getElementById('add-all-to-cart-btn');
    if (addAllBtn) {
        addAllBtn.addEventListener('click', addAllToCart);
    }
    
    const clearBtn = document.getElementById('clear-wishlist-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearWishlist);
    }
});
