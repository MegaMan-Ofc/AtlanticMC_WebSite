// Cart Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    setupCartEvents();
});

function loadCart() {
    const cart = getCart();
    const container = document.getElementById('cart-items-list');
    const emptyMessage = document.getElementById('empty-cart-message');
    const cartTable = document.getElementById('cart-table');
    const couponSection = document.getElementById('cart-coupon-section');
    const footerSection = document.getElementById('cart-footer-section');
    
    if (cart.length === 0) {
        // Show empty cart message
        emptyMessage.style.display = 'flex';
        cartTable.style.display = 'none';
        couponSection.style.display = 'none';
        footerSection.style.display = 'none';
        updateCartTotal(0);
        return;
    }
    
    // Hide empty message, show cart
    emptyMessage.style.display = 'none';
    cartTable.style.display = 'table';
    couponSection.style.display = 'block';
    footerSection.style.display = 'flex';
    
    // Clear existing items
    container.innerHTML = '';
    
    // Add each cart item
    cart.forEach(item => {
        const row = createCartRow(item);
        container.appendChild(row);
    });
    
    // Update total
    updateCartTotal();
}

function createCartRow(item) {
    const row = document.createElement('tr');
    row.className = 'cart-item-row';
    row.setAttribute('data-item-id', item.id);
    
    row.innerHTML = `
        <td class="cart-item-image">
            <img src="${item.image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/80x80/6C5CE7/FFFFFF?text=${item.type.toUpperCase()}'">
        </td>
        <td class="cart-item-name">
            <div class="item-name">${item.name}</div>
            <div class="item-type">${item.type.toUpperCase()}</div>
        </td>
        <td class="cart-item-price">
            <span class="price-amount">${item.price.toFixed(2)}</span>
            <small>EUR</small>
        </td>
        <td class="cart-item-quantity">
            <div class="quantity-controls">
                <button class="qty-btn qty-minus" data-id="${item.id}">
                    <i class="fa-solid fa-minus"></i>
                </button>
                <input type="number" class="qty-input" value="${item.quantity}" min="1" max="99" data-id="${item.id}">
                <button class="qty-btn qty-plus" data-id="${item.id}">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </td>
        <td class="cart-item-actions">
            <button class="btn-icon btn-remove" data-id="${item.id}" title="Remove item">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function setupCartEvents() {
    // Remove item buttons
    document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove')) {
            const id = e.target.closest('.btn-remove').getAttribute('data-id');
            removeFromCart(id);
            loadCart();
        }
    });
    
    // Quantity minus buttons
    document.addEventListener('click', (e) => {
        if (e.target.closest('.qty-minus')) {
            const id = e.target.closest('.qty-minus').getAttribute('data-id');
            updateQuantity(id, -1);
        }
    });
    
    // Quantity plus buttons
    document.addEventListener('click', (e) => {
        if (e.target.closest('.qty-plus')) {
            const id = e.target.closest('.qty-plus').getAttribute('data-id');
            updateQuantity(id, 1);
        }
    });
    
    // Quantity input change
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('qty-input')) {
            const id = e.target.getAttribute('data-id');
            const newQty = parseInt(e.target.value);
            if (newQty > 0 && newQty <= 99) {
                setQuantity(id, newQty);
            } else {
                e.target.value = 1;
                setQuantity(id, 1);
            }
        }
    });
    
    // Purchase button
    document.getElementById('purchase-btn')?.addEventListener('click', () => {
        proceedToCheckout();
    });
    
    // Coupon redeem
    document.getElementById('redeem-coupon-btn')?.addEventListener('click', () => {
        redeemCoupon();
    });
}

function updateQuantity(id, change) {
    const cart = getCart();
    const item = cart.find(i => i.id === id);
    
    if (item) {
        item.quantity = Math.max(1, Math.min(99, item.quantity + change));
        saveCart(cart);
        loadCart();
    }
}

function setQuantity(id, qty) {
    const cart = getCart();
    const item = cart.find(i => i.id === id);
    
    if (item) {
        item.quantity = qty;
        saveCart(cart);
        loadCart();
    }
}

function removeFromCart(id) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== id);
    saveCart(cart);
    updateCartCount();
}

function updateCartTotal(customTotal = null) {
    const totalElement = document.getElementById('cart-total-amount');
    
    if (customTotal !== null) {
        totalElement.textContent = customTotal.toFixed(2);
        return;
    }
    
    const cart = getCart();
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    totalElement.textContent = total.toFixed(2);
}

function proceedToCheckout() {
    const cart = getCart();
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Redirect to checkout page
    window.location.href = 'checkout.html';
}

function redeemCoupon() {
    const input = document.getElementById('coupon-input');
    const message = document.getElementById('coupon-message');
    const code = input.value.trim();
    
    if (!code) {
        showCouponMessage('Please enter a coupon code', 'error');
        return;
    }
    
    // Here you would validate the coupon with your backend
    // For demo purposes:
    
    const validCoupons = {
        'ATLANTIC': 10,    // 10% off
        'ATLANTIC10': 10,  // 10% off
        'WELCOME20': 20,   // 20% off
        'VIP50': 50        // 50% off
    };
    
    if (validCoupons[code.toUpperCase()]) {
        const discount = validCoupons[code.toUpperCase()];
        showCouponMessage(`Coupon applied! ${discount}% discount`, 'success');
        applyCouponDiscount(discount);
    } else {
        showCouponMessage('Invalid coupon code', 'error');
    }
}

function showCouponMessage(text, type) {
    const message = document.getElementById('coupon-message');
    message.textContent = text;
    message.className = `coupon-message coupon-${type}`;
    message.style.display = 'block';
    
    setTimeout(() => {
        message.style.display = 'none';
    }, 5000);
}

function applyCouponDiscount(percent) {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = subtotal * (percent / 100);
    const total = subtotal - discount;
    
    // Update display with discount
    const totalElement = document.getElementById('cart-total-amount');
    totalElement.innerHTML = `
        <span style="text-decoration: line-through; opacity: 0.5; font-size: 0.8em;">${subtotal.toFixed(2)}</span>
        ${total.toFixed(2)}
    `;
}

// Helper functions from main.js
function getCart() {
    const cart = localStorage.getItem('atlantic_cart');
    return cart ? JSON.parse(cart) : [];
}

function saveCart(cart) {
    localStorage.setItem('atlantic_cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const cart = getCart();
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const countElement = document.getElementById('cart-count');
    if (countElement) {
        countElement.textContent = count;
    }
}

// Export functions
window.loadCart = loadCart;
