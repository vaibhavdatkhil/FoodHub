<?php
require_once 'includes/config.php';
$db = getDB();
$isLoggedIn = isLoggedIn('customer');
$customerName = $_SESSION['customer_name'] ?? '';

// Fetch restaurants
$restaurants = $db->query("SELECT * FROM restaurants WHERE is_approved=1 AND is_active=1 LIMIT 6")->fetchAll();

// Fetch popular items
$popularItems = $db->query("SELECT mi.*, r.restaurant_name FROM menu_items mi JOIN restaurants r ON mi.restaurant_id=r.id WHERE mi.is_bestseller=1 AND mi.is_available=1 LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodHub – Order Food & Share Love</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
<style>
:root {
  --primary: #FF6B35;
  --primary-dark: #e85a2a;
  --secondary: #F7931E;
  --accent: #27ae60;
  --dark: #1a1a2e;
  --dark2: #16213e;
  --light: #fff7f0;
  --card-shadow: 0 8px 32px rgba(0,0,0,0.08);
  --transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { font-family: 'DM Sans', sans-serif; color: var(--dark); overflow-x: hidden; background: #fafafa; }

/* ===== NAVBAR ===== */
.navbar-foodhub {
  background: rgba(26,26,46,0.97);
  backdrop-filter: blur(20px);
  padding: 16px 0;
  position: sticky; top: 0; z-index: 1000;
  box-shadow: 0 4px 24px rgba(0,0,0,0.15);
}
.navbar-brand-custom {
  font-family: 'Playfair Display', serif;
  font-size: 28px; font-weight: 800;
  color: #fff;
  text-decoration: none;
  display: flex; align-items: center; gap: 8px;
}
.navbar-brand-custom span { color: var(--primary); }
.nav-link-custom {
  color: rgba(255,255,255,0.85) !important;
  font-weight: 500; font-size: 15px;
  padding: 8px 16px !important;
  border-radius: 8px;
  transition: var(--transition);
  text-decoration: none;
}
.nav-link-custom:hover, .nav-link-custom.active { color: #fff !important; background: rgba(255,107,53,0.2); }
.btn-nav-login {
  background: transparent;
  border: 1.5px solid rgba(255,255,255,0.4);
  color: #fff; padding: 8px 20px;
  border-radius: 50px; font-weight: 600; font-size: 14px;
  transition: var(--transition); text-decoration: none;
}
.btn-nav-login:hover { background: #fff; color: var(--dark); border-color: #fff; }
.btn-nav-register {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none; color: #fff;
  padding: 8px 24px; border-radius: 50px;
  font-weight: 600; font-size: 14px;
  transition: var(--transition); text-decoration: none;
}
.btn-nav-register:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,107,53,0.4); color: #fff; }
.donate-nav-pill {
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: #fff; padding: 8px 20px;
  border-radius: 50px; font-weight: 600; font-size: 14px;
  text-decoration: none; transition: var(--transition);
  display: flex; align-items: center; gap: 6px;
}
.donate-nav-pill:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(39,174,96,0.4); color: #fff; }

/* ===== HERO ===== */
.hero-section {
  min-height: 92vh;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 100%);
  position: relative; overflow: hidden;
  display: flex; align-items: center;
}
.hero-section::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FF6B35' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.hero-blob {
  position: absolute; border-radius: 50%;
  background: radial-gradient(circle, rgba(255,107,53,0.15), transparent 70%);
  animation: float 6s ease-in-out infinite;
}
.hero-blob-1 { width: 600px; height: 600px; top: -200px; right: -100px; }
.hero-blob-2 { width: 400px; height: 400px; bottom: -150px; left: -100px; animation-delay: -3s; }
@keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(42px, 6vw, 72px);
  color: #fff; line-height: 1.1; font-weight: 800;
}
.hero-title .highlight { color: var(--primary); font-style: italic; }
.hero-subtitle { color: rgba(255,255,255,0.75); font-size: 18px; line-height: 1.7; max-width: 520px; }

.search-hero {
  background: #fff;
  border-radius: 20px;
  padding: 8px 8px 8px 24px;
  display: flex; align-items: center;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  gap: 12px; max-width: 600px;
  margin-top: 40px;
}
.search-hero input {
  flex: 1; border: none; outline: none;
  font-size: 16px; color: var(--dark);
  font-family: 'DM Sans', sans-serif;
}
.search-hero input::placeholder { color: #aaa; }
.btn-search-hero {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none; color: #fff;
  padding: 14px 32px; border-radius: 14px;
  font-weight: 700; font-size: 16px;
  cursor: pointer; transition: var(--transition);
  white-space: nowrap;
}
.btn-search-hero:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,107,53,0.4); }

