// ===== Checkout Page Logic =====
// Note: La variable 'cart' est déjà déclarée dans cart.js

// Get cart from localStorage (refresh each time)
function getCart() {
    return JSON.parse(localStorage.getItem('cart')) || {};
}

// ===== Get Product by ID =====
function getProductById(id) {
    return products.find(p => p.id === parseInt(id));
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
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
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

// ===== Initialize Checkout Page =====
document.addEventListener('DOMContentLoaded', () => {
    console.log('🔄 Checkout page initializing...');
    
    // Vérifier que products est chargé
    if (typeof products === 'undefined' || !Array.isArray(products)) {
        console.error('❌ Products array not loaded!');
        return;
    }
    
    console.log('✅ Products loaded:', products.length, 'items');
    
    // Recharger le panier depuis localStorage
    cart = getCart();
    console.log('📦 Cart loaded from localStorage:', cart);
    console.log('📊 Cart items count:', Object.keys(cart).length);
    
    // Attendre un peu pour s'assurer que tout est bien chargé
    setTimeout(() => {
        updateOrderSummary();
        setupFormHandlers();
        loadFormFromLocalStorage();
    }, 100);
});

// ===== Update Order Summary =====
function updateOrderSummary() {
    console.log('\n🔄 === UPDATE ORDER SUMMARY START ===');
    
    // Vérifier que products est disponible
    if (typeof products === 'undefined') {
        console.error('❌ ERROR: products array is undefined!');
        return;
    }
    
    // Recharger le panier depuis localStorage
    cart = getCart();
    console.log('📦 Raw localStorage cart:', localStorage.getItem('cart'));
    
    const orderSummary = document.getElementById('order-summary');
    const orderDetails = document.getElementById('order-details');
    const orderItems = document.getElementById('order-items');
    const subtotalAmount = document.getElementById('subtotal-amount');
    const totalAmount = document.getElementById('total-amount');
    
    console.log('📊 Cart object:', cart);
    console.log('🔑 Cart keys:', Object.keys(cart));
    console.log('📈 Cart items count:', Object.keys(cart).length);
    console.log('🎯 Products array length:', products.length);
    
    // Check if cart is empty
    const cartKeys = Object.keys(cart);
    console.log('🔍 Checking cart... Keys:', cartKeys);
    
    if (!cart || cartKeys.length === 0) {
        console.log('❌ CART IS EMPTY - Showing empty message');
        console.log('   - cart exists:', !!cart);
        console.log('   - cart type:', typeof cart);
        console.log('   - cart value:', cart);
        if (orderSummary) orderSummary.classList.remove('d-none');
        if (orderDetails) orderDetails.classList.add('d-none');
        return;
    }
    
    console.log('✅ CART HAS ' + cartKeys.length + ' ITEMS - Showing order details');
    
    // Show order details
    orderSummary.classList.add('d-none');
    orderDetails.classList.remove('d-none');
    
    // Render order items
    let html = '';
    let total = 0;
    
    Object.keys(cart).forEach(productId => {
        console.log('\n🔍 Processing product ID:', productId, '(type:', typeof productId, ')');
        const product = getProductById(productId);
        console.log('   📦 Product found:', product ? product.name : 'NOT FOUND');
        console.log('   💾 Full product object:', product);
        
        if (!product) {
            console.error('   ❌ ERROR: Product not found for ID:', productId);
            console.log('   🔍 Searching in products array...');
            console.log('   📋 Available product IDs:', products.map(p => p.id + ' (' + typeof p.id + ')'));
            return;
        }
        
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        console.log(`   ✅ Success: ${product.name} x${quantity} = ${subtotal} DH`);
        
        html += `
            <div class="order-item mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${product.name}</strong><br>
                        <small class="text-muted">${quantity} × ${product.price} DH</small>
                    </div>
                    <div class="text-end">
                        <strong>${subtotal.toFixed(2)} DH</strong>
                    </div>
                </div>
            </div>
        `;
    });
    
    console.log('\n📝 Generated HTML length:', html.length, 'characters');
    console.log('💰 Total amount:', total, 'DH');
    
    if (orderItems) {
        orderItems.innerHTML = html;
        console.log('✅ HTML injected into order-items');
    } else {
        console.error('❌ order-items element not found!');
    }
    
    if (subtotalAmount) subtotalAmount.textContent = total.toFixed(2) + ' DH';
    if (totalAmount) totalAmount.textContent = total.toFixed(2);
    
    console.log('✅ === ORDER SUMMARY COMPLETED ===\n');
}

// ===== Setup Form Handlers =====
function setupFormHandlers() {
    const form = document.getElementById('order-form');
    if (!form) return;
    
    // Form submit handler
    form.addEventListener('submit', handleFormSubmit);
    
    // WhatsApp button handler
    const whatsappBtn = document.getElementById('send-whatsapp');
    if (whatsappBtn) {
        whatsappBtn.addEventListener('click', handleWhatsAppOrder);
    }
    
    // Auto-save form data
    const formInputs = form.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', saveFormToLocalStorage);
    });
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
    // Recharger le panier
    cart = getCart();
    
    // Check if cart is empty
    if (!cart || Object.keys(cart).length === 0) {
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
    let message = `🛍️ *Nouvelle Commande - Quartier d'Arômes*\n\n`;
    message += `👤 *Client:* ${formData.firstName} ${formData.lastName}\n`;
    message += `📧 *Email:* ${formData.email}\n`;
    message += `📱 *Téléphone:* +212${formData.phone}\n\n`;
    
    if (formData.address) {
        message += `📍 *Adresse:* ${formData.address}\n\n`;
    }
    
    message += `🛒 *Produits:*\n`;
    let total = 0;
    
    Object.keys(cart).forEach(productId => {
        const product = getProductById(productId);
        if (!product) return;
        
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        message += `• ${product.name} - ${quantity}x ${product.price} DH = ${subtotal} DH\n`;
    });
    
    message += `\n💰 *Total:* ${total.toFixed(2)} DH\n`;
    
    if (formData.notes) {
        message += `\n📝 *Notes:* ${formData.notes}`;
    }
    
    // Open WhatsApp
    const whatsappNumber = '212708505157'; // Numéro Quartier d'Arômes
    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// ===== Get Form Data =====
function getFormData() {
    return {
        firstName: document.getElementById('first-name').value,
        lastName: document.getElementById('last-name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        notes: document.getElementById('notes').value
    };
}

// ===== Get Order Data =====
function getOrderData() {
    const items = [];
    let total = 0;
    
    Object.keys(cart).forEach(productId => {
        const product = getProductById(productId);
        if (!product) return;
        
        const quantity = cart[productId];
        const subtotal = product.price * quantity;
        total += subtotal;
        
        items.push({
            productId: product.id,
            name: product.name,
            price: product.price,
            quantity: quantity,
            subtotal: subtotal
        });
    });
    
    return { items, total };
}

// ===== Send Order to Backend =====
function sendOrderToBackend(formData, orderData) {
    const submitBtn = document.querySelector('#order-form button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';
    
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
            updateOrderSummary();
            
            // Redirect to home after 2 seconds
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
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

// ===== Save Form to LocalStorage =====
function saveFormToLocalStorage() {
    const formData = getFormData();
    localStorage.setItem('formData', JSON.stringify(formData));
}

// ===== Load Form from LocalStorage =====
function loadFormFromLocalStorage() {
    const savedData = localStorage.getItem('formData');
    if (!savedData) return;
    
    const formData = JSON.parse(savedData);
    
    document.getElementById('first-name').value = formData.firstName || '';
    document.getElementById('last-name').value = formData.lastName || '';
    document.getElementById('email').value = formData.email || '';
    document.getElementById('phone').value = formData.phone || '';
    document.getElementById('address').value = formData.address || '';
    document.getElementById('notes').value = formData.notes || '';
}
