// Checkout Page JavaScript - SIMPLIFIED FOR TEBEX
let checkoutData = {
    cart: [],
    subtotal: 0,
    tax: 0,
    total: 0
};

document.addEventListener('DOMContentLoaded', () => {
    initCheckout();
});

function initCheckout() {
    // Load cart
    checkoutData.cart = getCart();
    
    if (checkoutData.cart.length === 0) {
        alert('Your cart is empty!');
        window.location.href = 'index.html';
        return;
    }
    
    // Calculate totals
    calculateTotals();
    
    // Load order summary
    loadOrderSummary();
    
    // Load cart preview
    loadCartPreview();
}

function calculateTotals() {
    checkoutData.subtotal = checkoutData.cart.reduce((sum, item) => {
        return sum + (item.price * item.quantity);
    }, 0);
    
    // IVA 23% (Portugal)
    checkoutData.tax = checkoutData.subtotal * 0.23;
    checkoutData.total = checkoutData.subtotal + checkoutData.tax;
}

function loadOrderSummary() {
    const itemsContainer = document.getElementById('summary-items');
    itemsContainer.innerHTML = '';
    
    checkoutData.cart.forEach(item => {
        const itemEl = document.createElement('div');
        itemEl.className = 'summary-item';
        itemEl.innerHTML = `
            <div class="summary-item-info">
                <img src="${item.image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/50x50/6C5CE7/FFFFFF?text=${item.type.toUpperCase()}'">
                <div>
                    <div class="summary-item-name">${item.name}</div>
                    <div class="summary-item-qty">Qty: ${item.quantity}</div>
                </div>
            </div>
            <div class="summary-item-price">€${(item.price * item.quantity).toFixed(2)}</div>
        `;
        itemsContainer.appendChild(itemEl);
    });
    
    // Update totals
    document.getElementById('summary-subtotal').textContent = `€${checkoutData.subtotal.toFixed(2)}`;
    document.getElementById('summary-tax').textContent = `€${checkoutData.tax.toFixed(2)}`;
    document.getElementById('summary-total').textContent = `€${checkoutData.total.toFixed(2)}`;
}

function loadCartPreview() {
    const preview = document.getElementById('cart-preview');
    if (!preview) return;
    
    preview.innerHTML = '<div class="cart-preview-items">';
    
    checkoutData.cart.forEach(item => {
        preview.innerHTML += `
            <div class="cart-preview-item">
                <span>${item.quantity}x ${item.name}</span>
                <span>€${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `;
    });
    
    preview.innerHTML += `</div>
        <div class="cart-preview-total">
            <strong>Total:</strong>
            <strong>€${checkoutData.total.toFixed(2)}</strong>
        </div>`;
}

// Redirect to Tebex Store
function goToTebexStore() {
    // IMPORTANTE: Substitui 'atlantic-anarchy' com o teu webstore ID do Tebex
    const tebexWebstore = 'atlantic-anarchy.tebex.io';
    
    // Abre Tebex em nova aba
    window.open(`https://${tebexWebstore}`, '_blank');
    
    // Opcional: Mostra mensagem
    alert('A abrir loja Tebex em nova aba!\n\nProcura pelos items que adicionaste ao carrinho e completa o pagamento lá.');
}

// Helper functions
function getCart() {
    const cart = localStorage.getItem('atlantic_cart');
    return cart ? JSON.parse(cart) : [];
}

// Export functions
window.checkoutData = checkoutData;
window.goToTebexStore = goToTebexStore;
