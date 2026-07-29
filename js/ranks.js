// Ranks Page JavaScript - Straindez Style
document.addEventListener('DOMContentLoaded', async () => {
    await initRanks();
});

async function initRanks() {
    if (!APP_STATE.config) {
        await loadConfig();
    }
    loadRanksFromConfig();
}

function loadRanksFromConfig() {
    const container = document.getElementById('ranks-container');
    if (!container) {
        console.error('Ranks container not found');
        return;
    }

    // Mapeamento de cores por ID (cores mais suaves e naturais)
    const rankColors = {
        'vip': '#8B7355',        // Madeira (marrom)
        'mvp': '#3E3E3E',        // Carvão (cinza escuro)
        'marshal': '#D8D8D8',    // Ferro (cinza claro)
        'mythic': '#FFD700',     // Ouro (dourado)
        'celestial': '#00D9FF',  // Diamante (azul claro)
        'straindez': '#FF6B35',  // Laranja
        'custom': '#6C5CE7'      // Roxo
    };

    // Fallback data se config não carregar
    const fallbackRanks = [
        {
            id: 'vip',
            name: 'WOOD',
            price: 6.64,
            image: 'assets/madeira.gif',
            description: 'WOOD prefix in front of your name',
            features: [
                '/workbench command',
                'Diamond Armor (Protection 1, Unbreaking 1)',
                'Diamond Pickaxe (Efficiency 2)',
                'Shield',
                'Bow',
                '7 Golden Apples',
                '32 XP Bottles',
                'Diamonds, Iron, Totem',
                'Arrows, Steak, Ender Pearls',
                'Fire Resistance Potion',
                'Cobwebs, Water Bucket'
            ]
        },
        {
            id: 'mvp',
            name: 'COAL',
            price: 13.84,
            image: 'assets/carvao.png',
            description: 'COAL prefix - All Wood perks plus more!',
            features: [
                'Diamond Armor (Protection 2-3, Unbreaking 1-2)',
                'Shield (Unbreaking 1)',
                'Diamond Sword (Sharpness 3, Fire Aspect 1)',
                'Diamond Pickaxe (Efficiency 3, Unbreaking 1)',
                'Diamond Axe (Efficiency 3, Unbreaking 1)',
                '12 Golden Apples',
                '2 Totems of Undying',
                '16 Ender Pearls',
                '80 Steak',
                '22 Diamonds, 45 Iron',
                'Bow (Unbreaking 1, Infinity)',
                '48 Cobwebs',
                'Instant Health II Potion',
                'Speed Potion (3 min)',
                'Fire Resistance (3 min)',
                'Water Bucket',
                '50 XP Bottles'
            ]
        },
        {
            id: 'marshal',
            name: 'IRON',
            price: 24.35,
            image: 'assets/ferro.png',
            description: 'IRON prefix - Elite warrior!',
            features: [
                'Diamond Armor (Protection 2-3, Unbreaking 2-3)',
                'Shield (Unbreaking 3)',
                'Diamond Sword (Sharpness 4, Fire Aspect 1, Unbreaking 2)',
                'Diamond Pickaxe (Fortune 2, Efficiency 4, Unbreaking 3)',
                'Diamond Axe (Efficiency 4, Unbreaking 3)',
                'Diamond Shovel (Efficiency 3, Unbreaking 3)',
                'Diamond Spear (Lunge 2, Sharpness 4, Unbreaking 3)',
                '32 Diamonds',
                '64 Iron',
                '2 Totems of Undying',
                '8 Enchanted Golden Apples',
                'Bow (Flame 1, Infinity, Unbreaking 3)',
                '16 Ender Pearls',
                '64 Cobwebs',
                '2 Fire Resistance Potions (3 min)',
                '1 Speed Potion (3 min)',
                '1 Instant Health II Potion',
                '64 Steak',
                '20 Golden Apples',
                'Water Bucket',
                '64 XP Bottles'
            ]
        },
        {
            id: 'mythic',
            name: 'GOLD',
            price: 32.68,
            image: 'assets/ouro.png',
            description: 'GOLD prefix - Legendary status!',
            features: [
                'Netherite Helmet (P4, Unbreaking 3, Respiration 2, Aqua Infinity 1, Mending)',
                'Netherite Chestplate (P4, Unbreaking 3)',
                'Netherite Leggings (P4, Unbreaking 3)',
                'Netherite Boots (P4, Unbreaking 3, Feather Falling 3, Depth Strider, Mending)',
                'Shield (Unbreaking 3)',
                'Diamond Sword (Fire Aspect 1, Unbreaking 3, Sharpness 4, Looting 2)',
                'Diamond Pickaxe (Fortune 3, Efficiency 4, Unbreaking 3)',
                'Diamond Axe (Unbreaking 3, Efficiency 4)',
                'Diamond Shovel (Efficiency 4, Unbreaking 3)',
                'Diamond Spear (Lunge 2, Sharpness 4, Unbreaking 3)',
                '16 Ender Pearls',
                '64 Cobwebs',
                '1 Instant Health II Potion',
                '2 Speed Potions (3 min)',
                '2 Fire Resistance Potions (3 min)',
                '40 Golden Apples',
                '16 Enchanted Golden Apples',
                '2 Totems of Undying',
                '84 XP Bottles',
                'Bow (Infinity, Unbreaking 3, Flame)',
                '64 Steak',
                'Water Bucket'
            ]
        },
        {
            id: 'celestial',
            name: 'DIAMOND',
            price: 57.86,
            image: 'assets/diamante.png',
            description: 'DIAMOND prefix - Ultimate power!',
            features: [
                'Netherite Helmet (P4, Unbreaking 3, Respiration 3, Aqua Infinity 1, Mending)',
                'Netherite Chestplate (P4, Unbreaking 3, Mending)',
                'Netherite Leggings (P4, Unbreaking 3, Mending)',
                'Netherite Boots (P4, Unbreaking 3, Feather Falling 4, Depth Strider 3, Soul Speed 3, Mending)',
                'Netherite Pickaxe (Fortune 3, Efficiency 5, Unbreaking 3, Mending)',
                'Netherite Shovel (Efficiency 5, Unbreaking 3, Mending)',
                'Netherite Axe (Efficiency 5, Unbreaking 3, Mending, Sharpness 5)',
                'Crossbow (Piercing 4, Quick Charge 3, Unbreaking 3, Mending)',
                '3 Totems of Undying',
                'Netherite Spear (Lunge 3, Sharpness 5, Looting 3, Unbreaking 3, Mending)',
                'Mace (Wind Burst 3, Density 5, Fire Aspect 2, Unbreaking 3, Mending)',
                'Trident (Channeling 1, Impaling 5, Loyalty 3, Unbreaking 3, Mending)',
                'Bow (Power 5, Flame, Mending, Punch 2, Unbreaking 3)',
                'Shield (Unbreaking 3, Mending)',
                '64 Cobwebs',
                '2 Packs XP Bottles',
                '32 Wind Charges',
                '3 Packs Ender Pearls',
                '32 Enchanted Golden Apples',
                '64 Golden Apples',
                '64 Golden Carrots'
            ]
        }
    ];

    // Use config ranks ou fallback, mas filtra STRAINDEZ e CUSTOM
    let ranks = (APP_STATE.config && APP_STATE.config.ranks) ? APP_STATE.config.ranks : fallbackRanks;
    
    // Filtrar apenas os ranks que queremos (remover straindez e custom)
    ranks = ranks.filter(rank => rank.id !== 'straindez' && rank.id !== 'custom');
    
    console.log('Loading ranks:', ranks.length);
    
    // Create cards for each rank
    ranks.forEach(rank => {
        const rankData = {
            id: rank.id,
            name: rank.name.replace(' Rank', ''), // Remove "Rank" do nome
            price: rank.price,
            color: rankColors[rank.id] || '#6C5CE7',
            image: rank.image,
            kitImage: `assets/kits/${rank.id}-kit.png`,
            description: rank.description,
            features: rank.features || [],
            kit: rank.kit ? { command: `/kit ${rank.id}` } : null
        };
        
        const card = createRankCard(rankData);
        container.appendChild(card);
    });
    
    console.log('Ranks loaded successfully');
}

