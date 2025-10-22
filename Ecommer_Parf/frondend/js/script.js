// ===== Note: Products are loaded from products-data.js =====
// The 'products' array is defined in products-data.js which is loaded before this file

// ===== Global Variables =====
// cart et wishlist sont déclarés dans cart.js
let currentCategory = 'all';

// ===== Initialize Application =====
document.addEventListener('DOMContentLoaded', () => {
    loadCartFromLocalStorage();
    loadWishlistFromLocalStorage();
    renderCarousel();
    renderProducts(currentCategory);
    setupEventListeners();
    updateCartUI();
    updateWishlistUI();
    setupSmoothScroll();
});

// ===== Setup Event Listeners =====
function setupEventListeners() {
    // Category filter buttons - Redirect to collection page
    const categoryBtns = document.querySelectorAll('.category-btn');
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.dataset.category;
            // Redirect to collection page with category parameter
            window.location.href = `collection.html?category=${category}`;
        });
    });
    
    // Form submission
    const form = document.getElementById('order-form');
    form.addEventListener('submit', handleFormSubmit);
    
    // WhatsApp button
    const whatsappBtn = document.getElementById('send-whatsapp');
    whatsappBtn.addEventListener('click', handleWhatsAppOrder);
    
    // Clear cart button
    const clearCartBtn = document.getElementById('clear-cart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (confirm('Voulez-vous vraiment vider le panier?')) {
                cart = {};
                saveCartToLocalStorage();
                updateCartUI();
                showNotification('Panier vidé', 'success');
            }
        });
    }
    
    // Checkout button - Open Modal
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            // Close offcanvas
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('cartOffcanvas'));
            if (offcanvas) {
                offcanvas.hide();
            }
            
            // Update modal summary
            updateModalSummary();
            
            // Show modal
            const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
            orderModal.show();
        });
    }
    
    // Auto-save form data
    const formInputs = form.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', saveFormToLocalStorage);
    });
    
    // Load form data
    loadFormFromLocalStorage();
}

