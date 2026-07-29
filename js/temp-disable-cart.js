// ===============================================
// TEMPORARY: Disable cart until Tebex is ready
// Add this script to all HTML pages temporarily
// Remove when Tebex is configured
// ===============================================

document.addEventListener('DOMContentLoaded', () => {
    // Replace all "Add to Cart" buttons behavior
    const addToCartButtons = document.querySelectorAll('[onclick*="addToCart"], .btn-add-cart, .rank-modal-add-cart');
    
    addToCartButtons.forEach(button => {
        button.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Show message to contact Discord
            showNotification('Store coming soon! Join our Discord to pre-order!', 'info');
            
            // Optional: Auto-open Discord after 1 second
            setTimeout(() => {
                window.open('https://discord.gg/DYspcWpUPv', '_blank');
            }, 1000);
        };
        
        // Change button text
        const buttonText = button.querySelector('.btn-text') || button;
        if (buttonText.textContent.includes('Cart')) {
            buttonText.innerHTML = '<i class="fa-brands fa-discord"></i> Pre-Order on Discord';
        }
    });
    
    // Update cart button in header
    const cartButtons = document.querySelectorAll('.basket a');
    cartButtons.forEach(button => {
        button.onclick = (e) => {
            e.preventDefault();
            showNotification('Store opening soon! Join Discord for updates.', 'info');
            setTimeout(() => {
                window.open('https://discord.gg/DYspcWpUPv', '_blank');
            }, 1000);
        };
    });
    
    console.log('🛒 Cart temporarily disabled - Tebex configuration pending');
});

// Show notification function (if not already defined in main.js)
if (typeof showNotification === 'undefined') {
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
}
