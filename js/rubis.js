// Rubis Page JavaScript - Straindez Style
document.addEventListener('DOMContentLoaded', async () => {
    await initRubis();
});

async function initRubis() {
    if (!APP_STATE.config) {
        await loadConfig();
    }
    loadRubisFromConfig();
}

function loadRubisFromConfig() {
    const container = document.getElementById('rubis-container');
    if (!container) {
        console.error('Rubis container not found');
        return;
    }

    // Data hardcoded dos rubis - pacotes de moeda virtual
    // 6 pacotes completos com imagens únicas
    const rubisData = [
        {
            name: '100 Rubis',
            amount: 100,
            price: 1.00,
            image: 'assets/rubis-saco-pequeno.png.png',
            theme: 'ruby-small',
            bonus: null,
            imageSize: 'small'
        },
        {
            name: '500 Rubis',
            amount: 500,
            price: 4.50,
            originalPrice: 5.00,
            image: 'assets/rubis-barril.png.png',
            theme: 'ruby-medium',
            bonus: '+10% Bonus',
            imageSize: 'medium'
        },
        {
            name: '1000 Rubis',
            amount: 1000,
            price: 8.50,
            originalPrice: 10.00,
            image: 'assets/rubis-bau-grande.png.png',
            theme: 'ruby-large',
            bonus: '+15% Bonus',
            imageSize: 'large'
        },
        {
            name: '2500 Rubis',
            amount: 2500,
            price: 20.00,
            originalPrice: 25.00,
            image: 'assets/rubis-saco-pequeno.png.png.png',
            theme: 'ruby-mega',
            bonus: '+20% Bonus',
            imageSize: 'xlarge'
        },
        {
            name: '5000 Rubis',
            amount: 5000,
            price: 37.50,
            originalPrice: 50.00,
            image: 'assets/rubis-bau-grande.png.png.png',
            theme: 'ruby-ultra',
            bonus: '+25% Bonus',
            imageSize: 'xlarge'
        },
        {
            name: '10000 Rubis',
            amount: 10000,
            price: 70.00,
            originalPrice: 100.00,
            image: 'assets/rubis-barril-grande.png.png.png',
            theme: 'ruby-legendary',
            bonus: '+30% Bonus',
            imageSize: 'huge'
        }
    ];

    // Create cards for each rubis package
    rubisData.forEach(rubisPackage => {
        const card = createRubisCard(rubisPackage);
        container.appendChild(card);
    });
}

function createRubisCard(rubisPackage) {
    const card = document.createElement('div');
    const { name, amount, price, originalPrice, image, theme, bonus, imageSize } = rubisPackage;
    
    const themeClass = `package-${theme}-card`;
    const sizeClass = imageSize ? `rubis-size-${imageSize}` : '';
    card.className = `package ${themeClass} ${sizeClass}`;
    
    // Bonus badge
    const bonusBadge = bonus ? 
        `<div class="rubis-bonus-badge">
            <i class="fa-solid fa-star"></i>
            <span>${bonus}</span>
        </div>` : '';
    
    card.innerHTML = `
        ${bonusBadge}
        <div class="image">
            <div class="package-image-wrap">
                <img src="${image}" class="package-image-glow rubis-glow" alt="${name}" loading="lazy" onerror="this.style.display='none'">
                <img src="${image}" class="package-active-image package-image-main" alt="${name}" loading="lazy" onerror="this.style.display='none'">
            </div>
        </div>
        <div class="info">
            <div class="text">
                <div class="rubis-amount">
                    <i class="fa-solid fa-gem"></i> ${amount.toLocaleString()}
                </div>
                <div class="name">${name.toUpperCase()}</div>
                <div class="price">
                    ${originalPrice ? `<span class="discount">${originalPrice.toFixed(2)} EUR</span>` : ''}
                    <span class="package-active-price">${price.toFixed(2)} EUR</span>
                </div>
            </div>
            <div class="button">
                <button class="btn btn-info package-active-action rubis-add-cart">
                    <i class="fa-solid fa-cart-shopping"></i> ADD TO CART
                </button>
            </div>
        </div>
    `;

    // Add to cart handler
    const addButton = card.querySelector('.rubis-add-cart');
    addButton.addEventListener('click', () => {
        addToCart({
            id: `rubis-${amount}`,
            type: 'rubis',
            name: `${amount} Rubis`,
            price: price,
            quantity: 1,
            image: image
        });
        
        // Visual feedback
        addButton.innerHTML = '<i class="fa-solid fa-check"></i> ADDED!';
        addButton.style.opacity = '0.7';
        setTimeout(() => {
            addButton.innerHTML = '<i class="fa-solid fa-cart-shopping"></i> ADD TO CART';
            addButton.style.opacity = '1';
        }, 1500);
    });
    
    return card;
}

// Export for use in other scripts
window.loadRubisFromConfig = loadRubisFromConfig;
