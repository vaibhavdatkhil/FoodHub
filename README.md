# 🍽️ FoodHub — Food Ordering + Food Donation Platform

A full-stack PHP/MySQL web application that combines food ordering (like Zomato/Swiggy) with a food donation feature that connects donors with NGOs.

---

## 📁 Project Structure

```
foodhub/
├── index.php                  ← Homepage (browse restaurants, search, hero)
├── restaurants.php            ← All restaurants listing + filter
├── restaurant_menu.php        ← Menu page with cart, coupon, checkout
├── database.sql               ← Full database schema + sample data
│
├── auth/
│   ├── login.php              ← 3-tab login (Customer / Restaurant / NGO)
│   ├── register.php           ← 3-tab registration with OTP trigger
│   ├── verify_otp.php         ← 6-digit OTP verification
│   ├── forgot_password.php    ← Request password reset OTP
│   ├── reset_password.php     ← Enter OTP + new password
│   └── auth_handler.php       ← AJAX auth backend
│
├── customer/
│   ├── dashboard.php          ← Account overview, stats, recent orders
│   ├── orders.php             ← All orders + live tracking panel
│   └── checkout.php           ← Delivery address + payment selection
│
├── restaurant/
│   └── dashboard.php          ← Orders management, menu CRUD, history
│
├── ngo/
│   └── dashboard.php          ← Donation requests, accept/pickup/complete flow
│
├── donate/
│   ├── donate_food.php        ← Food donation form (donor details, NGO select)
│   └── donate_handler.php     ← Donation form AJAX backend
│
├── api/
│   ├── order_handler.php      ← Place order, cancel order
│   ├── cart_handler.php       ← Apply coupon, get menu item
│   ├── restaurant_handler.php ← Update order status, CRUD menu items
│   └── ngo_handler.php        ← Update donation status
│
└── includes/
    ├── config.php             ← DB connection, session, helpers
    └── email.php              ← OTP email, donation confirmation
```

---

## ⚙️ Setup Instructions

### 1. Requirements
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+
- Apache / Nginx with mod_rewrite
- SMTP mail server (or Gmail SMTP)

### 2. Database Setup
```sql
CREATE DATABASE foodhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Then import the schema:
```bash
mysql -u root -p foodhub_db < database.sql
```

### 3. Configuration
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'foodhub_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your@gmail.com');
define('SMTP_PASS', 'your_app_password');
define('SMTP_PORT', 587);
```

### 4. Web Server
Place the `foodhub/` folder in your web root:
- **XAMPP/WAMP**: `htdocs/foodhub/`
- **LAMP**: `/var/www/html/foodhub/`

Then visit: `http://localhost/foodhub/`

---

## 👤 Demo Accounts (after importing database.sql)

> **Note:** The demo data uses placeholder password hashes. Register new accounts using the registration pages to get working logins.

### To Test:
1. Go to `/auth/register.php`
2. Register as Customer, Restaurant Owner, or NGO
3. Verify your email with the OTP sent
4. Log in and explore

### Sample Coupon Codes:
| Code | Type | Value | Min Order |
|------|------|-------|-----------|
| `WELCOME50` | Flat discount | ₹50 off | ₹200 |
| `FEAST20` | Percentage | 20% off | ₹300 |
| `DONATE10` | Flat discount | ₹10 off | ₹100 |

---

## 🔑 Key Features

### 🛒 Food Ordering
- Browse restaurants with search, city, and cuisine filters
- Full menu with veg/non-veg toggle and item search
- Add to cart with quantity controls (sessionStorage-based)
- Coupon code application with real-time discount
- Checkout with delivery address and multiple payment options (COD, UPI, Card, Wallet)
- Order tracking with 5-step progress bar

### 💚 Food Donation
- Donor form: name, contact, food type, quantity, pickup address, date/time
- Select specific NGO or auto-assign
- Confirmation email to donor + notification to NGO
- NGO dashboard to accept → pick up → complete donations

### 👥 Three User Types
| Type | Registration | Dashboard | Features |
|------|-------------|-----------|---------|
| Customer | Email + OTP | Orders, history, profile | Browse, order, track, donate |
| Restaurant | Email + OTP + Admin approval | Order management, menu CRUD | Accept orders, update status, manage menu |
| NGO | Email + OTP + Admin approval | Donation management | Accept/track/complete food donations |

### 🔐 Authentication
- OTP email verification at registration (6-digit, 10 min expiry)
- Session-based login with remember-me cookie (30 days)
- Forgot password via OTP reset flow
- Password hashing with bcrypt

---

## 🎨 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4+ (procedural + PDO) |
| Database | MySQL with normalized schema |
| Frontend | Bootstrap 5.3, jQuery 3.7 |
| AJAX | jQuery $.ajax / $.post / $.get |
| Email | PHP mail() / SMTP |
| Fonts | Playfair Display + DM Sans (Google Fonts) |
| Icons | Font Awesome 6.4 |

---

## 🗄️ Database Schema (10 tables)

| Table | Purpose |
|-------|---------|
| `customers` | Customer accounts with OTP verification |
| `restaurants` | Restaurant accounts (require admin approval) |
| `ngos` | NGO accounts (require admin approval) |
| `menu_categories` | Restaurant menu sections |
| `menu_items` | Individual dishes with veg/price/prep time |
| `orders` | Customer orders with status tracking |
| `order_items` | Individual items per order |
| `food_donations` | Donation requests linked to NGOs |
| `reviews` | Customer reviews (schema ready) |
| `coupons` | Discount codes (flat/percent) |

---

## 🚀 Future Enhancements
- [ ] Admin panel (approve restaurants/NGOs, view analytics)
- [ ] Real-time order tracking with WebSockets or polling
- [ ] Push notifications for order status updates
- [ ] Razorpay / Stripe payment gateway integration
- [ ] Google Maps integration for delivery address
- [ ] Customer review & rating system
- [ ] Restaurant analytics dashboard with charts
- [ ] Mobile app (React Native / Flutter)
