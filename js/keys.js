// Keys Page JavaScript - Straindez Style
document.addEventListener('DOMContentLoaded', async () => {
    await initKeys();
});

async function initKeys() {
    if (!APP_STATE.config) {
        await loadConfig();
    }
    loadKeysFromConfig();
}

function loadKeysFromConfig() {
    const container = document.getElementById('keys-container');
    if (!container) {
        console.error('Keys container not found');
        return;
    }

    // Data hardcoded das keys com variantes (x1, x5, x10, x20)
    const keysData = [
        {
            baseName: 'Magma Key',
            image: 'assets/magma-key.png',
            theme: 'magma',
            limited: true,
            variants: {
                '1x': { price: 5.96, originalPrice: 7.98 },
                '5x': { price: 29.80, originalPrice: 39.90 },
                '10x': { price: 56.64, originalPrice: 79.80 },
                '20x': { price: 106.56, originalPrice: 159.60 }
            }
        },
        {
            baseName: 'Atlantic Key',
            image: 'assets/atlantic-key.png',
            theme: 'atlantic',
            limited: false,
            variants: {
                '1x': { price: 7.20, originalPrice: null },
                '6x': { price: 34.20, originalPrice: 36.00 },
                '10x': { price: 64.80, originalPrice: 72.00 },
                '20x': { price: 122.40, originalPrice: 144.00 }
            }
        },
        {
            baseName: 'Dev Key',
            image: 'assets/dev-key.png',
            theme: 'dev',
            limited: false,
            variants: {
                '1x': { price: 8.86, originalPrice: null },
                '5x': { price: 42.15, originalPrice: 44.30 },
                '10x': { price: 79.74, originalPrice: 88.60 },
                '20x': { price: 150.52, originalPrice: 177.20 }
            }
        }
    ];

    // Create cards for each key group
    keysData.forEach(keyData => {
        const card = createKeyCard(keyData);
        container.appendChild(card);
    });
}

