<?php
require_once 'includes/config.php';
$db = getDB();
$restId = intval($_GET['id'] ?? 0);
if (!$restId) redirect('index.php');

$rest = $db->prepare("SELECT * FROM restaurants WHERE id=? AND is_approved=1");
$rest->execute([$restId]);
$restaurant = $rest->fetch();
if (!$restaurant) redirect('index.php');

$cats = $db->prepare("SELECT * FROM menu_categories WHERE restaurant_id=? ORDER BY sort_order");
$cats->execute([$restId]);
$categories = $cats->fetchAll();

$items = $db->prepare("SELECT mi.*, mc.name as cat_name FROM menu_items mi LEFT JOIN menu_categories mc ON mi.category_id=mc.id WHERE mi.restaurant_id=? AND mi.is_available=1 ORDER BY mc.sort_order, mi.is_bestseller DESC");
$items->execute([$restId]);
$menuItems = $items->fetchAll();

$isLoggedIn = isLoggedIn('customer');

$foodEmojis = ['🍛','🍕','🍜','🥘','🍔','🌮','🍱','🧆','🥗','🍮'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Restaurant Menu – FoodHub</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root { --primary:#FF6B35; --secondary:#F7931E; --accent:#27ae60; --dark:#1a1a2e; }
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: #f8f9fc; color: var(--dark); margin: 0; }

.navbar-fh { background: rgba(26,26,46,0.97); padding: 14px 0; position: sticky; top: 0; z-index: 200; }
.brand-lnk { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 800; color: #fff; text-decoration: none; }
.brand-lnk span { color: var(--primary); }

.rest-hero {
  height: 280px; position: relative; overflow: hidden;
  background: linear-gradient(135deg, #1a1a2e, #16213e);
  display: flex; align-items: flex-end;
}
.rest-hero-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 100%); }
.rest-hero-emoji { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-60%); font-size: 100px; opacity: 0.25; }
.rest-hero-info { position: relative; z-index: 1; padding: 24px; color: #fff; width: 100%; }
.rest-name { font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 800; }
.rest-meta { font-size: 14px; color: rgba(255,255,255,0.75); margin-top: 6px; }
.rest-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); padding: 4px 12px; border-radius: 50px; font-size: 13px; font-weight: 600; margin-right: 8px; }

.menu-layout { display: flex; gap: 24px; max-width: 1200px; margin: 32px auto; padding: 0 16px 60px; }