function createRankCard(rankData) {
    const card = document.createElement('div');
    const { id, name, price, color, image, description } = rankData;
    
    card.className = 'package package-rank-card';
    card.setAttribute('data-rank', id);
    card.style.setProperty('--rank-color', color);
    
    card.innerHTML = `
        <div class="rank-card-bg rank-bg-${id}"></div>
        <div class="image">
            <div class="package-image-wrap">
                <img src="${image}" class="package-image-glow" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x300/${color.replace('#', '')}/FFFFFF?text=${name.replace(/ /g, '+')}'">
                <img src="${image}" class="package-active-image package-image-main" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x300/${color.replace('#', '')}/FFFFFF?text=${name.replace(/ /g, '+')}'">
            </div>
        </div>
        <div class="info">
            <div class="text">
                <div class="name" style="color: ${color};">${name.toUpperCase()}</div>
                <div class="price">
                    <span class="package-active-price" style="color: ${color};">€${price.toFixed(2)}</span>
                </div>
            </div>
            <div class="button">
                <button class="btn btn-info rank-view-details" style="background: ${color};">
                    <i class="fa-solid fa-eye"></i> VIEW DETAILS
                </button>
            </div>
        </div>
    `;

    // Setup click handler to open modal
    const viewButton = card.querySelector('.rank-view-details');
    viewButton.addEventListener('click', () => {
        openRankModal(rankData);
    });
    
    return card;
}