.hero-stats { display: flex; gap: 40px; margin-top: 48px; }
.hero-stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 36px; font-weight: 800; color: #fff;
}
.hero-stat-label { color: rgba(255,255,255,0.6); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }

/* Hero food image area */
.hero-food-grid {
  position: relative; height: 520px;
}
.hero-food-card {
  position: absolute;
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(0,0,0,0.3);
  transition: var(--transition);
}
.hero-food-card:hover { transform: scale(1.03); }
.hero-food-card img { width: 100%; height: 100%; object-fit: cover; }
.fc-1 { width: 280px; height: 280px; top: 20px; right: 80px; animation: float 5s ease-in-out infinite; }
.fc-2 { width: 200px; height: 200px; top: 250px; right: 0; animation: float 7s ease-in-out infinite; }
.fc-3 { width: 180px; height: 180px; top: 10px; right: 320px; animation: float 6s ease-in-out infinite 1s; }
.food-emoji-big { font-size: 120px; display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #fff7f0, #fff0e8); }

/* ===== DONATE BANNER ===== */
.donate-banner {
  background: linear-gradient(135deg, #1a3a1a 0%, #0d4d0d 100%);
  position: relative; overflow: hidden; padding: 60px 0;
}
.donate-banner::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2327ae60' fill-opacity='0.07'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5 4.5-10 10-10s10 4.5 10 10-4.5 10-10 10-10-4.5-10-10z'/%3E%3C/g%3E%3C/svg%3E");
}
.donate-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(39,174,96,0.2); border: 1px solid rgba(39,174,96,0.4);
  color: #4ade80; padding: 6px 16px; border-radius: 50px;
  font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
  margin-bottom: 20px;
}
.donate-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(28px, 4vw, 44px);
  color: #fff; font-weight: 800; margin-bottom: 16px;
}
.donate-title .green { color: #4ade80; font-style: italic; }
.btn-donate-big {
  display: inline-flex; align-items: center; gap: 12px;
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  color: #fff; padding: 18px 40px;
  border-radius: 50px; font-size: 18px; font-weight: 700;
  text-decoration: none; transition: var(--transition);
  box-shadow: 0 8px 32px rgba(39,174,96,0.35);
}
.btn-donate-big:hover { transform: translateY(-3px); box-shadow: 0 16px 48px rgba(39,174,96,0.45); color: #fff; }

.donate-steps { display: flex; gap: 32px; flex-wrap: wrap; margin-top: 48px; }
.donate-step {
  flex: 1; min-width: 200px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px; padding: 24px;
  color: #fff; text-align: center;
}
.donate-step-icon { font-size: 40px; margin-bottom: 12px; }
.donate-step h6 { font-weight: 700; margin-bottom: 8px; }
.donate-step p { font-size: 13px; color: rgba(255,255,255,0.6); margin: 0; }

/* ===== CATEGORY TABS ===== */
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(28px, 4vw, 40px); font-weight: 800;
}
.section-badge {
  display: inline-block; background: var(--light);
  color: var(--primary); padding: 4px 16px;
  border-radius: 50px; font-size: 13px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;
}
.category-pill {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 12px 24px; border-radius: 50px;
  border: 2px solid #e8e8e8; background: #fff;
  font-weight: 600; font-size: 15px; cursor: pointer;
  transition: var(--transition); white-space: nowrap;
  color: #555;
}
.category-pill:hover, .category-pill.active {
  background: var(--primary); border-color: var(--primary);
  color: #fff; transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(255,107,53,0.25);
}
.category-scroll { overflow-x: auto; padding-bottom: 8px; }
.category-scroll::-webkit-scrollbar { height: 0; }

/* ===== RESTAURANT CARDS ===== */
.restaurant-card {
  border-radius: 20px; overflow: hidden;
  background: #fff; border: none;
  box-shadow: var(--card-shadow);
  transition: var(--transition);
  cursor: pointer;
}
.restaurant-card:hover { transform: translateY(-6px); box-shadow: 0 20px 48px rgba(0,0,0,0.14); }
.restaurant-cover {
  height: 180px; position: relative; overflow: hidden;
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  display: flex; align-items: center; justify-content: center;
  font-size: 80px;
}
.restaurant-cover img { width: 100%; height: 100%; object-fit: cover; }
.restaurant-time-badge {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  color: #fff; padding: 4px 12px; border-radius: 50px;
  font-size: 12px; font-weight: 600;
}
.restaurant-offer-badge {
  position: absolute; top: 12px; right: 12px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: #fff; padding: 4px 12px; border-radius: 50px;
  font-size: 12px; font-weight: 700;
}
.restaurant-logo {
  width: 56px; height: 56px; border-radius: 14px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; box-shadow: 0 4px 16px rgba(255,107,53,0.25);
  margin-top: -28px; margin-left: 16px; position: relative; z-index: 1;
}
.restaurant-info { padding: 12px 16px 16px; }
.restaurant-name { font-weight: 700; font-size: 17px; color: var(--dark); margin-bottom: 4px; }
.restaurant-meta { font-size: 13px; color: #888; }
.rating-badge {
  display: inline-flex; align-items: center; gap: 4px;
  background: #e8f7ee; color: #1a7a40;
  padding: 3px 10px; border-radius: 50px;
  font-size: 13px; font-weight: 700;
}
.veg-icon { color: var(--accent); }
.nonveg-icon { color: #e74c3c; }

/* ===== FOOD ITEM CARDS ===== */
.food-card {
  border-radius: 16px; background: #fff;
  box-shadow: var(--card-shadow);
  transition: var(--transition); overflow: hidden;
  border: 1px solid #f0f0f0;
}
.food-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
.food-img { height: 160px; overflow: hidden; position: relative; }
.food-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
.food-card:hover .food-img img { transform: scale(1.06); }
.food-img-emoji { font-size: 60px; display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #fff7f0, #fff); }
.bestseller-tag {
  position: absolute; top: 10px; left: 10px;
  background: linear-gradient(135deg, #f39c12, #f1c40f);
  color: #fff; padding: 3px 10px; border-radius: 50px;
  font-size: 11px; font-weight: 700; text-transform: uppercase;
}
.food-body { padding: 14px; }
.food-name { font-weight: 700; font-size: 15px; color: var(--dark); margin-bottom: 4px; }
.food-desc { font-size: 12px; color: #888; margin-bottom: 10px; line-height: 1.5;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.food-price { font-weight: 800; font-size: 17px; color: var(--dark); }
.food-price span { font-size: 12px; font-weight: 500; color: #888; }
.btn-add-cart {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none; color: #fff;
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: var(--transition);
  font-size: 16px;
}
.btn-add-cart:hover { transform: scale(1.15); box-shadow: 0 6px 16px rgba(255,107,53,0.4); }

/* ===== HOW IT WORKS ===== */
.how-section { 
  background: linear-gradient(135deg, var(--dark) 0%, #16213e 100%); 
  position: relative; overflow: hidden;
  padding: 100px 0; 
}
.how-section::before {
  content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
  background: radial-gradient(circle, rgba(255,107,53,0.05) 0%, transparent 60%);
  animation: rotateBg 20s linear infinite;
}
@keyframes rotateBg { 100% { transform: rotate(360deg); } }

.step-card {
  text-align: center; padding: 40px 30px;
  background: rgba(255,255,255,0.03);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.05);
  border-top: 1px solid rgba(255,255,255,0.1);
  border-left: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px; color: #fff;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative; z-index: 1;
  height: 100%;
}
.step-card::after {
  content: ''; position: absolute; inset: 0; border-radius: 24px;
  background: linear-gradient(135deg, rgba(255,107,53,0.2) 0%, transparent 100%);
  opacity: 0; transition: opacity 0.4s; z-index: -1;
}
.step-card:hover { 
  transform: translateY(-10px); 
  border-color: rgba(255,107,53,0.4); 
  box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 20px rgba(255,107,53,0.15);
}
.step-card:hover::after { opacity: 1; }

.step-num {
  position: absolute; top: 10px; right: 20px;
  font-family: 'Playfair Display', serif;
  font-size: 80px; font-weight: 800;
  color: rgba(255,255,255,0.03); line-height: 1;
  transition: color 0.4s;
}
.step-card:hover .step-num { color: rgba(255,107,53,0.1); }

.step-icon-wrapper {
  width: 80px; height: 80px; margin: 0 auto 24px;
  background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(247,147,30,0.1));
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  position: relative;
}
.step-icon-wrapper::before {
  content: ''; position: absolute; inset: -4px; border-radius: 50%;
  border: 2px dashed rgba(255,107,53,0.3);
  animation: spin 10s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.step-icon { 
  font-size: 32px; 
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.step-title { font-weight: 700; font-size: 20px; margin-bottom: 12px; letter-spacing: 0.5px; }
.step-desc { font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.6; }

/* ===== CART SIDEBAR ===== */
.cart-fab {
  position: fixed; bottom: 32px; right: 32px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: #fff; width: 64px; height: 64px;
  border-radius: 50%; font-size: 24px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; z-index: 999;
  box-shadow: 0 8px 32px rgba(255,107,53,0.4);
  transition: var(--transition); border: none;
  text-decoration: none;
}
.cart-fab:hover { transform: scale(1.1); color: #fff; }
.cart-count {
  position: absolute; top: -4px; right: -4px;
  background: var(--dark); color: #fff;
  width: 22px; height: 22px; border-radius: 50%;
  font-size: 11px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

/* ===== FOOTER ===== */
footer {
  background: var(--dark2);
  color: rgba(255,255,255,0.75);
  padding: 64px 0 32px;
}
.footer-brand {
  font-family: 'Playfair Display', serif;
  font-size: 30px; font-weight: 800; color: #fff;
}
.footer-link {
  color: rgba(255,255,255,0.65);
  text-decoration: none; font-size: 14px;
  transition: color 0.2s;
  display: block; margin-bottom: 10px;
}
.footer-link:hover { color: var(--primary); }
.social-icon {
  width: 40px; height: 40px; border-radius: 50%;
  background: rgba(255,255,255,0.08);
  display: inline-flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.7); font-size: 16px;
  transition: var(--transition); text-decoration: none; margin-right: 8px;
}
.social-icon:hover { background: var(--primary); color: #fff; transform: translateY(-3px); }
.footer-divider { border-color: rgba(255,255,255,0.08); margin: 40px 0 24px; }

/* ===== TOAST ===== */
.toast-container-custom {
  position: fixed; bottom: 110px; right: 32px; z-index: 9999;
}
.toast-custom {
  background: var(--dark);
  color: #fff; border-radius: 14px;
  padding: 14px 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.25);
  display: flex; align-items: center; gap: 12px;
  animation: slideInRight 0.3s ease;
  margin-bottom: 10px;
}
@keyframes slideInRight { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

/* ===== UTILITIES ===== */
.section-py { padding: 80px 0; }
.bg-light-custom { background: #f8f9fc; }
.rounded-pill-custom { border-radius: 50px; }
</style>
</head>
<body>


<!-- NAVBAR -->
<nav class="navbar-foodhub">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between w-100">
      <a href="index.php" class="navbar-brand-custom">
        🍽️ Food<span>Hub</span>
      </a>
      
      <!-- Desktop Nav -->
      <div class="d-none d-lg-flex align-items-center gap-2">
        <a href="index.php" class="nav-link-custom active">Home</a>
        <a href="restaurants.php" class="nav-link-custom">Restaurants</a>
        <a href="donate/donate_food.php" class="donate-nav-pill">
          <i class="fas fa-heart"></i> Donate Food
        </a>
      </div>

      <div class="d-flex align-items-center gap-3">
        <?php if ($isLoggedIn): ?>
          <a href="customer/dashboard.php" class="nav-link-custom">
            <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($customerName) ?>
          </a>
          <a href="customer/orders.php" class="nav-link-custom"><i class="fas fa-receipt"></i></a>
        <?php else: ?>
          <a href="auth/login.php" class="btn-nav-login">Login</a>
          <a href="auth/register.php" class="btn-nav-register">Sign Up</a>
        <?php endif; ?>
        <!-- Mobile hamburger -->
        <button class="d-lg-none btn btn-outline-secondary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
          <i class="fas fa-bars text-white"></i>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- MOBILE NAV OFFCANVAS -->
<div class="offcanvas offcanvas-end" id="mobileNav" tabindex="-1" style="background:var(--dark);">
  <div class="offcanvas-header">
    <h5 class="text-white fw-bold">🍽️ FoodHub</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="d-flex flex-column gap-3">
      <a href="index.php" class="nav-link-custom">🏠 Home</a>
      <a href="restaurants.php" class="nav-link-custom">🍴 Restaurants</a>
      <a href="donate/donate_food.php" class="donate-nav-pill">❤️ Donate Food</a>
      <?php if (!$isLoggedIn): ?>
      <a href="auth/login.php" class="btn-nav-login text-center">Login</a>
      <a href="auth/register.php" class="btn-nav-register text-center">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- HERO SECTION -->
<section class="hero-section">
  <div class="hero-blob hero-blob-1"></div>
  <div class="hero-blob hero-blob-2"></div>
  <div class="container py-5">
    <div class="row align-items-center min-vh-80">
      <div class="col-lg-6 animate__animated animate__fadeInLeft">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span style="background:rgba(255,107,53,0.2);color:#FF6B35;padding:4px 14px;border-radius:50px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">🔥 #1 Food Delivery App in Pune</span>
        </div>
        <h1 class="hero-title">
          Order <span class="highlight">Delicious</span><br>
          Food & <span class="highlight">Share</span><br>
          the Love
        </h1>
        <p class="hero-subtitle mt-4">
          Discover the best restaurants near you, get food delivered fast, 
          and join our mission to reduce food waste by donating surplus food to NGOs.
        </p>

        <div class="search-hero">
          <i class="fas fa-search" style="color:#ccc;font-size:18px;"></i>
          <input type="text" id="heroSearch" placeholder="Search restaurants, dishes, cuisines...">
          <select class="form-select" style="border:none;outline:none;width:140px;font-weight:600;color:var(--dark);">
            <option>Pune</option><option>Mumbai</option><option>Delhi</option>
          </select>
          <button class="btn-search-hero" onclick="searchFood()">Search</button>
        </div>

        <div class="hero-stats">
          <div>
            <div class="hero-stat-num">500+</div>
            <div class="hero-stat-label">Restaurants</div>
          </div>
          <div>
            <div class="hero-stat-num">50K+</div>
            <div class="hero-stat-label">Happy Customers</div>
          </div>
          <div>
            <div class="hero-stat-num">10K+</div>
            <div class="hero-stat-label">Meals Donated</div>
          </div>
        </div>
      </div>

      <div class="col-lg-6 d-none d-lg-block animate__animated animate__fadeInRight">
        <div class="hero-food-grid">
          <div class="hero-food-card fc-1">
            <img src="assets/images/hero/hero_curry.png" alt="Curry">
          </div>
          <div class="hero-food-card fc-2">
            <img src="assets/images/hero/hero_pizza.png" alt="Pizza">
          </div>
          <div class="hero-food-card fc-3">
            <img src="assets/images/hero/hero_noodles.png" alt="Noodles">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- QUICK CATEGORY SECTION -->
<section style="padding:40px 0 20px;background:#fff;">
  <div class="container">
    <div class="category-scroll">
      <div class="d-flex gap-3 flex-nowrap">
        <div class="category-pill active" onclick="filterCategory('all', this)">
          <span>🍽️</span> All
        </div>
        <div class="category-pill" onclick="filterCategory('indian', this)">
          <span>🍛</span> Indian
        </div>
        <div class="category-pill" onclick="filterCategory('pizza', this)">
          <span>🍕</span> Pizza
        </div>
        <div class="category-pill" onclick="filterCategory('chinese', this)">
          <span>🥢</span> Chinese
        </div>
        <div class="category-pill" onclick="filterCategory('biryani', this)">
          <span>🍚</span> Biryani
        </div>
        <div class="category-pill" onclick="filterCategory('burger', this)">
          <span>🍔</span> Burgers
        </div>
        <div class="category-pill" onclick="filterCategory('desserts', this)">
          <span>🍮</span> Desserts
        </div>
        <div class="category-pill" onclick="filterCategory('veg', this)">
          <span>🥗</span> Pure Veg
        </div>
      </div>
    </div>
  </div>
</section>

<!-- RESTAURANTS SECTION -->
<section class="section-py bg-light-custom" id="restaurants">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col">
        <span class="section-badge">Top Picks</span>
        <h2 class="section-title">Popular Restaurants</h2>
        <p class="text-muted mt-2">Handpicked restaurants with the highest ratings</p>
      </div>
      <div class="col-auto">
        <a href="restaurants.php" class="btn" style="background:var(--light);color:var(--primary);font-weight:600;border-radius:50px;padding:10px 24px;">
          View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
      </div>
    </div>

    <div class="row g-4" id="restaurantsGrid">
      <?php foreach($restaurants as $r): ?>
      <div class="col-sm-6 col-lg-4">
        <a href="restaurant_menu.php?id=<?= $r['id'] ?>" style="text-decoration:none;">
          <div class="restaurant-card h-100">
            <div class="restaurant-cover">
              <?php 
              $emojis = ['🍛','🍕','🍜','🥘','🍔','🌮'];
              if (!empty($r['cover_image']) && $r['cover_image'] !== 'default-cover.jpg'): 
              ?>
                <img src="assets/images/restaurants/<?= htmlspecialchars($r['cover_image']) ?>" alt="<?= htmlspecialchars($r['restaurant_name']) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div class="food-emoji-big" style="font-size:70px">
                  <?= $emojis[$r['id'] % count($emojis)] ?>
                </div>
              <?php endif; ?>
              <div class="restaurant-time-badge"><i class="fas fa-clock me-1"></i>30-45 min</div>
              <?php if($r['id'] % 2 == 0): ?>
              <div class="restaurant-offer-badge">20% OFF</div>
              <?php endif; ?>
            </div>
            <?php if (!empty($r['logo']) && $r['logo'] !== 'default-restaurant.png'): ?>
              <img src="assets/images/restaurants/<?= htmlspecialchars($r['logo']) ?>" class="restaurant-logo" style="object-fit:cover;border:2px solid #fff;">
            <?php else: ?>
              <div class="restaurant-logo"><?= $emojis[$r['id'] % count($emojis)] ?></div>
            <?php endif; ?>
            <div class="restaurant-info">
              <div class="d-flex justify-content-between align-items-start">
                <div class="restaurant-name"><?= htmlspecialchars($r['restaurant_name']) ?></div>
                <div class="rating-badge"><i class="fas fa-star"></i> <?= $r['rating'] ?: '4.2' ?></div>
              </div>
              <div class="restaurant-meta mt-1">
                <span><?= htmlspecialchars($r['cuisine_type']) ?></span>
                <span class="mx-2">•</span>
                <span><?= htmlspecialchars($r['city']) ?></span>
              </div>
              <div class="d-flex align-items-center mt-2 gap-3">
                <span style="font-size:12px;color:#888;"><i class="fas fa-motorcycle me-1"></i>₹40 delivery</span>
                <span style="font-size:12px;color:#888;"><i class="fas fa-rupee-sign me-1"></i>Min ₹200</span>
              </div>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DONATE FOOD BANNER -->
<section class="donate-banner">
  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="donate-badge"><i class="fas fa-leaf"></i> Zero Food Waste Initiative</div>
        <h2 class="donate-title">
          Got Leftover Food?<br>
          Help Us <span class="green">Feed Someone</span> Today
        </h2>
        <p style="color:rgba(255,255,255,0.7);font-size:16px;line-height:1.7;max-width:520px;margin-bottom:32px;">
          If you have surplus food from a wedding, event, or restaurant — don't let it go to waste. 
          Our NGO partners are ready to pick it up and distribute it to those in need.
        </p>
        <a href="donate/donate_food.php" class="btn-donate-big">
          <i class="fas fa-hand-holding-heart" style="font-size:22px;"></i>
          Donate Food Now
        </a>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-center align-items-center">
        <div style="font-size:140px;filter:drop-shadow(0 20px 40px rgba(0,0,0,0.3));animation:float 4s ease-in-out infinite;">🤝</div>
      </div>
    </div>

    <div class="donate-steps">
      <div class="donate-step">
        <div class="donate-step-icon">📝</div>
        <h6>Fill the Form</h6>
        <p>Enter food details, quantity & pickup location</p>
      </div>
      <div class="donate-step">
        <div class="donate-step-icon">🔔</div>
        <h6>NGO Gets Notified</h6>
        <p>Nearby NGOs receive your donation request</p>
      </div>
      <div class="donate-step">
        <div class="donate-step-icon">🚗</div>
        <h6>Free Pickup</h6>
        <p>NGO volunteers collect from your location</p>
      </div>
      <div class="donate-step">
        <div class="donate-step-icon">😊</div>
        <h6>Someone Smiles</h6>
        <p>Your food reaches those who truly need it</p>
      </div>
    </div>
  </div>
</section>

<!-- POPULAR DISHES -->
<section class="section-py" id="popular-dishes">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col">
        <span class="section-badge">Bestsellers</span>
        <h2 class="section-title">Popular Dishes</h2>
        <p class="text-muted mt-2">Crowd favourites across all restaurants</p>
      </div>
    </div>
    <div class="row g-4">
      <?php
      $foodEmojis = ['🍛','🍕','🍜','🥘','🍔','🌮','🍱','🧆'];
      foreach($popularItems as $i => $item): 
      ?>
      <div class="col-6 col-md-4 col-xl-3">
        <div class="food-card h-100">
          <div class="food-img">
            <?php if (!empty($item['image']) && $item['image'] !== 'default-food.jpg'): ?>
              <img src="assets/images/menu/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div class="food-img-emoji"><?= $foodEmojis[$i % count($foodEmojis)] ?></div>
            <?php endif; ?>
            <?php if($item['is_bestseller']): ?>
            <div class="bestseller-tag">⭐ Bestseller</div>
            <?php endif; ?>
          </div>
          <div class="food-body">
            <div class="d-flex align-items-center gap-1 mb-1">
              <?php if($item['is_veg']): ?>
              <i class="fas fa-circle veg-icon" style="font-size:10px;"></i>
              <?php else: ?>
              <i class="fas fa-circle nonveg-icon" style="font-size:10px;"></i>
              <?php endif; ?>
              <span style="font-size:11px;color:#888;"><?= $item['is_veg'] ? 'Veg' : 'Non-Veg' ?></span>
            </div>
            <div class="food-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="food-desc text-muted"><?= htmlspecialchars($item['description'] ?? '') ?></div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="food-price">₹<?= number_format($item['price'], 0) ?> <span>• <?= $item['restaurant_name'] ?></span></div>
              <button class="btn-add-cart" onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['price'] ?>, <?= $item['restaurant_id'] ?>)" title="Add to cart">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-badge" style="background:rgba(255,107,53,0.15);">Simple &amp; Fast</span>
      <h2 class="section-title" style="color:#fff">How FoodHub Works</h2>
      <p style="color:rgba(255,255,255,0.6);max-width:480px;margin:12px auto 0;">
        Getting your favourite food delivered has never been easier
      </p>
    </div>
    <div class="row g-4">
      <div class="col-lg-3 col-sm-6 mb-4">
        <div class="step-card">
          <div class="step-num">01</div>
          <div class="step-icon-wrapper">
            <i class="fas fa-map-marker-alt step-icon"></i>
          </div>
          <div class="step-title">Set Location</div>
          <div class="step-desc">Enter your delivery address and discover restaurants near you</div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 mb-4">
        <div class="step-card">
          <div class="step-num">02</div>
          <div class="step-icon-wrapper">
            <i class="fas fa-utensils step-icon"></i>
          </div>
          <div class="step-title">Choose Food</div>
          <div class="step-desc">Browse menus, explore bestsellers, add items to your cart</div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 mb-4">
        <div class="step-card">
          <div class="step-num">03</div>
          <div class="step-icon-wrapper">
            <i class="fas fa-credit-card step-icon"></i>
          </div>
          <div class="step-title">Pay Securely</div>
          <div class="step-desc">Multiple payment options — online, COD, or wallet</div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 mb-4">
        <div class="step-card">
          <div class="step-num">04</div>
          <div class="step-icon-wrapper">
            <i class="fas fa-rocket step-icon"></i>
          </div>
          <div class="step-title">Fast Delivery</div>
          <div class="step-desc">Track your order live and receive it hot at your door</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="footer-brand">🍽️ FoodHub</div>
        <p class="mt-3" style="font-size:14px;line-height:1.7;color:rgba(255,255,255,0.6);">
          Connecting hungry people with great food, and making the world better by reducing food waste through our NGO donation network.
        </p>
        <div class="mt-4">
          <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-700 mb-4">Company</h6>
        <a href="#" class="footer-link">About Us</a>
        <a href="#" class="footer-link">Careers</a>
        <a href="#" class="footer-link">Blog</a>
        <a href="#" class="footer-link">Press</a>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-700 mb-4">Join Us</h6>
        <a href="auth/register.php?type=restaurant" class="footer-link">Add Restaurant</a>
        <a href="auth/register.php?type=ngo" class="footer-link">NGO Registration</a>
        <a href="donate/donate_food.php" class="footer-link">Donate Food</a>
        <a href="auth/register.php" class="footer-link">Create Account</a>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-700 mb-4">Support</h6>
        <a href="#" class="footer-link">Help Center</a>
        <a href="#" class="footer-link">Contact Us</a>
        <a href="#" class="footer-link">Privacy Policy</a>
        <a href="#" class="footer-link">Terms of Service</a>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-700 mb-4">Contact</h6>
        <p class="footer-link"><i class="fas fa-phone me-2" style="color:var(--primary)"></i>+91 98765 43210</p>
        <p class="footer-link"><i class="fas fa-envelope me-2" style="color:var(--primary)"></i>hello@foodhub.com</p>
        <p class="footer-link"><i class="fas fa-map-marker-alt me-2" style="color:var(--primary)"></i>Pune, Maharashtra</p>
      </div>
    </div>
    <hr class="footer-divider">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <p style="font-size:13px;color:rgba(255,255,255,0.45);margin:0">© 2024 FoodHub. Built with ❤️ to reduce food waste.</p>
      <div class="d-flex gap-3">
        <a href="auth/login.php?type=restaurant" style="color:rgba(255,255,255,0.5);font-size:13px;text-decoration:none;">Restaurant Login</a>
        <a href="auth/login.php?type=ngo" style="color:rgba(255,255,255,0.5);font-size:13px;text-decoration:none;">NGO Login</a>
      </div>
    </div>
  </div>
</footer>

<!-- CART FAB -->
<a href="customer/cart.php" class="cart-fab" id="cartFab">
  <i class="fas fa-shopping-cart"></i>
  <span class="cart-count" id="cartCount">0</span>
</a>

<!-- TOAST -->
<div class="toast-container-custom" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// Cart management
let cart = JSON.parse(localStorage.getItem('fh_cart') || '[]');
let cartRestaurantId = localStorage.getItem('fh_cart_restaurant') || null;

function updateCartCount() {
    const total = cart.reduce((s, i) => s + i.qty, 0);
    $('#cartCount').text(total);
    if (total > 0) $('#cartFab').addClass('animate__animated animate__pulse');
}
updateCartCount();

function addToCart(itemId, name, price, restaurantId) {
    <?php if(!$isLoggedIn): ?>
    showToast('warning', 'Please login to add items to cart!');
    setTimeout(() => window.location.href = 'auth/login.php', 1500);
    return;
    <?php endif; ?>

    if (cartRestaurantId && cartRestaurantId != restaurantId) {
        if (!confirm('Your cart contains items from a different restaurant. Clear cart and add this item?')) return;
        cart = [];
        localStorage.setItem('fh_cart', '[]');
    }
    
    cartRestaurantId = restaurantId;
    localStorage.setItem('fh_cart_restaurant', restaurantId);

    const existing = cart.find(i => i.id == itemId);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ id: itemId, name, price, qty: 1, restaurantId });
    }
    
    localStorage.setItem('fh_cart', JSON.stringify(cart));
    
    // Sync with server
    $.post('api/cart_handler.php', { action: 'add', menu_item_id: itemId }, null, 'json');
    
    updateCartCount();
    showToast('success', `${name} added to cart!`);
}

function filterCategory(cat, el) {
    $('.category-pill').removeClass('active');
    $(el).addClass('active');
    // Would filter in real implementation
}

function searchFood() {
    const q = $('#heroSearch').val().trim();
    if (q) window.location.href = `restaurants.php?search=${encodeURIComponent(q)}`;
}

$('#heroSearch').on('keypress', e => { if (e.which == 13) searchFood(); });

function showToast(type, msg) {
    const icons = { success: '✅', warning: '⚠️', error: '❌', info: 'ℹ️' };
    const colors = { success: '#27ae60', warning: '#f39c12', error: '#e74c3c', info: '#3498db' };
    const toast = $(`<div class="toast-custom"><span style="font-size:18px;">${icons[type]||'ℹ️'}</span><span>${msg}</span></div>`);
    toast.css('border-left', `4px solid ${colors[type]||'#3498db'}`);
    $('#toastContainer').append(toast);
    setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
}

// Smooth scroll on category clicks
$(document).on('click', '[data-scroll]', function() {
    $('html,body').animate({ scrollTop: $($(this).data('scroll')).offset().top - 80 }, 600);
});

// Animate on scroll
function animateOnScroll() {
    $('.restaurant-card, .food-card, .step-card, .donate-step').each(function() {
        const rect = this.getBoundingClientRect();
        if (rect.top < window.innerHeight - 60) {
            $(this).addClass('animate__animated animate__fadeInUp');
        }
    });
}
$(window).on('scroll', animateOnScroll);
animateOnScroll();
</script>
</body>
</html>
