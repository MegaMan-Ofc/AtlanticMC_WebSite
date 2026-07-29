// Boosters Page JavaScript
document.addEventListener('DOMContentLoaded', async () => {
    await initBoosters();
});

async function initBoosters() {
    if (!APP_STATE.config) {
        await loadConfig();
    }
    loadBoostersFromConfig();
}

function loadBoostersFromConfig() {
    const container = document.getElementById('boosters-container');
    if (!container) {
        console.error('Boosters container not found');
        return;
    }

    // Data dos pacotes de corações
    const boostersData = [
        {
            id: 'hearts-pack-1',
            name: 'Pack de 1 Coração',
            icon: '❤️',
            color: '#FF1744',
            description: 'Adiciona 1 coração extra de vida',
            price: 1.99,
            multiplier: '+1 ❤️'
        },
        {
            id: 'hearts-pack-2',
            name: 'Pack de 2 Corações',
            icon: '❤️',
            color: '#E91E63',
            description: 'Adiciona 2 corações extras de vida',
            price: 3.49,
            multiplier: '+2 ❤️'
        },
        {
            id: 'hearts-pack-5',
            name: 'Pack de 5 Corações',
            icon: '❤️',
            color: '#F44336',
            description: 'Adiciona 5 corações extras de vida',
            price: 7.99,
            multiplier: '+5 ❤️'
        },
        {
            id: 'hearts-pack-10',
            name: 'Pack de 10 Corações',
            icon: '❤️',
            color: '#C2185B',
            description: 'Adiciona 10 corações extras de vida',
            price: 14.99,
            multiplier: '+10 ❤️'
        }
    ];

    // Create cards for each booster
    boostersData.forEach(booster => {
        const card = createBoosterCard(booster);
        container.appendChild(card);
    });
}

function createBoosterCard(boosterData) {
    const card = document.createElement('div');
    const { id, name, icon, color, description, price, multiplier } = boosterData;
    
    card.className = `package package-booster-card booster-${id}`;
    card.style.setProperty('--booster-color', color);
    
    card.innerHTML = `
        <div class="booster-card-bg booster-bg-${id}">
            <div class="booster-particle"></div>
            <div class="booster-particle"></div>
            <div class="booster-particle"></div>
            <div class="booster-particle"></div>
            <div class="booster-particle"></div>
        </div>
        <div class="booster-icon-wrap">
            <img src="assets/heart (2).png" alt="Heart" class="booster-icon-img" style="width: 80px; height: 80px; object-fit: contain;">
        </div>
        <div class="info">
            <div class="text">
                <div class="name">${name.toUpperCase()}</div>
                <div class="booster-description">${description}</div>
                <div class="booster-multiplier">${multiplier}</div>
                <div class="price">
                    <span class="package-active-price">${price.toFixed(2)} EUR</span>
                </div>
            </div>
            <div class="button">
                <button class="btn btn-info booster-active-action" style="background: ${color};">
                    <i class="fa-solid fa-cart-plus"></i> ADD TO CART
                </button>
            </div>
        </div>
    `;

    // Setup interactivity
    setupBoosterCardInteractions(card, boosterData);
    
    return card;
}

function setupBoosterCardInteractions(card, boosterData) {
    const { id, name, price, multiplier, color, icon } = boosterData;
    const addButton = card.querySelector('.booster-active-action');
    
    // Add to cart button handler
    addButton.addEventListener('click', () => {
        addToCart({
            id: id,
            type: 'hearts',
            name: name,
            price: price,
            quantity: 1,
            image: 'assets/heart (2).png'
        });
        
        // Visual feedback
        addButton.innerHTML = '<i class="fa-solid fa-check"></i> ADICIONADO!';
        addButton.style.opacity = '0.9';
        setTimeout(() => {
            addButton.innerHTML = '<i class="fa-solid fa-cart-plus"></i> ADD TO CART';
            addButton.style.opacity = '1';
        }, 1500);
    });
}

// Export for use in other scripts
window.loadBoostersFromConfig = loadBoostersFromConfig;
