// ===== Products Database =====
const products = [
    // Homme
    {
        id: 1,
        name: "Dior Sauvage",
        description: "Parfum moderne et puissant, notes boisées et épicées",
        price: 890,
        oldPrice: null,
        category: "homme",
        tags: ["nouveautes"],
        image: "https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=500&h=500&fit=crop"
    },
    {
        id: 2,
        name: "Bleu de Chanel",
        description: "Élégance intemporelle, notes boisées aromatiques",
        price: 920,
        oldPrice: null,
        category: "homme",
        tags: [],
        image: "https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=500&h=500&fit=crop"
    },
    {
        id: 3,
        name: "Giorgio Armani Acqua",
        description: "Fraîcheur méditerranéenne, notes aquatiques",
        price: 780,
        oldPrice: 950,
        category: "homme",
        tags: ["offres"],
        image: "https://images.unsplash.com/photo-1541643600914-78b084683601?w=500&h=500&fit=crop"
    },
    {
        id: 4,
        name: "Paco Rabanne 1 Million",
        description: "Audacieux et séduisant, notes épicées et cuirées",
        price: 850,
        oldPrice: null,
        category: "homme",
        tags: [],
        image: "https://images.unsplash.com/photo-1594035910387-fea47794261f?w=500&h=500&fit=crop"
    },
    
    // Femme
    {
        id: 5,
        name: "Chanel N°5",
        description: "Icône intemporelle, notes florales aldéhydées",
        price: 950,
        oldPrice: null,
        category: "femme",
        tags: [],
        image: "https://images.unsplash.com/photo-1588405748880-12d1d2a59bd9?w=500&h=500&fit=crop"
    },
    {
        id: 6,
        name: "Lancôme La Vie Est Belle",
        description: "Douceur gourmande, notes de praline et vanille",
        price: 820,
        oldPrice: null,
        category: "femme",
        tags: ["nouveautes"],
        image: "https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop"
    },
    {
        id: 7,
        name: "Gucci Bloom",
        description: "Jardin fleuri, notes de jasmin et tubéreuse",
        price: 760,
        oldPrice: 920,
        category: "femme",
        tags: ["offres"],
        image: "https://images.unsplash.com/photo-1587017539504-67cfbddac569?w=500&h=500&fit=crop"
    },
    {
        id: 8,
        name: "YSL Black Opium",
        description: "Café noir et vanille, addiction pure",
        price: 880,
        oldPrice: null,
        category: "femme",
        tags: [],
        image: "https://images.unsplash.com/photo-1563170351-be82bc888aa4?w=500&h=500&fit=crop"
    },
    {
        id: 9,
        name: "Valentino Donna",
        description: "Élégance florale, notes de rose et iris",
        price: 790,
        oldPrice: null,
        category: "femme",
        tags: [],
        image: "https://images.unsplash.com/photo-1541643600914-78b084683601?w=500&h=500&fit=crop"
    },
    
    // Mixte
    {
        id: 10,
        name: "Tom Ford Oud Wood",
        description: "Bois précieux d'Orient, notes de oud et épices",
        price: 1200,
        oldPrice: null,
        category: "mixte",
        tags: ["nouveautes"],
        image: "https://images.unsplash.com/photo-1610969524113-bae462bb3892?w=500&h=500&fit=crop"
    },
    {
        id: 11,
        name: "Byredo Gypsy Water",
        description: "Nomade et libre, notes boisées et vanille",
        price: 1100,
        oldPrice: null,
        category: "mixte",
        tags: [],
        image: "https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop"
    },
    {
        id: 12,
        name: "Le Labo Santal 33",
        description: "Bois de santal et cardamome, signature unique",
        price: 1150,
        oldPrice: 1400,
        category: "mixte",
        tags: ["offres"],
        image: "https://images.unsplash.com/photo-1610969524113-bae462bb3892?w=500&h=500&fit=crop"
    },
    
    // NOUVEAUX PRODUITS AJOUTÉS POUR TEST
    {
        id: 13,
        name: "Hermès Terre d'Hermès",
        description: "Fraîcheur épicée et boisée, notes de pamplemousse",
        price: 980,
        oldPrice: null,
        category: "homme",
        tags: ["nouveautes"],
        image: "https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=500&h=500&fit=crop"
    },
    {
        id: 14,
        name: "Viktor & Rolf Flowerbomb",
        description: "Explosion florale, notes de jasmin et orchidée",
        price: 840,
        oldPrice: 1050,
        category: "femme",
        tags: ["offres"],
        image: "https://images.unsplash.com/photo-1588405748880-12d1d2a59bd9?w=500&h=500&fit=crop"
    },
    {
        id: 15,
        name: "Creed Aventus",
        description: "Sophistication masculine, notes d'ananas et bouleau",
        price: 1350,
        oldPrice: null,
        category: "homme",
        tags: [],
        image: "https://images.unsplash.com/photo-1594035910387-fea47794261f?w=500&h=500&fit=crop"
    },
    {
        id: 16,
        name: "Maison Francis Kurkdjian Baccarat Rouge 540",
        description: "Luxe absolu, notes ambrées et florales",
        price: 1480,
        oldPrice: null,
        category: "mixte",
        tags: ["nouveautes"],
        image: "https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop"
    },
    {
        id: 17,
        name: "Dolce & Gabbana Light Blue",
        description: "Fraîcheur méditerranéenne, notes de citron et pomme",
        price: 720,
        oldPrice: 890,
        category: "femme",
        tags: ["offres"],
        image: "https://images.unsplash.com/photo-1587017539504-67cfbddac569?w=500&h=500&fit=crop"
    }
];

// ===== Helper Functions =====
function getCategoryLabel(category) {
    const labels = {
        'homme': 'Homme',
        'femme': 'Femme',
        'mixte': 'Mixte'
    };
    return labels[category] || category;
}

function getProductById(id) {
    return products.find(p => p.id === parseInt(id));
}
