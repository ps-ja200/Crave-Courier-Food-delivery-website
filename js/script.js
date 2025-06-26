// Shopping Cart Implementation
class ShoppingCart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('crave_courier_cart')) || [];
        this.initializeCart();
    }

    initializeCart() {
        this.updateCartDisplay();
        this.addEventListeners();
        this.createCartElements();
    }

    createCartElements() {
        // Create or remove cart icon in header based on cart contents
        const nav = document.querySelector('nav ul');
        const existingCartLi = document.querySelector('li .cart-icon')?.parentElement;
        if (this.getItemCount() > 0) {
            if (!existingCartLi && nav) {
                const cartLi = document.createElement('li');
                cartLi.innerHTML = `
                    <a href="#" class="btn cart-icon" onclick="cart.toggleCart()">
                        🛒 Cart (<span class="cart-count">${this.getItemCount()}</span>)
                    </a>
                `;
                nav.appendChild(cartLi);
            } else if (existingCartLi) {
                // Update cart count if already exists
                const cartCount = existingCartLi.querySelector('.cart-count');
                if (cartCount) cartCount.textContent = this.getItemCount();
            }
        } else if (existingCartLi) {
            existingCartLi.remove();
        }

        // Create cart sidebar if it doesn't exist
        if (!document.querySelector('.cart-sidebar')) {
            const cartSidebar = document.createElement('div');
            cartSidebar.className = 'cart-sidebar';
            cartSidebar.innerHTML = `
                <div class="cart-header">
                    <h3>🛒 Your Cart</h3>
                    <button class="close-cart" onclick="cart.toggleCart()">&times;</button>
                </div>
                <div class="cart-items" id="cartItems">
                    <!-- Cart items will appear here -->
                </div>
                <div class="cart-footer">
                    <div class="cart-total">
                        <strong>Total: ₹<span id="cartTotal">0</span></strong>
                    </div>
                    <button class="checkout-btn" onclick="cart.proceedToCheckout()">
                        Proceed to Checkout
                    </button>
                </div>
            `;
            document.body.appendChild(cartSidebar);
        }
    }

    addEventListeners() {
        // Add click handlers to all "Add to Cart" buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                e.preventDefault();
                const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                this.addFromButton(button);
            }
        });
    }

    addFromButton(button) {
        // Extract item data from the menu item
        const menuItem = button.closest('.menu-item') || button.closest('.featured-dish');
        
        if (!menuItem) return;

        const name = menuItem.querySelector('h3, h4')?.textContent?.trim();
        const priceText = menuItem.querySelector('.price')?.textContent?.trim();
        const price = parseFloat(priceText?.replace(/[₹,]/g, '')) || 0;
        const image = menuItem.querySelector('img')?.src || '';
        const description = menuItem.querySelector('p')?.textContent?.trim() || '';
        
        // Generate ID from name
        const id = name?.toLowerCase().replace(/\s+/g, '_') || Math.random().toString(36).substr(2, 9);

        if (name && price > 0) {
            this.addItem(id, name, price, image, description);
        }
    }

    addItem(id, name, price, image, description = '') {
        const existingItem = this.items.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.push({
                id: id,
                name: name,
                price: price,
                image: image,
                description: description,
                quantity: 1
            });
        }
        
        this.saveCart();
        this.updateCartDisplay();
        this.showNotification(`${name} added to cart!`);
    }

    removeItem(id) {
        this.items = this.items.filter(item => item.id !== id);
        this.saveCart();
        this.updateCartDisplay();
    }

    updateQuantity(id, newQuantity) {
        const item = this.items.find(item => item.id === id);
        if (item) {
            if (newQuantity <= 0) {
                this.removeItem(id);
            } else {
                item.quantity = newQuantity;
                this.saveCart();
                this.updateCartDisplay();
            }
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    getItemCount() {
        return this.items.reduce((count, item) => count + item.quantity, 0);
    }

    saveCart() {
        localStorage.setItem('crave_courier_cart', JSON.stringify(this.items));
    }

    updateCartDisplay() {
        // Update cart count
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = this.getItemCount();
        }

        // Update cart total
        const cartTotal = document.getElementById('cartTotal');
        if (cartTotal) {
            cartTotal.textContent = this.getTotal().toFixed(2);
        }

        // Update cart items list
        this.updateCartItemsList();
    }

    updateCartItemsList() {
        const cartItemsContainer = document.getElementById('cartItems');
        if (!cartItemsContainer) return;

        if (this.items.length === 0) {
            cartItemsContainer.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
            return;
        }

        cartItemsContainer.innerHTML = this.items.map(item => `
            <div class="cart-item" data-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    <p class="cart-item-price">₹${item.price}</p>
                </div>
                <div class="cart-item-controls">
                    <button onclick="cart.updateQuantity('${item.id}', ${item.quantity - 1})" class="qty-btn">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button onclick="cart.updateQuantity('${item.id}', ${item.quantity + 1})" class="qty-btn">+</button>
                    <button onclick="cart.removeItem('${item.id}')" class="remove-btn">🗑️</button>
                </div>
                <div class="cart-item-total">₹${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `).join('');
    }

    toggleCart() {
        const cartSidebar = document.querySelector('.cart-sidebar');
        if (cartSidebar) {
            cartSidebar.classList.toggle('open');
        }
    }

    proceedToCheckout() {
        if (this.items.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        
        // For now, redirect to checkout page (we'll create this later)
        // You can customize this based on your needs
        alert(`Proceeding to checkout with ${this.getItemCount()} items totaling ₹${this.getTotal().toFixed(2)}`);
        
        // In the future, redirect to checkout:
        // window.location.href = 'checkout.html';
    }

    showNotification(message) {
        // Remove existing notification
        const existingNotification = document.querySelector('.cart-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create new notification
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize cart when page loads
const cart = new ShoppingCart();

// Navigation active page highlighting
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        if (link.getAttribute('href').includes(currentPage)) {
            link.classList.add('active');
        }
    });
});

// Legacy login modal code (keeping for compatibility)
if (document.getElementById('login-modal')) {
    var loginModal = document.getElementById('login-modal');
    var closeBtn = document.getElementById('close-btn');

    if (document.getElementById('login-btn')) {
        document.getElementById('login-btn').addEventListener('click', function () {
            loginModal.style.display = 'block';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            loginModal.style.display = 'none';
        });
    }
        
    if (document.getElementById('login-btn')) {
        document.getElementById('login-btn').addEventListener('click', function () {
            window.location.href = 'login.html';
        });
    }
}