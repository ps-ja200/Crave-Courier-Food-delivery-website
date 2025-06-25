// Shopping Cart Implementation
class ShoppingCart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('cart')) || [];
        this.updateCartDisplay();
    }

    addItem(id, name, price, image) {
        const existingItem = this.items.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.push({
                id: id,
                name: name,
                price: price,
                image: image,
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
        localStorage.setItem('cart', JSON.stringify(this.items));
    }

    updateCartDisplay() {
        const cartCount = document.querySelector('.cart-count');
        const cartTotal = document.querySelector('.cart-total');
        
        if (cartCount) cartCount.textContent = this.getItemCount();
        if (cartTotal) cartTotal.textContent = `₹${this.getTotal()}`;
    }

    showNotification(message) {
        // Create notification popup
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize cart
const cart = new ShoppingCart();

// Add event listeners to all "Add to Cart" buttons
document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            
            cart.addItem(id, name, price, image);
        });
    });
});
