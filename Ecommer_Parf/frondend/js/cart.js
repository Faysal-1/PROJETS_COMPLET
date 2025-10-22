// ===== Cart Management (Shared across all pages) =====

// Global cart object
let cart = {};

// ===== Load Cart from LocalStorage =====
function loadCartFromLocalStorage() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
}

// ===== Save Cart to LocalStorage =====
function saveCartToLocalStorage() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// ===== Add to Cart =====
function addToCart(productId, quantity = 1) {
    if (cart[productId]) {
        cart[productId] += quantity;
    } else {
        cart[productId] = quantity;
    }
    
    saveCartToLocalStorage();
    updateCartUI();
    
    const product = getProductById(productId);
    if (product) {
        showNotification(`${product.name} ajouté au panier`, 'success');
    }
}

// ===== Remove from Cart =====
function removeFromCart(productId) {
    delete cart[productId];
    saveCartToLocalStorage();
    updateCartUI();
    showNotification('Produit retiré du panier', 'success');
}

// ===== Update Cart Quantity =====
function updateCartQuantity(productId, quantity) {
    if (quantity <= 0) {
        removeFromCart(productId);
    } else {
        cart[productId] = parseInt(quantity);
        saveCartToLocalStorage();
        updateCartUI();
    }
}

// ===== Get Cart Total =====
function getCartTotal() {
    let total = 0;
    Object.keys(cart).forEach(productId => {
        const product = getProductById(productId);
        if (product) {
            total += product.price * cart[productId];
        }
    });
    return total;
}

// ===== Get Cart Count =====
function getCartCount() {
    return Object.values(cart).reduce((sum, qty) => sum + qty, 0);
}

// ===== Update Cart UI =====
function updateCartUI() {
    updateCartCount();
    updateCartOffcanvas();
}

// ===== Update Cart Count Badge =====
function updateCartCount() {
    const cartCountEl = document.getElementById('cart-count');
    if (cartCountEl) {
        const totalItems = getCartCount();
        cartCountEl.textContent = totalItems;
        
        if (totalItems > 0) {
            cartCountEl.classList.add('animate-pulse');
            setTimeout(() => cartCountEl.classList.remove('animate-pulse'), 300);
        }
    }
}

// ===== Update Cart Offcanvas =====
function updateCartOffcanvas() {
    const cartItemsEl = document.getElementById('cart-items-offcanvas');
    const cartFooterEl = document.getElementById('cart-footer');
    const cartTotalEl = document.getElementById('cart-total');
    
    if (!cartItemsEl) return;
    
    // Check if cart is empty
    if (Object.keys(cart).length === 0) {
        cartItemsEl.innerHTML = `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">Votre panier est vide</p>
            </div>
        `;
        if (cartFooterEl) cartFooterEl.classList.add('d-none');
        return;
    }
    
    // Display cart items
    let html = '';
    Object.keys(cart).forEach(productId => {
        const product = getProductById(productId);
        if (!product) return;
        
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        
        html += `
            <div class="cart-item mb-3 p-3 border rounded">
                <div class="row align-items-center">
                    <div class="col-3">
                        <img src="${product.image}" alt="${product.name}" class="img-fluid rounded">
                    </div>
                    <div class="col-9">
                        <h6 class="mb-1">${product.name}</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="input-group input-group-sm" style="width: 100px;">
                                <button class="btn btn-outline-secondary" onclick="updateCartQuantity(${productId}, ${quantity - 1})">-</button>
                                <input type="number" class="form-control text-center" value="${quantity}" readonly>
                                <button class="btn btn-outline-secondary" onclick="updateCartQuantity(${productId}, ${quantity + 1})">+</button>
                            </div>
                            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${productId})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <small class="text-muted">${subtotal.toFixed(2)} DH</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    cartItemsEl.innerHTML = html;
    
    // Show footer with total
    if (cartFooterEl) {
        cartFooterEl.classList.remove('d-none');
    }
    if (cartTotalEl) {
        cartTotalEl.textContent = getCartTotal().toFixed(2);
    }
}

// ===== Clear Cart =====
function clearCart() {
    if (confirm('Voulez-vous vraiment vider le panier?')) {
        cart = {};
        saveCartToLocalStorage();
        updateCartUI();
        showNotification('Panier vidé', 'success');
    }
}

// ===== Show Notification =====
function showNotification(message, type = 'success') {
    const toastEl = document.getElementById('notification-toast');
    if (!toastEl) return;
    
    const toastBody = toastEl.querySelector('.toast-body');
    const toastIcon = toastEl.querySelector('.toast-header i');
    
    toastBody.textContent = message;
    
    toastIcon.className = type === 'success' 
        ? 'fas fa-check-circle text-success me-2' 
        : 'fas fa-exclamation-circle text-danger me-2';
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadCartFromLocalStorage();
    updateCartUI();
});
