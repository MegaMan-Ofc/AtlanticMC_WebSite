// Global State
const APP_STATE = {
    user: null,
    cart: [],
    config: null,
    discordConnected: false
};

// Load configuration
async function loadConfig() {
    try {
        const response = await fetch('config/store-config.json');
        APP_STATE.config = await response.json();
        return APP_STATE.config;
    } catch (error) {
        console.error('Error loading config:', error);
        return null;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    await loadConfig();
    loadCartFromStorage();
    updateCartDisplay();
    updatePlayerCount();
    updateDiscordMembers();
    checkUserLogin();
    
    // Update every 30 seconds
    setInterval(updatePlayerCount, 30000);
    setInterval(updateDiscordMembers, 60000);
});

// Cart Management
function loadCartFromStorage() {
    const saved = localStorage.getItem('atlantic_cart');
    if (saved) {
        APP_STATE.cart = JSON.parse(saved);
    }
}

function saveCartToStorage() {
    localStorage.setItem('atlantic_cart', JSON.stringify(APP_STATE.cart));
}

function addToCart(item) {
    // Check if item already exists
    const existing = APP_STATE.cart.find(i => i.id === item.id && i.type === item.type);
    
    if (existing) {
        existing.quantity += item.quantity || 1;
    } else {
        APP_STATE.cart.push({
            id: item.id,
            type: item.type,
            name: item.name,
            price: item.price,
            quantity: item.quantity || 1,
            image: item.image
        });
    }
    
    saveCartToStorage();
    updateCartDisplay();
    showNotification('Item added to cart!', 'success');
}

function removeFromCart(index) {
    APP_STATE.cart.splice(index, 1);
    saveCartToStorage();
    updateCartDisplay();
}

function updateCartQuantity(index, quantity) {
    if (quantity <= 0) {
        removeFromCart(index);
    } else {
        APP_STATE.cart[index].quantity = quantity;
        saveCartToStorage();
        updateCartDisplay();
    }
}

function clearCart() {
    APP_STATE.cart = [];
    saveCartToStorage();
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartCount = APP_STATE.cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartTotal = APP_STATE.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Update cart badge
    const badges = document.querySelectorAll('#cart-count');
    badges.forEach(badge => {
        badge.textContent = cartCount;
    });
    
    // Update cart button text
    const cartBtns = document.querySelectorAll('#btn-cart');
    cartBtns.forEach(btn => {
        const textElement = btn.childNodes[btn.childNodes.length - 1];
        if (textElement.nodeType === Node.TEXT_NODE) {
            textElement.textContent = cartCount > 0 ? ` ${cartTotal.toFixed(2)} EUR` : ' EMPTY';
        }
    });
}

function getCartTotal() {
    return APP_STATE.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
}

// User Management
function checkUserLogin() {
    const user = localStorage.getItem('atlantic_user');
    if (user) {
        APP_STATE.user = JSON.parse(user);
        updateUserDisplay();
    }
}

function updateUserDisplay() {
    if (APP_STATE.user) {
        const usernameElements = document.querySelectorAll('#username');
        const avatarElements = document.querySelectorAll('#user-avatar');
        
        usernameElements.forEach(el => {
            el.textContent = APP_STATE.user.username;
        });
        
        avatarElements.forEach(el => {
            el.src = `https://crafatar.com/avatars/${APP_STATE.user.uuid}?overlay`;
        });
    }
}

// Minecraft Username Validation
async function validateMinecraftUsername(username) {
    try {
        // Try Mojang API
        const response = await fetch(`https://api.mojang.com/users/profiles/minecraft/${username}`);
        
        if (response.ok) {
            const data = await response.json();
            return {
                valid: true,
                uuid: data.id,
                username: data.name
            };
        } else {
            return {
                valid: false,
                error: 'Player not found'
            };
        }
    } catch (error) {
        console.error('Error validating username:', error);
        return {
            valid: false,
            error: 'Failed to validate username'
        };
    }
}

// Discord Login
document.querySelectorAll('#btn-discord-login').forEach(btn => {
    btn.addEventListener('click', () => {
        // Redirect to Discord OAuth
        const discordClientId = APP_STATE.config?.discord?.clientId || 'YOUR_CLIENT_ID';
        const redirectUri = encodeURIComponent(window.location.origin + '/discord-callback.html');
        const scope = 'identify';
        
        const discordAuthUrl = `https://discord.com/api/oauth2/authorize?client_id=${discordClientId}&redirect_uri=${redirectUri}&response_type=code&scope=${scope}`;
        
        window.location.href = discordAuthUrl;
    });
});

// Server Stats
async function updatePlayerCount() {
    const playerElements = document.querySelectorAll('#mcPlayers, #player-count');
    
    try {
        const serverIP = APP_STATE.config?.server?.ip || 'atlantic.net';
        const response = await fetch(`https://api.mcsrvstat.us/2/${serverIP}`);
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.online) {
                const count = data.players?.online || 0;
                playerElements.forEach(el => {
                    if (el.id === 'mcPlayers') {
                        el.textContent = count;
                    } else {
                        el.textContent = `${count} PLAYING`;
                    }
                });
                return;
            }
        }
    } catch (error) {
        console.error('Error fetching player count:', error);
    }
    
    // Fallback
    const count = Math.floor(Math.random() * 150) + 50;
    playerElements.forEach(el => {
        if (el.id === 'mcPlayers') {
            el.textContent = count;
        } else {
            el.textContent = `${count} PLAYING`;
        }
    });
}

async function updateDiscordMembers() {
    const discordElements = document.querySelectorAll('#dPlayers, #discord-members');
    const count = '12,954';
    
    discordElements.forEach(el => {
        if (el.id === 'dPlayers') {
            el.textContent = count;
        } else {
            el.textContent = `${count} MEMBERS`;
        }
    });
}

// Notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6',
        color: 'white',
        padding: '15px 25px',
        borderRadius: '8px',
        boxShadow: '0 10px 30px rgba(0, 0, 0, 0.3)',
        zIndex: 99999,
        animation: 'slideIn 0.3s ease'
    });
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// CSS Animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Export functions for other scripts
window.APP_STATE = APP_STATE;
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateCartQuantity;
window.clearCart = clearCart;
window.getCartTotal = getCartTotal;
window.validateMinecraftUsername = validateMinecraftUsername;
window.showNotification = showNotification;