function openRankModal(rankData) {
    const modal = document.getElementById('rank-modal');
    const { id, name, price, color, description, features, kit, kitImage } = rankData;
    
    // Set modal data
    document.getElementById('modal-rank-name').textContent = name.toUpperCase();
    document.getElementById('modal-rank-name').style.color = color;
    document.getElementById('modal-rank-description').textContent = description;
    document.getElementById('modal-price').textContent = `€${price.toFixed(2)}`;
    document.getElementById('modal-price').style.color = color;
    
    // Populate features
    const featuresList = document.getElementById('modal-rank-features');
    featuresList.innerHTML = '';
    
    if (features && features.length > 0) {
        features.forEach(feature => {
            const li = document.createElement('li');
            li.textContent = feature;
            featuresList.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.textContent = 'Feature details coming soon!';
        li.style.opacity = '0.5';
        featuresList.appendChild(li);
    }
    
    // Set kit command and image
    if (kit) {
        document.getElementById('modal-kit-command').textContent = kit.command;
        
        // Set kit image
        const kitImageEl = document.getElementById('modal-kit-image');
        kitImageEl.src = kitImage;
        kitImageEl.onerror = function() {
            // Fallback placeholder if image not found
            this.src = `https://via.placeholder.com/800x300/${color.replace('#', '')}/FFFFFF?text=${name}+Kit+Preview`;
        };
    } else {
        // Hide kit section if no kit
        document.getElementById('modal-kit-preview').style.display = 'none';
    }
    
    // Add to cart handler
    const addButton = document.getElementById('modal-add-cart');
    addButton.onclick = () => {
        addToCart({
            id: id,
            type: 'rank',
            name: `${name} Rank`,
            price: price,
            quantity: 1,
            image: rankData.image
        });
        
        // Visual feedback
        addButton.innerHTML = '<i class="fa-solid fa-check"></i> ADDED!';
        addButton.style.opacity = '0.7';
        setTimeout(() => {
            addButton.innerHTML = '<i class="fa-solid fa-cart-shopping"></i> Add to Cart';
            addButton.style.opacity = '1';
        }, 1500);
    };
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRankModal() {
    const modal = document.getElementById('rank-modal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Export functions
window.openRankModal = openRankModal;
window.closeRankModal = closeRankModal;
