<?php
require_once '../includes/config.php';
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$cid = $_SESSION['customer_id'];
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$cid]);
$customer = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--accent:#27ae60;--dark:#1a1a2e}
body{font-family:'DM Sans',sans-serif;background:#f8f9fa}
.navbar-brand{font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--primary)!important}
.checkout-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);padding:1.75rem;margin-bottom:1.5rem}
.section-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;margin-bottom:1.25rem}
.payment-option{border:2px solid #e9ecef;border-radius:12px;padding:1rem 1.25rem;cursor:pointer;transition:.3s;display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem}
.payment-option:hover{border-color:var(--primary)}
.payment-option.selected{border-color:var(--primary);background:#fff8f5}
.payment-option input[type=radio]{accent-color:var(--primary)}
.order-summary-item{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px dashed #eee}
.order-summary-item:last-child{border-bottom:none}
.btn-checkout{background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;color:#fff;border-radius:50px;padding:.85rem 2rem;font-weight:600;font-size:1rem;width:100%;transition:.3s}
.btn-checkout:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,107,53,.4)}
.veg-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:6px}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="../index.php">🍽️ FoodHub</a>
    <div class="ms-auto d-flex align-items-center gap-2">
      <span class="text-muted small">Logged in as <?= htmlspecialchars($customer['full_name']) ?></span>
    </div>
  </div>
</nav>

<!-- Progress Steps -->
<div class="bg-white border-bottom py-3">
  <div class="container">
    <div class="d-flex align-items-center justify-content-center gap-4">
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:var(--primary);color:#fff;font-weight:700;font-size:.85rem">1</div>
        <span class="fw-600 small" style="color:var(--primary)">Cart</span>
      </div>
      <div style="height:2px;width:40px;background:#e9ecef"></div>
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:var(--primary);color:#fff;font-weight:700;font-size:.85rem">2</div>
        <span class="fw-600 small" style="color:var(--primary)">Checkout</span>
      </div>
      <div style="height:2px;width:40px;background:#e9ecef"></div>
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:#e9ecef;color:#aaa;font-weight:700;font-size:.85rem">3</div>
        <span class="text-muted small">Confirmed</span>
      </div>
    </div>
  </div>
</div>

<div class="container py-4">
  <div class="row g-4">
    <!-- Left: Delivery + Payment -->
    <div class="col-lg-7">
      <!-- Delivery Address -->
      <div class="checkout-card">
        <div class="section-title"><i class="fas fa-map-marker-alt me-2" style="color:var(--primary)"></i>Delivery Address</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-600 small">Full Name</label>
            <input type="text" class="form-control rounded-3" id="del_name" value="<?= htmlspecialchars($customer['full_name']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600 small">Phone</label>
            <input type="text" class="form-control rounded-3" id="del_phone" value="<?= htmlspecialchars($customer['phone']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600 small">City</label>
            <input type="text" class="form-control rounded-3" id="del_city" value="<?= htmlspecialchars($customer['city']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-600 small">Delivery Address *</label>
            <textarea class="form-control rounded-3" id="del_address" rows="3" placeholder="House/Flat no., Street, Landmark..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-600 small">Delivery Instructions (optional)</label>
            <input type="text" class="form-control rounded-3" id="del_instructions" placeholder="E.g. Ring the bell, leave at door...">
          </div>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="checkout-card">
        <div class="section-title"><i class="fas fa-credit-card me-2" style="color:var(--primary)"></i>Payment Method</div>
        <div id="payment-options">
          <label class="payment-option selected">
            <input type="radio" name="payment" value="cod" checked>
            <i class="fas fa-money-bill-wave fa-lg text-success"></i>
            <div>
              <div class="fw-600">Cash on Delivery</div>
              <small class="text-muted">Pay when your food arrives</small>
            </div>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment" value="upi">
            <i class="fas fa-mobile-alt fa-lg" style="color:var(--primary)"></i>
            <div>
              <div class="fw-600">UPI</div>
              <small class="text-muted">Google Pay, PhonePe, BHIM</small>
            </div>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment" value="card">
            <i class="fas fa-credit-card fa-lg text-info"></i>
            <div>
              <div class="fw-600">Credit / Debit Card</div>
              <small class="text-muted">Visa, Mastercard, RuPay</small>
            </div>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment" value="wallet">
            <i class="fas fa-wallet fa-lg text-warning"></i>
            <div>
              <div class="fw-600">Wallet</div>
              <small class="text-muted">Paytm, Amazon Pay</small>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- Right: Order Summary -->
    <div class="col-lg-5">
      <div class="checkout-card" style="position:sticky;top:80px">
        <div class="section-title"><i class="fas fa-receipt me-2" style="color:var(--primary)"></i>Order Summary</div>
        <div id="checkout-items">
          <!-- Items injected from cart in sessionStorage -->
          <p class="text-muted text-center py-3" id="empty-cart-msg">Loading cart...</p>
        </div>
        <hr>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Subtotal</span>
          <span id="co-subtotal">₹0</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Delivery fee</span>
          <span id="co-delivery">₹40</span>
        </div>
        <div class="d-flex justify-content-between mb-1 d-none" id="co-discount-row">
          <span class="text-success">Discount</span>
          <span class="text-success" id="co-discount">-₹0</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold fs-5">
          <span>Total</span>
          <span style="color:var(--primary)" id="co-total">₹40</span>
        </div>
        <div class="mt-1 small text-muted" id="co-restaurant-name"></div>
        <div class="mt-1 small text-muted" id="co-coupon-info"></div>

        <button class="btn-checkout mt-3" id="place-order-btn" onclick="placeOrder()">
          <i class="fas fa-check-circle me-2"></i>Place Order
        </button>
        <p class="text-center text-muted small mt-2"><i class="fas fa-shield-alt me-1"></i>Secure checkout. Your data is protected.</p>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-body text-center py-5">
        <div style="font-size:5rem">🎉</div>
        <h4 class="fw-bold mt-3" style="font-family:'Playfair Display',serif">Order Placed!</h4>
        <p class="text-muted">Your order has been confirmed and the restaurant is preparing your food.</p>
        <p class="fw-bold" id="modal-order-number" style="color:var(--primary)"></p>
        <a href="orders.php" class="btn rounded-pill px-5 fw-600" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff">Track Order</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// ── Cart key must match restaurant_menu.php ───────────────
const cartKey = 'foodhub_cart';

function loadCart() {
    let raw = sessionStorage.getItem(cartKey);
    let cart = null;

    try { cart = raw ? JSON.parse(raw) : null; } catch(e) { cart = null; }

    // Empty or missing cart — show helpful message
    if (!cart || !cart.items || cart.items.length === 0) {
        document.getElementById('empty-cart-msg').innerHTML =
            '🛒 Your cart is empty.<br><br>' +
            '<a href="../index.php" class="btn btn-sm rounded-pill px-4" ' +
            'style="background:linear-gradient(135deg,#FF6B35,#F7931E);color:#fff;font-weight:600">' +
            'Browse Restaurants</a>';
        // Disable place order button
        const btn = document.getElementById('place-order-btn');
        if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; }
        return;
    }

    document.getElementById('empty-cart-msg').style.display = 'none';
    document.getElementById('co-restaurant-name').textContent = '🍴 ' + (cart.restaurantName || '');

    let html = '';
    cart.items.forEach(item => {
        const dot = item.isVeg
            ? '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#27ae60;margin-right:5px"></span>'
            : '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e74c3c;margin-right:5px"></span>';
        html += `<div class="order-summary-item">
            <span style="display:flex;align-items:center">${dot}${item.name}
                <span class="text-muted ms-1">×${item.qty}</span>
            </span>
            <span class="fw-600">₹${(item.price * item.qty).toFixed(2)}</span>
        </div>`;
    });
    document.getElementById('checkout-items').innerHTML = html;

    const sub  = parseFloat(cart.subtotal)  || 0;
    const del  = parseFloat(cart.delivery)  || 40;
    const disc = parseFloat(cart.discount)  || 0;
    const tot  = parseFloat(cart.total)     || (sub + del - disc);

    document.getElementById('co-subtotal').textContent = '₹' + sub.toFixed(2);
    document.getElementById('co-delivery').textContent = '₹' + del.toFixed(2);
    document.getElementById('co-total').textContent    = '₹' + tot.toFixed(2);

    if (disc > 0) {
        document.getElementById('co-discount-row').classList.remove('d-none');
        document.getElementById('co-discount').textContent  = '-₹' + disc.toFixed(2);
        document.getElementById('co-coupon-info').textContent = '🏷️ ' + (cart.couponCode || '');
    }
}