function createKeyCard(keyData) {
    const card = document.createElement('div');
    const { baseName, image, theme, limited, variants } = keyData;
    
    // Determine card theme class
    const themeClass = `package-${theme}-card`;
    
    card.className = `package package-bulk-card ${themeClass}`;
    
    // Create themed background effects based on key type
    let themedBackground = '';
    
    if (theme === 'magma') {
        themedBackground = `
            <div class="key-card-bg magma-card-bg">
                <div class="magma-star"></div>
                <div class="magma-star"></div>
                <div class="magma-star"></div>
                <div class="magma-star"></div>
                <div class="magma-nebula magma-nebula-1"></div>
                <div class="magma-nebula magma-nebula-2"></div>
            </div>
        `;
    } else if (theme === 'atlantic') {
        themedBackground = `
            <div class="key-card-bg atlantic-card-bg">
                <div class="atlantic-wave atlantic-wave-1"></div>
                <div class="atlantic-wave atlantic-wave-2"></div>
                <div class="atlantic-wave atlantic-wave-3"></div>
                <div class="atlantic-fish atlantic-fish-1">🐟</div>
                <div class="atlantic-fish atlantic-fish-2">🐠</div>
                <div class="atlantic-fish atlantic-fish-3">🐡</div>
                <div class="atlantic-bubble"></div>
                <div class="atlantic-bubble"></div>
                <div class="atlantic-bubble"></div>
            </div>
        `;
    } else if (theme === 'dev') {
        themedBackground = `
            <div class="key-card-bg dev-card-bg">
                <div class="dev-code-line">{"status":"active"}</div>
                <div class="dev-code-line">function unlock() { return true; }</div>
                <div class="dev-code-line">const key = "DEV_ACCESS_GRANTED";</div>
                <div class="dev-code-line">console.log("HACKED");</div>
                <div class="dev-code-line">while(true) { boost(); }</div>
                <div class="dev-matrix">01010</div>
                <div class="dev-matrix">11001</div>
                <div class="dev-matrix">10110</div>
                <div class="dev-matrix">00111</div>
                <div class="dev-grid"></div>
                <div class="dev-grid"></div>
                <div class="dev-grid"></div>
            </div>
        `;
    }
    
    // Limited badge for time key
    const limitedBadge = limited ? 
        '<div class="package-bonus-badge"><span>Bonus</span><span>Limited<br/>Time Only</span></div>' : '';
    
    // Create quantity buttons - usando 6x para Atlantic Key
    const quantities = baseName === 'Atlantic Key' ? ['1x', '6x', '10x', '20x'] : ['1x', '5x', '10x', '20x'];
    let quantityButtons = '';
    quantities.forEach((qty, index) => {
        if (variants[qty]) {
            quantityButtons += `
                <button type="button" class="package-quantity-option ${index === 0 ? 'is-active' : ''}" 
                        data-quantity="${qty}">
                    ${qty.toUpperCase()}
                </button>
            `;
        }
    });

    // Get first available variant for initial display
    const initialVariant = variants['1x'];
    
    card.innerHTML = `
        ${themedBackground}
        ${limitedBadge}
        <div class="image">
            <div class="package-image-wrap">
                <img src="${image}" class="package-image-glow" alt="${baseName}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x300/${theme === 'magma' ? 'FF3333' : theme === 'atlantic' ? '00A3FF' : '00FF66'}/FFFFFF?text=${baseName.replace(' ', '+')}">
                <img src="${image}" class="package-active-image package-image-main" alt="${baseName}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x300/${theme === 'magma' ? 'FF3333' : theme === 'atlantic' ? '00A3FF' : '00FF66'}/FFFFFF?text=${baseName.replace(' ', '+')}">
            </div>
        </div>
        <div class="info">
            <div class="text">
                <div class="name">${baseName.toUpperCase()}</div>
                <div class="price">
                    ${initialVariant.originalPrice ? `<span class="discount">${initialVariant.originalPrice.toFixed(2)} EUR</span>` : ''}
                    <span class="package-active-price">${initialVariant.price.toFixed(2)} EUR</span>
                </div>
                ${limited ? '<div class="package-limited-note">LIMITED TIME ONLY</div>' : ''}
            </div>
            <div class="package-quantity-options">
                ${quantityButtons}
            </div>
            <div class="button">
                <button class="btn btn-info package-active-action">
                    ADD TO CART
                </button>
            </div>
        </div>
    `;

    // Setup interactivity
    setupKeyCardInteractions(card, baseName, variants);
    
    return card;
}

function setupKeyCardInteractions(card, baseName, variants) {
    const quantityBtns = card.querySelectorAll('.package-quantity-option');
    const priceElement = card.querySelector('.package-active-price');
    const discountElement = card.querySelector('.discount');
    const addButton = card.querySelector('.package-active-action');
    
    let currentQuantity = '1x';
    
    // Quantity button click handlers
    quantityBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active from all
            quantityBtns.forEach(b => b.classList.remove('is-active'));
            // Add active to clicked
            btn.classList.add('is-active');
            
            // Update price based on quantity
            currentQuantity = btn.getAttribute('data-quantity');
            const variant = variants[currentQuantity];
            
            if (variant) {
                priceElement.textContent = `${variant.price.toFixed(2)} EUR`;
                if (discountElement && variant.originalPrice) {
                    discountElement.textContent = `${variant.originalPrice.toFixed(2)} EUR`;
                    discountElement.style.display = 'block';
                } else if (discountElement) {
                    discountElement.style.display = 'none';
                }
            }
        });
    });

    // Add to cart button handler
    addButton.addEventListener('click', () => {
        const selectedVariant = variants[currentQuantity];
        
        if (selectedVariant) {
            addToCart({
                id: `${baseName.toLowerCase().replace(' ', '-')}-${currentQuantity}`,
                type: 'key',
                name: `${baseName} ${currentQuantity.toUpperCase()}`,
                price: selectedVariant.price,
                quantity: 1,
                image: card.querySelector('.package-active-image').src
            });
            
            // Visual feedback
            addButton.textContent = 'ADDED!';
            addButton.style.opacity = '0.7';
            setTimeout(() => {
                addButton.textContent = 'ADD TO CART';
                addButton.style.opacity = '1';
            }, 1500);
        }
    });
}

// Export for use in other scripts
window.loadKeysFromConfig = loadKeysFromConfig;

