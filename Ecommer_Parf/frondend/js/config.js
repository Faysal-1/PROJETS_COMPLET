// ===== Configuration Globale du Site =====
// Fichier de configuration pour Quartier d'Arômes

const CONFIG = {
    // Informations du site
    siteName: "Quartier d'Arômes",
    siteDescription: "Votre destination de parfums de luxe au Maroc",
    
    // Contact
    whatsappNumber: "212708505157", // Numéro Quartier d'Arômes (+212 708-505157)
    email: "contact@quartiersdaromes.ma",
    instagramUrl: "https://www.instagram.com/quartier_daromes/",
    facebookUrl: "https://www.facebook.com/quartiersdaromes",
    
    // Localisation
    city: "Fès",
    country: "Maroc",
    
    // Backend API
    apiUrl: "../backend/process_order.php", // URL relative pour développement local
    // apiUrl: "https://votre-domaine.com/api/process_order.php", // Pour production
    
    // Options du site
    currency: "DH",
    freeShipping: true,
    freeShippingThreshold: 0, // Livraison gratuite dès 0 DH
    
    // Messages
    messages: {
        addedToCart: "ajouté au panier",
        addedToWishlist: "ajouté aux favoris",
        removedFromCart: "retiré du panier",
        removedFromWishlist: "retiré des favoris",
        emptyCart: "Votre panier est vide",
        emptyWishlist: "Aucun favori pour le moment",
        orderSuccess: "Commande envoyée avec succès!",
        orderError: "Erreur lors de l'envoi de la commande",
        fillRequired: "Veuillez remplir tous les champs obligatoires"
    },
    
    // Réseaux sociaux
    social: {
        instagram: {
            name: "Instagram",
            icon: "fab fa-instagram",
            url: "https://www.instagram.com/quartier_daromes/",
            handle: "@quartier_daromes"
        },
        facebook: {
            name: "Facebook",
            icon: "fab fa-facebook",
            url: "https://www.facebook.com/quartiersdaromes",
            handle: "Quartier d'Arômes"
        },
        whatsapp: {
            name: "WhatsApp",
            icon: "fab fa-whatsapp",
            number: "212708505157"
        }
    }
};

// Fonction pour obtenir l'URL WhatsApp
function getWhatsAppUrl(message) {
    return `https://wa.me/${CONFIG.whatsappNumber}?text=${encodeURIComponent(message)}`;
}

// Fonction pour formater le prix
function formatPrice(price) {
    return `${price.toFixed(2)} ${CONFIG.currency}`;
}

// Export pour utilisation dans d'autres fichiers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}
