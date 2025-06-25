# 🍔 Crave Courier - Food Delivery Website

A full-stack food delivery web application built with PHP, MySQL, JavaScript, HTML, and CSS. This project demonstrates user authentication, shopping cart, order management, and a modern, responsive UI.

---

## 🚀 Features

- User registration, login, and session management
- Secure password hashing and validation
- Shopping cart (add/remove, quantity, local storage)
- Menu display with categories and images
- Order placement and order tracking
- Contact form
- Admin/test user support
- Responsive, mobile-friendly design
- MySQL database with sample data
- Security best practices (prepared statements, input validation)
- **Password reset via email OTP (configure your SMTP email in `php/forgot_password.php`)**

---

## 🛠️ Setup Instructions

### 1. **Requirements**
- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP + MySQL stack)
- Web browser (Chrome, Firefox, Edge, etc.)

### 2. **Clone or Download the Project**
- Place the project folder (`Food-delivery-website`) inside your XAMPP `htdocs` directory:
  - Example: `C:/xampp/htdocs/Food-delivery-website`

### 3. **Start XAMPP Services**
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### 4. **Import the Database**
- Go to [phpMyAdmin](http://localhost/phpmyadmin)
- Create a new database: `crave_courier`
- Import the SQL file: `database/schema.sql`

### 5. **Configure Database Connection (if needed)**
- Open `php/db_connect.php`
- Update `$servername`, `$username`, `$password`, `$dbname` if your MySQL settings are different

### 6. **Configure Email for Password Reset**
- Open `php/forgot_password.php`
- Set your SMTP email and password in these lines:
  - `$mail->Username = 'your_email@example.com';`
  - `$mail->Password = 'your_email_password';`
  - `$mail->setFrom('your_email@example.com', 'Crave Courier');`
- This is required for the forgot password/OTP feature to work.

### 7. **Access the Website**
- Homepage: [http://localhost/Food-delivery-website/html/index.html](http://localhost/Food-delivery-website/html/index.html)
- Login: [http://localhost/Food-delivery-website/html/login.html](http://localhost/Food-delivery-website/html/login.html)
- Menu: [http://localhost/Food-delivery-website/html/menu.html](http://localhost/Food-delivery-website/html/menu.html)

---

## 👤 Test User Credentials
- **Username:** `testuser`
- **Password:** `password123`
- **Email:** `test@example.com`

---

## 📂 Project Structure

```
Food-delivery-website/
├── css/           # Stylesheets
├── database/      # SQL schema
├── html/          # Web pages
├── images/        # Food images
├── js/            # JavaScript (cart, UI)
├── php/           # Backend (API, auth)
├── composer.json  # PHP dependencies (if any)
└── .gitignore     # Git ignore rules
```

---

## ✨ Key Pages
- `index.html` - Homepage
- `menu.html` - Menu and shopping cart
- `login.html` - User login
- `signup.html` - User registration
- `checkout.html` - Order checkout
- `dashboard.html` - User dashboard
- `track-order.html` - Order tracking
- `contact.html` - Contact form

---

## 🔒 Security Highlights
- Passwords hashed with PHP `password_hash()`
- SQL injection protection (prepared statements)
- Input validation and sanitization
- Session management for authentication

---

## 📱 Mobile Friendly
- Responsive design for all devices
- Modern CSS and layout

---

## 📝 Customization
- Add menu items via phpMyAdmin (`menu_items` table)
- Add images to `images/` folder
- Update styles in `css/styles.css`
- Extend backend in `php/`

---

## 🛠️ Troubleshooting
- **Database connection failed:**
  - Make sure MySQL is running
  - Check credentials in `php/db_connect.php`
  - Import `database/schema.sql` if tables are missing
- **Cart not working:**
  - Ensure `js/cart.js` and `js/script.js` are loaded
  - Check browser console for errors
- **Login not working:**
  - Use the test credentials above
  - Check `users` table in database

---

## 📄 License
This project is for educational and portfolio use. Feel free to modify and use for your own learning or demonstration purposes.

---

**Enjoy your food delivery website!** 🍕🍔🥗