// ===== Render Carousel =====
function renderCarousel() {
    const carouselTrack = document.getElementById('carousel-track');
    if (!carouselTrack) return;
    
    // Sélectionner les produits vedettes (nouveautés et offres)
    const featuredProducts = products.filter(p => 
        p.tags.includes('nouveautes') || p.tags.includes('offres')
    );
    
    // Dupliquer les produits pour un défilement infini
    const allProducts = [...featuredProducts, ...featuredProducts];
    
    // Créer les cartes produits
    allProducts.forEach(product => {
        const card = document.createElement('div');
        card.className = 'carousel-product-card';
        
        let badge = '';
        if (product.tags.includes('offres')) {
            badge = '<span class="product-badge">Promo</span>';
        } else if (product.tags.includes('nouveautes')) {
            badge = '<span class="product-badge new">Nouveau</span>';
        }
        
        let priceHTML = `<div class="product-price">${product.price} DH</div>`;
        if (product.oldPrice) {
            priceHTML = `
                <div class="product-price">
                    ${product.price} DH
                    <span class="product-old-price">${product.oldPrice} DH</span>
                </div>
            `;
        }
        
        card.innerHTML = `
            <div class="product-card">
                ${badge}
                <img src="${product.image}" alt="${product.name}" class="product-image">
                <div class="product-body">
                    <div class="product-category">${getCategoryLabel(product.category)}</div>
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    ${priceHTML}
                    <div class="d-grid gap-2">
                        <button class="btn btn-add-cart" data-product-id="${product.id}">
                            <i class="fas fa-cart-plus me-2"></i>Ajouter au Panier
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Ajouter événement au bouton
        const addBtn = card.querySelector('.btn-add-cart');
        addBtn.addEventListener('click', () => addToCart(product.id));
        
        carouselTrack.appendChild(card);
    });
}

// ===== Render Products =====
function renderProducts(category) {
    const container = document.getElementById('products-container');
    const noProducts = document.getElementById('no-products');
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
                    <button class="btn btn-view-details" data-product-id="${product.id}">
                        <i class="fas fa-eye me-2"></i>Voir détails
                    </button>
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
    const viewBtn = col.querySelector('.btn-view-details');
    viewBtn.addEventListener('click', () => {
        window.location.href = `product-detail.html?id=${product.id}`;
    });
    
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

// ===== Show Product Details =====
function showProductDetails(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    // Fill modal with product details
    document.getElementById('detail-product-image').src = product.image;
    document.getElementById('detail-product-name').textContent = product.name;
    document.getElementById('detail-product-category').textContent = getCategoryLabel(product.category);
    document.getElementById('detail-product-description').textContent = product.description;
    document.getElementById('detail-product-price').textContent = product.price;
    
    // Show/hide old price
    const oldPriceEl = document.getElementById('detail-product-old-price');
    if (product.oldPrice) {
        oldPriceEl.textContent = product.oldPrice + ' DH';
        oldPriceEl.classList.remove('d-none');
    } else {
        oldPriceEl.classList.add('d-none');
    }
    
    // Set quantity to 1
    document.getElementById('detail-quantity').value = 1;
    
    // Store current product id
    document.getElementById('productDetailModal').dataset.productId = productId;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
    modal.show();
}

// ===== Change Detail Quantity =====
function changeDetailQuantity(delta) {
    const quantityInput = document.getElementById('detail-quantity');
    let currentQty = parseInt(quantityInput.value) || 1;
    let newQty = currentQty + delta;
    
    // Limiter entre 1 et 10
    if (newQty < 1) newQty = 1;
    if (newQty > 10) newQty = 10;
    
    quantityInput.value = newQty;
}

// ===== Add to Cart from Detail Modal =====
function addToCartFromDetail() {
    const productId = parseInt(document.getElementById('productDetailModal').dataset.productId);
    const quantity = parseInt(document.getElementById('detail-quantity').value) || 1;
    const product = products.find(p => p.id === productId);
    
    if (!product) return;
    
    if (cart[productId]) {
        cart[productId] += quantity;
    } else {
        cart[productId] = quantity;
    }
    
    saveCartToLocalStorage();
    updateCartUI();
    showNotification(`${product.name} ajouté au panier (×${quantity})`, 'success');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('productDetailModal'));
    if (modal) {
        modal.hide();
    }
}

// ===== Add to Wishlist from Detail Modal =====
function addToWishlistFromDetail() {
    const productId = parseInt(document.getElementById('productDetailModal').dataset.productId);
    const product = products.find(p => p.id === productId);
    
    if (!product) return;
    
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

// ===== Update Cart UI =====
function updateCartUI() {
    updateCartCount();
    updateCartOffcanvas();
}

// ===== Update Cart Count =====
function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    const totalItems = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
    cartCount.textContent = totalItems;
    
    if (totalItems > 0) {
        cartCount.classList.add('animate-pulse');
        setTimeout(() => cartCount.classList.remove('animate-pulse'), 300);
    }
}

// ===== Update Modal Summary =====
function updateModalSummary() {
    const orderSummary = document.getElementById('order-summary-modal');
    const orderDetails = document.getElementById('order-details-modal');
    const orderItems = document.getElementById('order-items-modal');
    const subtotalAmount = document.getElementById('subtotal-amount-modal');
    const totalAmount = document.getElementById('total-amount-modal');
    
    // Check if cart is empty
    if (Object.keys(cart).length === 0) {
        orderSummary.classList.remove('d-none');
        orderDetails.classList.add('d-none');
        return;
    }
    
    // Show order details
    orderSummary.classList.add('d-none');
    orderDetails.classList.remove('d-none');
    
    // Clear previous items
    orderItems.innerHTML = '';
    
    let total = 0;
    
    // Add each item
    Object.keys(cart).forEach(productId => {
        const product = products.find(p => p.id == productId);
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        const itemDiv = document.createElement('div');
        itemDiv.className = 'order-item';
        itemDiv.innerHTML = `
            <button class="order-item-remove" data-product-id="${productId}">
                <i class="fas fa-times"></i>
            </button>
            <div class="order-item-info">
                <div class="order-item-name">${product.name}</div>
                <div class="order-item-quantity">${quantity} × ${product.price} DH</div>
            </div>
            <div class="order-item-price">${subtotal} DH</div>
        `;
        
        // Add remove button listener
        const removeBtn = itemDiv.querySelector('.order-item-remove');
        removeBtn.addEventListener('click', () => removeFromCart(productId));
        
        orderItems.appendChild(itemDiv);
    });
    
    // Update totals
    subtotalAmount.textContent = total.toFixed(2) + ' DH';
    totalAmount.textContent = total.toFixed(2);
}

// ===== Update Cart Offcanvas =====
function updateCartOffcanvas() {
    const cartItemsOffcanvas = document.getElementById('cart-items-offcanvas');
    const cartFooter = document.getElementById('cart-footer');
    const cartTotal = document.getElementById('cart-total');
    
    // Check if cart is empty
    if (Object.keys(cart).length === 0) {
        cartItemsOffcanvas.innerHTML = `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">Votre panier est vide</p>
            </div>
        `;
        cartFooter.classList.add('d-none');
        return;
    }
    
    // Show cart items
    cartFooter.classList.remove('d-none');
    cartItemsOffcanvas.innerHTML = '';
    
    let total = 0;
    
    Object.keys(cart).forEach(productId => {
        const product = products.find(p => p.id == productId);
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        const itemDiv = document.createElement('div');
        itemDiv.className = 'cart-item mb-2';
        itemDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${product.name}</strong>
                    <div class="text-muted small">${quantity} × ${product.price} DH</div>
                </div>
                <div class="text-primary fw-bold">${subtotal} DH</div>
            </div>
        `;
        cartItemsOffcanvas.appendChild(itemDiv);
    });
    
    cartTotal.textContent = total.toFixed(2);
}

