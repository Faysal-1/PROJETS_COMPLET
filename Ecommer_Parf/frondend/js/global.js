// ===== SCRIPT GLOBAL - À charger sur TOUTES les pages =====

// Charger et mettre à jour les compteurs au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updateGlobalCounters();
});

// ===== Mise à jour des compteurs panier et wishlist =====
function updateGlobalCounters() {
    // Mettre à jour le compteur de panier
    updateGlobalCartCount();
    
    // Mettre à jour le compteur de favoris
    updateGlobalWishlistCount();
}

// ===== Compteur Panier =====
function updateGlobalCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        const cart = JSON.parse(localStorage.getItem('cart')) || {};
        const totalItems = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
        cartCount.textContent = totalItems;
    }
}

// ===== Compteur Wishlist =====
function updateGlobalWishlistCount() {
    const wishlistCount = document.getElementById('wishlist-count');
    if (wishlistCount) {
        const wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};
        const totalItems = Object.keys(wishlist).length;
        wishlistCount.textContent = totalItems;
    }
}

// ===== Écouter les changements dans localStorage (entre onglets) =====
window.addEventListener('storage', function(e) {
    if (e.key === 'cart' || e.key === 'wishlist') {
        updateGlobalCounters();
    }
});

// ===== Fonction publique pour rafraîchir depuis d'autres scripts =====
window.refreshCounters = function() {
    updateGlobalCounters();
};