// Payment option styling
document.querySelectorAll('.payment-option').forEach(el => {
    el.addEventListener('click', () => {
        document.querySelectorAll('.payment-option').forEach(p => p.classList.remove('selected'));
        el.classList.add('selected');
    });
});

function placeOrder() {
    const address = document.getElementById('del_address').value.trim();
    if (!address) { alert('Please enter your delivery address.'); return; }
    
    const cart = JSON.parse(sessionStorage.getItem(cartKey) || '{"items":[]}');
    if (!cart.items || cart.items.length === 0) { alert('Your cart is empty!'); return; }
    
    const payment = document.querySelector('input[name=payment]:checked').value;
    const btn = document.getElementById('place-order-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Placing Order...';
    btn.disabled = true;
    
    $.ajax({
        url: '../api/order_handler.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'place_order',
            restaurant_id: cart.restaurantId,
            items: cart.items,
            subtotal: cart.subtotal,
            delivery_fee: cart.delivery,
            discount: cart.discount,
            coupon_code: cart.couponCode,
            total: cart.total,
            delivery_address: address,
            payment_method: payment,
            instructions: document.getElementById('del_instructions').value
        }),
        success: function(res) {
            if (res.success) {
                sessionStorage.removeItem(cartKey);
                document.getElementById('modal-order-number').textContent = 'Order #' + res.order_number;
                new bootstrap.Modal(document.getElementById('successModal')).show();
            } else {
                alert(res.message || 'Failed to place order.');
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Place Order';
                btn.disabled = false;
            }
        },
        error: function() {
            alert('Something went wrong. Please try again.');
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Place Order';
            btn.disabled = false;
        }
    });
}

loadCart();
</script>
</body>
</html>