// ===== Remove from Cart =====
function removeFromCart(productId) {
    delete cart[productId];
    saveCartToLocalStorage();
    updateCartUI();
    showNotification('Produit retiré du panier', 'success');
}

// ===== Handle Form Submit =====
function handleFormSubmit(event) {
    event.preventDefault();
    
    // Check if cart is empty
    if (Object.keys(cart).length === 0) {
        showNotification('Veuillez d\'abord ajouter des produits au panier', 'danger');
        return;
    }
    
    // Validate form
    const form = event.target;
    if (!form.checkValidity()) {
        event.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    // Get form data
    const formData = getFormData();
    
    // Get order data
    const orderData = getOrderData();
    
    // Send to backend
    sendOrderToBackend(formData, orderData);
}

// ===== Handle WhatsApp Order =====
function handleWhatsAppOrder() {
    // Check if cart is empty
    if (Object.keys(cart).length === 0) {
        showNotification('Veuillez d\'abord ajouter des produits au panier', 'danger');
        return;
    }
    
    // Validate form
    const form = document.getElementById('order-form');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        showNotification('Veuillez remplir tous les champs obligatoires', 'danger');
        return;
    }
    
    // Get form data
    const formData = getFormData();
    
    // Generate WhatsApp message
    const message = generateWhatsAppMessage(formData);
    
    // WhatsApp number - Quartier d'Arômes
    const whatsappNumber = '212708505157';
    
    // Create WhatsApp URL
    const url = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
    
    // Open WhatsApp
    window.open(url, '_blank');
    
    showNotification('Redirection vers WhatsApp...', 'success');
}

// ===== Generate WhatsApp Message =====
function generateWhatsAppMessage(formData) {
    let message = `🛍️ *NOUVELLE COMMANDE - LUXE PARFUMS*\n\n`;
    message += `👤 *CLIENT*\n`;
    message += `Nom: ${formData.firstName} ${formData.lastName}\n`;
    message += `Email: ${formData.email}\n`;
    message += `Téléphone: +212${formData.phone}\n`;
    
    if (formData.address) {
        message += `Adresse: ${formData.address}\n`;
    }
    
    if (formData.notes) {
        message += `Notes: ${formData.notes}\n`;
    }
    
    message += `\n📦 *PRODUITS*\n\n`;
    
    let total = 0;
    Object.keys(cart).forEach(productId => {
        const product = products.find(p => p.id == productId);
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        message += `▪️ ${product.name}\n`;
        message += `   ${quantity} × ${product.price} DH = ${subtotal} DH\n\n`;
    });
    
    message += `━━━━━━━━━━━━━━━━━\n`;
    message += `💰 *TOTAL: ${total.toFixed(2)} DH*\n`;
    message += `🚚 *Livraison: GRATUITE*\n`;
    message += `━━━━━━━━━━━━━━━━━\n\n`;
    message += `Merci de confirmer cette commande.`;
    
    return message;
}

