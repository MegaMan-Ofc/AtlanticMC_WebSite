// Tebex Integration for Atlantic Store
// https://docs.tebex.io/developers/headless-api

const TEBEX_CONFIG = {
    // Substitui com teu Webstore Identifier do Tebex
    webstoreId: 'atlantic-anarchy',
    
    // URLs da API Tebex
    apiBase: 'https://plugin.tebex.io/public',
    checkoutBase: 'https://checkout.tebex.io/web',
    
    // Mapeamento de IDs locais para IDs do Tebex
    // Quando criares os packages no Tebex, atualiza estes IDs
    packageMapping: {
        // Ranks
        'vip': null,         // ID do package VIP no Tebex
        'mvp': null,         // ID do package MVP no Tebex
        'marshal': null,     // ID do package MARSHAL no Tebex
        'mythic': null,      // ID do package MYTHIC no Tebex
        'celestial': null,   // ID do package CELESTIAL no Tebex
        'straindez': null,   // ID do package STRAINDEZ no Tebex
        'custom': null,      // ID do package CUSTOM no Tebex
        
        // Keys
        'time-key': null,
        'vanta-key': null,
        'aventus-key': null,
        
        // Rubis
        'rubis-500': null,
        'rubis-1200': null,
        'rubis-2500': null,
        'rubis-6000': null,
        'rubis-13000': null,
        
        // Battle Pass
        'battlepass-basic': null,
        'battlepass-boost': null,
        'battlepass-complete': null
    }
};

// Estado do checkout Tebex
let tebexCheckout = {
    basketId: null,
    items: []
};

/**
 * Inicializa a integração Tebex
 */
async function initTebex() {
    console.log('Initializing Tebex integration...');
    
    // Verificar se temos webstore ID configurado
    if (TEBEX_CONFIG.webstoreId === 'atlantic-anarchy') {
        console.warn('⚠️ Tebex webstore ID not configured! Using demo mode.');
        return false;
    }
    
    return true;
}

/**
 * Cria um basket (carrinho) no Tebex
 */
async function createTebexBasket() {
    try {
        const response = await fetch(`${TEBEX_CONFIG.apiBase}/baskets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                complete_url: `${window.location.origin}/success.html`,
                cancel_url: `${window.location.origin}/cart.html`
            })
        });
        
        if (!response.ok) {
            throw new Error('Failed to create Tebex basket');
        }
        
        const data = await response.json();
        tebexCheckout.basketId = data.ident;
        
        console.log('Tebex basket created:', tebexCheckout.basketId);
        return tebexCheckout.basketId;
        
    } catch (error) {
        console.error('Error creating Tebex basket:', error);
        return null;
    }
}

/**
 * Adiciona um package ao basket do Tebex
 */
async function addToTebexBasket(packageId, quantity = 1) {
    if (!tebexCheckout.basketId) {
        await createTebexBasket();
    }
    
    try {
        const response = await fetch(
            `${TEBEX_CONFIG.apiBase}/baskets/${tebexCheckout.basketId}/packages`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    package_id: packageId,
                    quantity: quantity
                })
            }
        );
        
        if (!response.ok) {
            throw new Error('Failed to add package to Tebex basket');
        }
        
        const data = await response.json();
        console.log('Package added to Tebex basket:', data);
        return data;
        
    } catch (error) {
        console.error('Error adding to Tebex basket:', error);
        return null;
    }
}

/**
 * Sincroniza carrinho local com Tebex
 */
async function syncCartWithTebex() {
    const localCart = getCart();
    
    if (localCart.length === 0) {
        console.log('Cart is empty, nothing to sync');
        return;
    }
    
    // Criar novo basket
    const basketId = await createTebexBasket();
    
    if (!basketId) {
        console.error('Failed to create Tebex basket');
        return null;
    }
    
    // Adicionar cada item ao basket
    for (const item of localCart) {
        const tebexPackageId = TEBEX_CONFIG.packageMapping[item.id];
        
        if (!tebexPackageId) {
            console.warn(`No Tebex package ID mapped for ${item.id}`);
            continue;
        }
        
        await addToTebexBasket(tebexPackageId, item.quantity);
    }
    
    return basketId;
}

/**
 * Abre o checkout do Tebex
 */
async function openTebexCheckout(username) {
    console.log('Opening Tebex checkout for:', username);
    
    // Sincronizar carrinho
    const basketId = await syncCartWithTebex();
    
    if (!basketId) {
        alert('Failed to create checkout. Please try again.');
        return;
    }
    
    // URL do checkout Tebex
    const checkoutUrl = `${TEBEX_CONFIG.checkoutBase}/${basketId}?username=${encodeURIComponent(username)}`;
    
    // Abrir em nova aba
    window.open(checkoutUrl, '_blank');
    
    // Opcional: Limpar carrinho local após abrir checkout
    // localStorage.removeItem('atlantic_cart');
    // updateCartDisplay();
}

/**
 * Método alternativo: Redirecionar direto para Tebex Webstore
 */
function redirectToTebexWebstore() {
    const webstoreUrl = `https://${TEBEX_CONFIG.webstoreId}.tebex.io`;
    window.open(webstoreUrl, '_blank');
}

/**
 * Obter informações de um package do Tebex
 */
async function getTebexPackageInfo(packageId) {
    try {
        const response = await fetch(
            `${TEBEX_CONFIG.apiBase}/packages/${packageId}`
        );
        
        if (!response.ok) {
            throw new Error('Failed to fetch package info');
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error fetching Tebex package:', error);
        return null;
    }
}

/**
 * Listar todas as categorias da webstore
 */
async function getTebexCategories() {
    try {
        const response = await fetch(
            `${TEBEX_CONFIG.apiBase}/categories`
        );
        
        if (!response.ok) {
            throw new Error('Failed to fetch categories');
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error fetching Tebex categories:', error);
        return null;
    }
}

/**
 * Obter todos os packages de uma categoria
 */
async function getTebexPackagesByCategory(categoryId) {
    try {
        const response = await fetch(
            `${TEBEX_CONFIG.apiBase}/categories/${categoryId}/packages`
        );
        
        if (!response.ok) {
            throw new Error('Failed to fetch packages');
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error fetching Tebex packages:', error);
        return null;
    }
}

// Export functions
window.TEBEX_CONFIG = TEBEX_CONFIG;
window.initTebex = initTebex;
window.createTebexBasket = createTebexBasket;
window.addToTebexBasket = addToTebexBasket;
window.syncCartWithTebex = syncCartWithTebex;
window.openTebexCheckout = openTebexCheckout;
window.redirectToTebexWebstore = redirectToTebexWebstore;
window.getTebexPackageInfo = getTebexPackageInfo;
window.getTebexCategories = getTebexCategories;
window.getTebexPackagesByCategory = getTebexPackagesByCategory;