/* SIDEBAR CATEGORIES */
.cat-sidebar {
  width: 220px; flex-shrink: 0;
  background: #fff; border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.07);
  height: fit-content; position: sticky; top: 80px;
  overflow: hidden;
}
.cat-sidebar-title { padding: 16px 20px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #888; border-bottom: 1px solid #f0f0f0; }
.cat-item { padding: 14px 20px; cursor: pointer; font-weight: 600; font-size: 14px; color: #555; border-left: 3px solid transparent; transition: all 0.2s; }
.cat-item:hover { background: #fff7f0; color: var(--primary); }
.cat-item.active { background: #fff7f0; color: var(--primary); border-left-color: var(--primary); }

/* MENU AREA */
.menu-main { flex: 1; min-width: 0; }
.veg-toggle-bar {
  background: #fff; border-radius: 14px;
  padding: 14px 20px; margin-bottom: 24px;
  display: flex; align-items: center; justify-content: space-between;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.toggle-switch { position: relative; width: 44px; height: 24px; cursor: pointer; }
.toggle-switch input { display: none; }
.toggle-track { display: block; width: 100%; height: 100%; background: #e0e0e0; border-radius: 12px; transition: background 0.2s; }
.toggle-switch input:checked ~ .toggle-track { background: var(--accent); }
.toggle-thumb { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: #fff; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: left 0.2s; }
.toggle-switch input:checked ~ .toggle-track ~ .toggle-thumb { left: 22px; }

.menu-section { margin-bottom: 40px; }
.menu-section-title { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
.menu-section-count { font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; color: #aaa; }

.menu-item-row {
  display: flex; gap: 16px; padding: 16px;
  background: #fff; border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  margin-bottom: 12px; transition: all 0.2s;
  border: 1px solid #f5f5f5;
}
.menu-item-row:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.1); transform: translateY(-1px); }
.menu-item-img {
  width: 100px; height: 90px; border-radius: 12px;
  overflow: hidden; flex-shrink: 0;
  background: #fff7f0; display: flex; align-items: center; justify-content: center; font-size: 44px;
}
.menu-item-img img { width: 100%; height: 100%; object-fit: cover; }
.menu-item-info { flex: 1; min-width: 0; }
.item-veg-dot { width: 14px; height: 14px; border-radius: 3px; border: 2px solid; display: inline-block; margin-bottom: 4px; }
.item-veg-dot.veg { border-color: var(--accent); }
.item-veg-dot.veg::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); display: block; margin: 2px auto; }
.item-veg-dot.nonveg { border-color: #e74c3c; }
.item-veg-dot.nonveg::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #e74c3c; display: block; margin: 2px auto; }
.item-name { font-weight: 700; font-size: 15px; color: var(--dark); margin-bottom: 4px; }
.item-desc { font-size: 12px; color: #888; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.item-price { font-weight: 800; font-size: 16px; color: var(--dark); margin-top: 8px; }
.item-actions { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; flex-shrink: 0; }
.bestseller-chip { font-size: 11px; font-weight: 700; background: linear-gradient(135deg, #f39c12, #f1c40f); color: #fff; padding: 2px 8px; border-radius: 50px; }
.btn-add-item {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none; color: #fff; padding: 8px 20px;
  border-radius: 50px; font-size: 14px; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
  display: flex; align-items: center; gap: 6px;
}
.btn-add-item:hover { transform: scale(1.05); box-shadow: 0 6px 16px rgba(255,107,53,0.35); }
.qty-control { display: flex; align-items: center; gap: 8px; background: #fff; border: 2px solid var(--primary); border-radius: 50px; padding: 4px 10px; }
.qty-btn { background: none; border: none; cursor: pointer; font-size: 18px; color: var(--primary); font-weight: 700; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s; }
.qty-btn:hover { background: var(--primary); color: #fff; }
.qty-num { font-weight: 800; font-size: 16px; min-width: 24px; text-align: center; }

/* CART PANEL */
.cart-panel {
  width: 320px; flex-shrink: 0;
  background: #fff; border-radius: 20px;
  box-shadow: 0 8px 40px rgba(0,0,0,0.1);
  height: fit-content; position: sticky; top: 80px;
  overflow: hidden;
}
.cart-header {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  padding: 20px; color: #fff;
}
.cart-header h5 { font-family: 'Playfair Display', serif; font-size: 18px; margin: 0; }
.cart-items { max-height: 380px; overflow-y: auto; padding: 16px; }
.cart-items::-webkit-scrollbar { width: 4px; }
.cart-items::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }
.cart-item-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f5f5f5; }
.cart-item-row:last-child { border-bottom: none; }
.cart-item-name { flex: 1; font-size: 13px; font-weight: 600; color: var(--dark); }
.cart-item-price { font-size: 13px; font-weight: 700; color: var(--dark); white-space: nowrap; }
.cart-empty { text-align: center; padding: 32px 16px; color: #aaa; }
.cart-empty-icon { font-size: 48px; margin-bottom: 12px; }
.cart-summary { padding: 16px; border-top: 2px solid #f5f5f5; }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #555; }
.summary-row.total { font-weight: 800; font-size: 16px; color: var(--dark); margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ddd; }
.btn-checkout { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 50px; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 12px; }
.btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,107,53,0.35); }
.btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.coupon-row { display: flex; gap: 8px; margin-bottom: 12px; }
.coupon-input { flex: 1; border: 2px solid #e8e8e8; border-radius: 50px; padding: 8px 16px; font-size: 13px; outline: none; }
.coupon-input:focus { border-color: var(--primary); }
.coupon-btn { background: var(--primary); color: #fff; border: none; border-radius: 50px; padding: 8px 16px; font-size: 13px; font-weight: 700; cursor: pointer; }

/* TOAST */
.toast-custom { position: fixed; bottom: 24px; right: 24px; background: var(--dark); color: #fff; padding: 14px 20px; border-radius: 14px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); z-index: 9999; animation: slideIn 0.3s ease; display: flex; align-items: center; gap: 10px; }
@keyframes slideIn { from { transform: translateX(120px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>
</head>
<body>


<!-- NAVBAR -->
<nav class="navbar-fh">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand-lnk">🍽️ Food<span>Hub</span></a>
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;"><i class="fas fa-arrow-left me-1"></i>Back</a>
      <?php if(!$isLoggedIn): ?>
      <a href="auth/login.php" style="background:linear-gradient(135deg,#FF6B35,#F7931E);color:#fff;padding:8px 20px;border-radius:50px;text-decoration:none;font-weight:700;font-size:14px;">Login to Order</a>
      <?php else: ?>
      <a href="customer/orders.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;"><i class="fas fa-receipt me-1"></i>My Orders</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- RESTAURANT HERO -->
<div class="rest-hero">
  <div class="rest-hero-overlay"></div>
  <div class="rest-hero-emoji">🍽️</div>
  <div class="rest-hero-info">
    <div class="rest-name"><?= htmlspecialchars($restaurant['restaurant_name']) ?></div>
    <div class="rest-meta">
      <span class="rest-badge"><i class="fas fa-star" style="color:#f1c40f;"></i> <?= $restaurant['rating'] ?: '4.2' ?></span>
      <span class="rest-badge"><i class="fas fa-clock"></i> 30-45 min</span>
      <span class="rest-badge"><i class="fas fa-motorcycle"></i> ₹40 delivery</span>
      <span class="rest-badge"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($restaurant['city']) ?></span>
    </div>
    <div style="margin-top:8px;font-size:13px;color:rgba(255,255,255,0.6);"><?= htmlspecialchars($restaurant['cuisine_type']) ?></div>
  </div>
</div>

<div class="menu-layout">
  <!-- CATEGORY SIDEBAR -->
  <div class="cat-sidebar d-none d-lg-block">
    <div class="cat-sidebar-title">Menu</div>
    <?php foreach($categories as $cat): ?>
    <div class="cat-item" onclick="scrollToSection('cat-<?= $cat['id'] ?>', this)"><?= htmlspecialchars($cat['name']) ?></div>
    <?php endforeach; ?>
  </div>

  <!-- MENU MAIN -->
  <div class="menu-main">
    <!-- Veg Toggle -->
    <div class="veg-toggle-bar">
      <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:14px;font-weight:600;color:#555;">Veg Only</span>
        <label class="toggle-switch">
          <input type="checkbox" id="vegToggle" onchange="toggleVeg()">
          <span class="toggle-track"></span>
          <span class="toggle-thumb"></span>
        </label>
      </div>
      <div style="font-size:14px;color:#888;">
        <i class="fas fa-search me-2"></i>
        <input type="text" id="menuSearch" placeholder="Search in menu..." style="border:none;outline:none;font-family:'DM Sans',sans-serif;width:180px;" oninput="searchMenu(this.value)">
      </div>
    </div>

    <!-- Menu Sections -->
    <?php
    $grouped = [];
    foreach($menuItems as $item) {
        $catName = $item['cat_name'] ?? 'Other';
        $catId = $item['category_id'] ?? 0;
        $grouped[$catId]['name'] = $catName;
        $grouped[$catId]['items'][] = $item;
    }
    foreach($grouped as $catId => $group):
    ?>
    <div class="menu-section" id="cat-<?= $catId ?>" data-category="<?= htmlspecialchars($group['name']) ?>">
      <div class="menu-section-title">
        <span><?= $foodEmojis[$catId % count($foodEmojis)] ?></span>
        <?= htmlspecialchars($group['name']) ?>
        <span class="menu-section-count">(<?= count($group['items']) ?>)</span>
      </div>

      <?php foreach($group['items'] as $i => $item): ?>
      <div class="menu-item-row <?= $item['is_veg'] ? 'veg-item' : 'nonveg-item' ?>"
           id="item-<?= $item['id'] ?>"
           data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>"
           data-veg="<?= $item['is_veg'] ?>">
        <div class="menu-item-img">
          <?php if (!empty($item['image']) && $item['image'] !== 'default-food.jpg'): ?>
            <img src="assets/images/menu/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <?= $foodEmojis[$i % count($foodEmojis)] ?>
          <?php endif; ?>
        </div>
        <div class="menu-item-info">
          <div class="item-veg-dot <?= $item['is_veg'] ? 'veg' : 'nonveg' ?>"></div>
          <?php if($item['is_bestseller']): ?>
          <span class="bestseller-chip">⭐ Bestseller</span>
          <?php endif; ?>
          <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
          <div class="item-desc"><?= htmlspecialchars($item['description'] ?? '') ?></div>
          <div class="item-price">₹<?= number_format($item['price'], 0) ?></div>
        </div>
        <div class="item-actions">
          <div></div>
          <div id="actions-<?= $item['id'] ?>">
            <!-- Hidden data element so rebuilt Add buttons can restore item details -->
            <span id="item-data-<?= $item['id'] ?>"
                  data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>"
                  data-price="<?= $item['price'] ?>"
                  data-isveg="<?= (int)$item['is_veg'] ?>"
                  style="display:none"></span>
            <button class="btn-add-item" onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['price'] ?>, <?= $restId ?>, <?= (int)$item['is_veg'] ?>)">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- CART PANEL -->
  <div class="cart-panel d-none d-xl-block">
    <div class="cart-header">
      <h5><i class="fas fa-shopping-cart me-2"></i>Your Order</h5>
      <div style="font-size:13px;opacity:0.85;"><?= htmlspecialchars($restaurant['restaurant_name']) ?></div>
    </div>
    <div class="cart-items" id="cartItemsList">
      <div class="cart-empty" id="cartEmpty">
        <div class="cart-empty-icon">🛒</div>
        <p style="font-size:14px;margin:0;">Your cart is empty<br><small>Add items to get started</small></p>
      </div>
    </div>
    <div class="cart-summary" id="cartSummary" style="display:none;">
      <div class="coupon-row">
        <input type="text" class="coupon-input" id="couponInput" placeholder="Coupon code">
        <button class="coupon-btn" onclick="applyCoupon()">Apply</button>
      </div>
      <div class="summary-row"><span>Subtotal</span><span id="subtotalAmt">₹0</span></div>
      <div class="summary-row"><span>Delivery</span><span style="color:#27ae60;">₹40</span></div>
      <div class="summary-row" id="discountRow" style="display:none"><span style="color:#27ae60;">Discount</span><span id="discountAmt" style="color:#27ae60;">-₹0</span></div>
      <div class="summary-row total"><span>Total</span><span id="totalAmt">₹0</span></div>
      <button class="btn-checkout" id="checkoutBtn" onclick="proceedCheckout()">
        <i class="fas fa-bolt me-2"></i>Proceed to Checkout
      </button>
    </div>
  </div>
</div>

<!-- MOBILE CART FAB -->
<button onclick="proceedCheckout()" id="mobileCartFab" style="display:none;position:fixed;bottom:24px;left:16px;right:16px;background:linear-gradient(135deg,#FF6B35,#F7931E);color:#fff;border:none;border-radius:16px;padding:16px;font-size:15px;font-weight:700;z-index:100;cursor:pointer;box-shadow:0 8px 32px rgba(255,107,53,0.4);">
  <i class="fas fa-shopping-cart me-2"></i>View Cart (<span id="mobileCartCount">0</span> items) • <span id="mobileCartTotal">₹0</span>
</button>

<div id="toastContainer"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// ── Cart state ────────────────────────────────────────────
let cart    = {};   // { itemId: {id, name, price, qty, isVeg} }
let cartTotal = 0;
const DELIVERY    = 40;
const REST_ID     = <?= $restId ?>;
const REST_NAME   = <?= json_encode($restaurant['restaurant_name']) ?>;
const CART_KEY    = 'foodhub_cart';   // same key checkout.php reads
let discount      = 0;
let appliedCoupon = '';

// ── Add to cart ───────────────────────────────────────────
function addToCart(id, name, price, restId, isVeg) {
    <?php if(!$isLoggedIn): ?>
    showToast('⚠️ Please login to place orders!');
    setTimeout(() => window.location.href = 'auth/login.php', 1500);
    return;
    <?php endif; ?>

    // If items from a different restaurant exist, ask before clearing
    const saved = getSavedCart();
    if (saved && saved.restaurantId && saved.restaurantId !== REST_ID && saved.items && saved.items.length > 0) {
        if (!confirm('Your cart has items from another restaurant. Clear cart and start fresh?')) return;
        cart = {};
        discount = 0;
        appliedCoupon = '';
    }

    if (cart[id]) {
        cart[id].qty++;
    } else {
        cart[id] = { id, name, price: parseFloat(price), qty: 1, isVeg: !!isVeg };
    }
    renderCart();
    updateQtyControl(id);
    showToast('✅ ' + name + ' added!');
}

// ── Change quantity ───────────────────────────────────────
function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) {
        delete cart[id];
    }
    renderCart();
    updateQtyControl(id);
}

// ── Update the +/- button controls on menu item ───────────
function updateQtyControl(id) {
    const item = cart[id];
    if (item) {
        const isVeg = item.isVeg ? 1 : 0;
        $(`#actions-${id}`).html(
            `<div class="qty-control">
                <button class="qty-btn" onclick="changeQty(${id},-1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${id},1)">+</button>
            </div>`
        );
    } else {
        // Rebuild add button — use data attrs to avoid JS escaping issues
        const btn = $(`#actions-${id}`).data();
        $(`#actions-${id}`).html(
            `<button class="btn-add-item" onclick="addToCartFromBtn(${id})"><i class="fas fa-plus"></i> Add</button>`
        );
    }
}

// Called by rebuilt add buttons (reads data attrs set on menu row)
function addToCartFromBtn(id) {
    const row  = document.getElementById('actions-' + id).closest('.menu-item-row') ||
                 document.getElementById('actions-' + id).closest('[data-item-id]');
    const el   = document.getElementById('item-data-' + id);
    if (el) {
        addToCart(id, el.dataset.name, el.dataset.price, REST_ID, parseInt(el.dataset.isveg));
    }
}

// ── Render cart panel ─────────────────────────────────────
function renderCart() {
    const keys     = Object.keys(cart);
    cartTotal      = keys.reduce((s, k) => s + cart[k].price * cart[k].qty, 0);
    const finalTotal = Math.max(0, cartTotal + DELIVERY - discount);
    const totalItems = keys.reduce((s, k) => s + cart[k].qty, 0);

    if (keys.length === 0) {
        $('#cartEmpty').show();
        $('#cartSummary').hide();
        $('#mobileCartFab').hide();
        saveCartToSession([]);
        return;
    }

    $('#cartEmpty').hide();
    $('#cartSummary').show();

    let html = '';
    keys.forEach(id => {
        const item = cart[id];
        const dot  = item.isVeg
            ? '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#27ae60;margin-right:6px;flex-shrink:0"></span>'
            : '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e74c3c;margin-right:6px;flex-shrink:0"></span>';
        html += `<div class="cart-item-row">
            <div class="cart-item-name" style="display:flex;align-items:center">${dot}${item.name}</div>
            <div class="d-flex align-items-center gap-1" style="flex-shrink:0">
                <button onclick="changeQty(${id},-1)" style="width:22px;height:22px;border-radius:50%;border:none;background:#f0f0f0;cursor:pointer;font-size:13px;font-weight:700;line-height:1">−</button>
                <span style="font-weight:700;font-size:13px;min-width:16px;text-align:center">${item.qty}</span>
                <button onclick="changeQty(${id},1)" style="width:22px;height:22px;border-radius:50%;border:none;background:#f0f0f0;cursor:pointer;font-size:13px;font-weight:700;line-height:1">+</button>
            </div>
            <div class="cart-item-price">₹${(item.price * item.qty).toFixed(0)}</div>
        </div>`;
    });

    $('#cartItemsList').html(html + '<div id="cartEmpty" style="display:none"></div>');
    $('#subtotalAmt').text('₹' + cartTotal.toFixed(0));
    $('#discountRow').toggle(discount > 0);
    $('#discountAmt').text('-₹' + discount.toFixed(0));
    $('#totalAmt').text('₹' + finalTotal.toFixed(0));

    $('#mobileCartFab').show();
    $('#mobileCartCount').text(totalItems);
    $('#mobileCartTotal').text('₹' + finalTotal.toFixed(0));

    // Always save current cart to sessionStorage so checkout can read it
    saveCartToSession(keys);
}

// ── Save cart to sessionStorage in the format checkout.php expects ──
function saveCartToSession(keys) {
    const items = (keys || Object.keys(cart)).map(k => ({
        id:    cart[k].id,
        name:  cart[k].name,
        price: cart[k].price,
        qty:   cart[k].qty,
        isVeg: cart[k].isVeg
    }));
    const finalTotal = Math.max(0, cartTotal + DELIVERY - discount);
    const payload = {
        items:          items,
        restaurantId:   REST_ID,
        restaurantName: REST_NAME,
        subtotal:       cartTotal,
        delivery:       DELIVERY,
        discount:       discount,
        couponCode:     appliedCoupon,
        total:          finalTotal
    };
    sessionStorage.setItem(CART_KEY, JSON.stringify(payload));
}

function getSavedCart() {
    try { return JSON.parse(sessionStorage.getItem(CART_KEY)); } catch(e) { return null; }
}

// ── Coupon ────────────────────────────────────────────────
function applyCoupon() {
    const code = $('#couponInput').val().trim().toUpperCase();
    if (!code) { showToast('❌ Enter a coupon code first'); return; }
    if (cartTotal === 0) { showToast('❌ Add items before applying coupon'); return; }

    $.ajax({
        url: 'api/cart_handler.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'apply_coupon', code: code, subtotal: cartTotal }),
        success: function(res) {
            if (res && res.success) {
                discount      = parseFloat(res.discount) || 0;
                appliedCoupon = code;
                showToast('🎉 ' + res.message);
                renderCart();
            } else {
                discount = 0; appliedCoupon = '';
                showToast('❌ ' + (res ? res.message : 'Invalid coupon'));
                renderCart();
            }
        },
        error: function() {
            // Fallback: handle basic coupons client-side if API call fails
            if (code === 'WELCOME50' && cartTotal >= 200)      { discount = 50;              appliedCoupon = code; showToast('🎉 ₹50 off!'); }
            else if (code === 'FEAST20' && cartTotal >= 300)   { discount = cartTotal * 0.2; appliedCoupon = code; showToast('🎉 20% off!'); }
            else if (code === 'DONATE10' && cartTotal >= 100)  { discount = 10;              appliedCoupon = code; showToast('🎉 ₹10 off!'); }
            else { discount = 0; appliedCoupon = ''; showToast('❌ Invalid or inapplicable coupon'); }
            renderCart();
        },
        dataType: 'json'
    });
}

// ── Proceed to checkout ───────────────────────────────────
function proceedCheckout() {
    const keys = Object.keys(cart);
    if (!keys.length) { showToast('⚠️ Add items to cart first'); return; }

    // Save final cart state to sessionStorage
    saveCartToSession(keys);

    // Navigate to checkout
    window.location.href = 'customer/checkout.php';
}

function toggleVeg() {
    const vegOnly = $('#vegToggle').prop('checked');
    $('.nonveg-item').toggle(!vegOnly);
}

function searchMenu(q) {
    q = q.toLowerCase();
    $('.menu-item-row').each(function() {
        const name = $(this).data('name') || '';
        $(this).toggle(!q || name.includes(q));
    });
}

function scrollToSection(id, el) {
    $('.cat-item').removeClass('active');
    $(el).addClass('active');
    const target = document.getElementById(id);
    if (target) window.scrollTo({ top: target.offsetTop - 90, behavior: 'smooth' });
}

function showToast(msg) {
    const t = $(`<div class="toast-custom">${msg}</div>`);
    $('body').append(t);
    setTimeout(() => t.fadeOut(300, () => t.remove()), 2500);
}

// Sticky category highlight
window.addEventListener('scroll', function() {
    document.querySelectorAll('.menu-section').forEach(sec => {
        const rect = sec.getBoundingClientRect();
        if (rect.top < 150 && rect.bottom > 100) {
            const id = sec.id;
            $('.cat-item').removeClass('active');
            $(`.cat-item[onclick*="${id}"]`).addClass('active');
        }
    });
});
</script>
</body>
</html>