// ===== Get Form Data =====
function getFormData() {
    return {
        firstName: document.getElementById('first-name').value.trim(),
        lastName: document.getElementById('last-name').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        address: document.getElementById('address').value.trim(),
        notes: document.getElementById('notes').value.trim()
    };
}

// ===== Get Order Data =====
function getOrderData() {
    const items = [];
    let total = 0;
    
    Object.keys(cart).forEach(productId => {
        const product = products.find(p => p.id == productId);
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        items.push({
            productId: product.id,
            productName: product.name,
            quantity: quantity,
            price: product.price,
            subtotal: subtotal
        });
    });
    
    return { items, total };
}

// ===== Send Order to Backend =====
function sendOrderToBackend(formData, orderData) {
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
    
    const data = {
        customer: formData,
        order: orderData
    };
    
    fetch('../backend/process_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Commande envoyée avec succès!', 'success');
            
            // Clear cart and form
            cart = {};
            saveCartToLocalStorage();
            document.getElementById('order-form').reset();
            document.getElementById('order-form').classList.remove('was-validated');
            localStorage.removeItem('formData');
            
            updateCartUI();
            
            // Close modal
            const orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
            if (orderModal) {
                orderModal.hide();
            }
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            showNotification('Erreur: ' + result.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur de connexion. Veuillez réessayer.', 'danger');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Envoyer la commande';
    });
}

// ===== Show Notification =====
function showNotification(message, type = 'success') {
    const toastEl = document.getElementById('notification-toast');
    const toastBody = toastEl.querySelector('.toast-body');
    const toastIcon = toastEl.querySelector('.toast-header i');
    
    toastBody.textContent = message;
    
    toastIcon.className = type === 'success' 
        ? 'fas fa-check-circle text-success me-2' 
        : 'fas fa-exclamation-circle text-danger me-2';
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// ===== Wishlist Functions =====
function loadWishlistFromLocalStorage() {
    const saved = localStorage.getItem('wishlist');
    if (saved) {
        wishlist = JSON.parse(saved);
    }
}

function saveWishlistToLocalStorage() {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

function updateWishlistUI() {
    const wishlistCount = document.getElementById('wishlist-count');
    if (wishlistCount) {
        const totalItems = Object.keys(wishlist).length;
        wishlistCount.textContent = totalItems;
    }
}

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

// ===== LocalStorage Functions =====
// saveCartToLocalStorage et loadCartFromLocalStorage sont dans cart.js

function saveFormToLocalStorage() {
    const formData = getFormData();
    localStorage.setItem('formData', JSON.stringify(formData));
}

function loadFormFromLocalStorage() {
    const savedFormData = localStorage.getItem('formData');
    if (savedFormData) {
        const formData = JSON.parse(savedFormData);
        document.getElementById('first-name').value = formData.firstName || '';
        document.getElementById('last-name').value = formData.lastName || '';
        document.getElementById('email').value = formData.email || '';
        document.getElementById('phone').value = formData.phone || '';
        document.getElementById('address').value = formData.address || '';
        document.getElementById('notes').value = formData.notes || '';
    }
}

// ===== Smooth Scroll =====
function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
}

// ===== Keyboard Shortcuts =====
document.addEventListener('keydown', (e) => {
    // Ctrl+S to save form
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveFormToLocalStorage();
        showNotification('Formulaire sauvegardé', 'success');
    }
});

// ===== Auto-save form every 30 seconds =====
setInterval(() => {
    if (document.getElementById('first-name').value) {
        saveFormToLocalStorage();
    }
}, 30000);
