// Battle Pass (Rank Upgrades) Page JavaScript
document.addEventListener('DOMContentLoaded', async () => {
    await initBattlePass();
});

async function initBattlePass() {
    if (!APP_STATE.config) {
        await loadConfig();
    }
    loadBattlePassFromConfig();
}

function loadBattlePassFromConfig() {
    const container = document.getElementById('battlepass-container');
    if (!container) {
        console.error('Battle Pass container not found');
        return;
    }

    // Data dos 3 tipos de Battle Pass
    const battlePassData = [
        {
            name: 'Battle Pass',
            subtitle: 'Season Básico',
            price: 9.99,
            originalPrice: null,
            image: 'assets/battlepass/battlepass-basic.png',
            color: '#6C5CE7',
            type: 'basic',
            benefits: [
                '50 níveis de recompensas',
                'Cosméticos exclusivos da season',
                'Desafios semanais',
                'XP boost 1.5x',
                'Chat tag especial',
                'Acesso antecipado a eventos'
            ]
        },
        {
            name: 'Battle Pass +25 Níveis',
            subtitle: 'Season + Boost',
            price: 19.99,
            originalPrice: 24.99,
            image: 'assets/battlepass/battlepass-boost.png',
            color: '#FFD166',
            type: 'boost',
            badge: '+25 NÍVEIS',
            benefits: [
                'Battle Pass básico incluído',
                'Começa no nível 25 automaticamente',
                'Desbloqueia recompensas instantaneamente',
                'XP boost 2x',
                'Cosméticos premium extras',
                'Título dourado exclusivo'
            ]
        },
        {
            name: 'Battle Pass Completo',
            subtitle: 'Desbloqueia Tudo',
            price: 49.99,
            originalPrice: 59.99,
            image: 'assets/battlepass/battlepass-complete.png',
            color: '#FF6B35',
            type: 'complete',
            special: true,
            badge: 'MELHOR VALOR',
            benefits: [
                'Todos os 50 níveis desbloqueados',
                'Todas as recompensas instantâneas',
                'Cosméticos ultra raros',
                'XP boost 3x permanente',
                'Título lendário exclusivo',
                'Acesso VIP a eventos',
                '500 Rubis bônus',
                'Kit Battle Pass lendário'
            ]
        }
    ];

    // Create cards for each battle pass tier
    battlePassData.forEach(tier => {
        const card = createBattlePassCard(tier);
        container.appendChild(card);
    });
}

function createBattlePassCard(tier) {
    const card = document.createElement('div');
    const { name, subtitle, price, originalPrice, image, color, special, badge, benefits, type } = tier;
    
    card.className = 'package battlepass-card';
    card.style.setProperty('--bp-color', color);
    
    // Badge (para boost e complete)
    const badgeHtml = badge ? 
        `<div class="battlepass-special-badge" style="background: linear-gradient(135deg, ${color}, ${adjustColor(color, -30)});">
            <i class="fa-solid fa-star"></i> ${badge}
        </div>` : '';
    
    // Subtitle
    const subtitleHtml = subtitle ? 
        `<div class="battlepass-subtitle">${subtitle}</div>` : '';
    
    card.innerHTML = `
        ${badgeHtml}
        <div class="image">
            <div class="package-image-wrap">
                <img src="${image}" class="package-image-glow" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x300/${color.replace('#', '')}/FFFFFF?text=${name.replace(/ /g, '+')}'">
                <img src="${image}" class="package-active-image package-image-main" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x300/${color.replace('#', '')}/FFFFFF?text=${name.replace(/ /g, '+')}'">
            </div>
        </div>
        <div class="info">
            <div class="text">
                <div class="name" style="color: ${color};">${name.toUpperCase()}</div>
                ${subtitleHtml}
                <div class="battlepass-benefits">
                    ${benefits.map(b => `<div class="benefit-item"><i class="fa-solid fa-check"></i> ${b}</div>`).join('')}
                </div>
                <div class="price">
                    ${originalPrice ? `<span class="discount">${originalPrice.toFixed(2)} EUR</span>` : ''}
                    <span class="package-active-price" style="color: ${color};">${price.toFixed(2)} EUR</span>
                </div>
            </div>
            <div class="button">
                <button class="btn btn-info package-active-action battlepass-add-cart" style="background: linear-gradient(135deg, ${color}, ${adjustColor(color, -20)});">
                    <i class="fa-solid fa-cart-shopping"></i> COMPRAR AGORA
                </button>
            </div>
        </div>
    `;

    // Add to cart handler
    const addButton = card.querySelector('.battlepass-add-cart');
    addButton.addEventListener('click', () => {
        addToCart({
            id: `battlepass-${type}`,
            type: 'battlepass',
            name: name,
            price: price,
            quantity: 1,
            image: image
        });
        
        // Visual feedback
        addButton.innerHTML = '<i class="fa-solid fa-check"></i> ADICIONADO!';
        addButton.style.opacity = '0.7';
        setTimeout(() => {
            addButton.innerHTML = '<i class="fa-solid fa-cart-shopping"></i> COMPRAR AGORA';
            addButton.style.opacity = '1';
        }, 1500);
    });
    
    return card;
}

// Helper function to adjust color brightness
function adjustColor(color, amount) {
    const num = parseInt(color.replace('#', ''), 16);
    const r = Math.max(0, Math.min(255, (num >> 16) + amount));
    const g = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + amount));
    const b = Math.max(0, Math.min(255, (num & 0x0000FF) + amount));
    return '#' + ((r << 16) | (g << 8) | b).toString(16).padStart(6, '0');
}

// Export for use in other scripts
window.loadBattlePassFromConfig = loadBattlePassFromConfig;
