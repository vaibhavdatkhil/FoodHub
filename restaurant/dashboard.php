<?php
require_once '../includes/config.php';
if (!isset($_SESSION['restaurant_id'])) {
    header('Location: ../auth/login.php?tab=restaurant');
    exit;
}
$rid = $_SESSION['restaurant_id'];
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id=?");
$stmt->execute([$rid]);
$restaurant = $stmt->fetch();

// Stats
$stats = [];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id=? AND DATE(created_at)=CURDATE()");
$stmt->execute([$rid]); $stats['today_orders'] = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE restaurant_id=? AND order_status='delivered' AND DATE(created_at)=CURDATE()");
$stmt->execute([$rid]); $stats['today_revenue'] = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id=? AND order_status IN ('placed','confirmed','preparing','out_for_delivery')");
$stmt->execute([$rid]); $stats['active_orders'] = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id=?");
$stmt->execute([$rid]); $stats['menu_items'] = $stmt->fetchColumn();

// Active orders
$stmt = $pdo->prepare("
    SELECT o.*, c.full_name as customer_name, c.phone as customer_phone
    FROM orders o JOIN customers c ON o.customer_id=c.id
    WHERE o.restaurant_id=? AND o.order_status IN ('placed','confirmed','preparing','out_for_delivery')
    ORDER BY o.created_at ASC
");
$stmt->execute([$rid]);
$active_orders = $stmt->fetchAll();

// Recent completed
$stmt = $pdo->prepare("
    SELECT o.*, c.full_name as customer_name 
    FROM orders o JOIN customers c ON o.customer_id=c.id
    WHERE o.restaurant_id=? AND o.order_status='delivered'
    ORDER BY o.created_at DESC LIMIT 8
");
$stmt->execute([$rid]);
$completed = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Restaurant Dashboard – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--accent:#27ae60;--dark:#1a1a2e}
body{font-family:'DM Sans',sans-serif;background:#f0f2f5}
.sidebar{background:var(--dark);width:240px;min-height:100vh;position:fixed;left:0;top:0;z-index:100;padding-top:1rem}
.sidebar .brand{font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--primary);padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:.5rem}
.sidebar .nav-link{color:rgba(255,255,255,.7);padding:.7rem 1.5rem;border-radius:0 50px 50px 0;margin-right:1rem;transition:.3s;font-size:.9rem}
.sidebar .nav-link:hover,.sidebar .nav-link.active{background:var(--primary);color:#fff}
.sidebar .nav-link i{width:20px}
.main-content{margin-left:240px;padding:2rem}
.stat-card{border-radius:16px;padding:1.5rem;color:#fff;position:relative;overflow:hidden}
.stat-card::after{content:'';position:absolute;right:-20px;top:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.1)}
.order-card{background:#fff;border-radius:16px;padding:1.25rem;box-shadow:0 4px 20px rgba(0,0,0,.07);margin-bottom:1rem}
.status-select{border:none;font-weight:600;font-size:.85rem;padding:.3rem .8rem;border-radius:50px;cursor:pointer}
.status-placed{background:#fff3e0;color:#e65100}
.status-confirmed{background:#e3f2fd;color:#1565c0}
.status-preparing{background:#fce4ec;color:#ad1457}
.status-out_for_delivery{background:#e8f5e9;color:#2e7d32}
.status-delivered{background:#e8f5e9;color:#1b5e20}
.tab-content-area{display:none}
.tab-content-area.active{display:block}
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
  <div class="brand">🍽️ FoodHub</div>
  <p class="text-center small" style="color:rgba(255,255,255,.5);font-size:.75rem;margin-bottom:1rem"><?= htmlspecialchars($restaurant['restaurant_name']) ?></p>
  <nav class="nav flex-column">
    <a class="nav-link active" href="#" onclick="showTab('dashboard')"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a class="nav-link" href="#" onclick="showTab('orders')"><i class="fas fa-receipt me-2"></i>Active Orders <span class="badge ms-1 rounded-pill" style="background:var(--primary)"><?= $stats['active_orders'] ?></span></a>
    <a class="nav-link" href="#" onclick="showTab('menu')"><i class="fas fa-utensils me-2"></i>Menu Management</a>
    <a class="nav-link" href="#" onclick="showTab('history')"><i class="fas fa-history me-2"></i>Order History</a>
    <a class="nav-link" href="#" onclick="showTab('profile')"><i class="fas fa-store me-2"></i>Restaurant Profile</a>
    <hr style="border-color:rgba(255,255,255,.1);margin:.5rem 1rem">
    <a class="nav-link text-danger" href="../auth/auth_handler.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
  </nav>
</div>

<div class="main-content">
  <!-- Dashboard Tab -->
  <div id="tab-dashboard" class="tab-content-area active">
    <h4 class="fw-bold mb-1" style="font-family:'Playfair Display',serif">Good day, <?= htmlspecialchars($restaurant['restaurant_name']) ?>! 👋</h4>
    <p class="text-muted mb-4">Here's what's happening at your restaurant today.</p>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,var(--primary),var(--secondary))">
          <p class="mb-1 opacity-75 small">Today's Orders</p>
          <h2 class="fw-bold mb-0"><?= $stats['today_orders'] ?></h2>
          <i class="fas fa-receipt position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#2ecc71)">
          <p class="mb-1 opacity-75 small">Today's Revenue</p>
          <h2 class="fw-bold mb-0">₹<?= number_format($stats['today_revenue']) ?></h2>
          <i class="fas fa-rupee-sign position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e91e63,#f06292)">
          <p class="mb-1 opacity-75 small">Active Orders</p>
          <h2 class="fw-bold mb-0"><?= $stats['active_orders'] ?></h2>
          <i class="fas fa-fire position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#74b9ff)">
          <p class="mb-1 opacity-75 small">Menu Items</p>
          <h2 class="fw-bold mb-0"><?= $stats['menu_items'] ?></h2>
          <i class="fas fa-utensils position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="order-card text-center" style="cursor:pointer" onclick="showTab('orders')">
          <i class="fas fa-bell fa-2x mb-2" style="color:var(--primary)"></i>
          <h6 class="fw-bold mb-0"><?= $stats['active_orders'] ?> Active Orders</h6>
          <p class="text-muted small mb-0">Tap to manage</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="order-card text-center" style="cursor:pointer" onclick="showTab('menu')">
          <i class="fas fa-plus-circle fa-2x mb-2" style="color:var(--accent)"></i>
          <h6 class="fw-bold mb-0">Add Menu Item</h6>
          <p class="text-muted small mb-0">Expand your menu</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="order-card text-center" style="cursor:pointer" onclick="showTab('history')">
          <i class="fas fa-chart-line fa-2x mb-2" style="color:#3498db"></i>
          <h6 class="fw-bold mb-0">View Sales</h6>
          <p class="text-muted small mb-0">Order history</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders Tab -->
  <div id="tab-orders" class="tab-content-area">
    <h4 class="fw-bold mb-4" style="font-family:'Playfair Display',serif">Active Orders</h4>
    <?php if (empty($active_orders)): ?>
    <div class="text-center py-5 bg-white rounded-4">
      <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
      <h5>All clear! No active orders.</h5>
      <p class="text-muted">New orders will appear here.</p>
    </div>
    <?php else: ?>
    <div class="row g-3" id="orders-grid">
      <?php foreach ($active_orders as $o): ?>
      <div class="col-lg-6" id="order-<?= $o['id'] ?>">
        <div class="order-card">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h6 class="fw-bold mb-0"><?= htmlspecialchars($o['customer_name']) ?></h6>
              <small class="text-muted">📞 <?= htmlspecialchars($o['customer_phone']) ?> · #<?= htmlspecialchars($o['order_number']) ?></small>
            </div>
            <span class="fw-bold" style="color:var(--primary)">₹<?= number_format($o['final_amount'],2) ?></span>
          </div>
          <div class="mb-2">
            <small class="text-muted"><i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($o['created_at'])) ?></small>
            <?php if ($o['delivery_address']): ?>
            <small class="text-muted d-block mt-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(substr($o['delivery_address'],0,60)).(strlen($o['delivery_address'])>60?'...':'') ?></small>
            <?php endif; ?>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Status:</span>
            <select class="status-select status-<?= $o['order_status'] ?>" onchange="updateStatus(<?= $o['id'] ?>, this.value, this)">
              <option value="placed" <?= $o['order_status']==='placed'?'selected':'' ?>>Placed</option>
              <option value="confirmed" <?= $o['order_status']==='confirmed'?'selected':'' ?>>Confirmed</option>
              <option value="preparing" <?= $o['order_status']==='preparing'?'selected':'' ?>>Preparing</option>
              <option value="out_for_delivery" <?= $o['order_status']==='out_for_delivery'?'selected':'' ?>>Out for Delivery</option>
              <option value="delivered" <?= $o['order_status']==='delivered'?'selected':'' ?>>Delivered</option>
            </select>
          </div>
          <div class="mt-2" id="items-<?= $o['id'] ?>">
            <button class="btn btn-sm btn-link p-0 text-muted" onclick="loadOrderItems(<?= $o['id'] ?>, this)">
              <i class="fas fa-chevron-down me-1"></i>View items
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Menu Tab -->
  <div id="tab-menu" class="tab-content-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0" style="font-family:'Playfair Display',serif">Menu Management</h4>
      <button class="btn rounded-pill fw-600" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="fas fa-plus me-2"></i>Add Item
      </button>
    </div>
    <div id="menu-list">
      <div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted"></i></div>
    </div>
  </div>

  <!-- History Tab -->
  <div id="tab-history" class="tab-content-area">
    <h4 class="fw-bold mb-4" style="font-family:'Playfair Display',serif">Order History</h4>
    <div class="bg-white rounded-4 overflow-hidden shadow-sm">
      <table class="table table-hover mb-0">
        <thead style="background:#f8f9fa">
          <tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Date</th><th>Payment</th></tr>
        </thead>
        <tbody>
          <?php foreach ($completed as $o): ?>
          <tr>
            <td class="fw-600"><?= htmlspecialchars($o['order_number']) ?></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td class="fw-600" style="color:var(--accent)">₹<?= number_format($o['final_amount'],2) ?></td>
            <td><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
            <td><?= ucfirst($o['payment_method']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($completed)): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No completed orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Profile Tab -->
  <div id="tab-profile" class="tab-content-area">
    <h4 class="fw-bold mb-4" style="font-family:'Playfair Display',serif">Restaurant Profile</h4>
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="order-card">
          <h6 class="fw-bold mb-3"><i class="fas fa-store me-2" style="color:var(--primary)"></i>Basic Info</h6>
          <table class="table table-borderless table-sm">
            <tr><td class="text-muted">Restaurant Name</td><td class="fw-600"><?= htmlspecialchars($restaurant['restaurant_name']) ?></td></tr>
            <tr><td class="text-muted">Owner</td><td class="fw-600"><?= htmlspecialchars($restaurant['owner_name']) ?></td></tr>
            <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($restaurant['email']) ?></td></tr>
            <tr><td class="text-muted">Phone</td><td><?= htmlspecialchars($restaurant['phone']) ?></td></tr>
            <tr><td class="text-muted">City</td><td><?= htmlspecialchars($restaurant['city']) ?></td></tr>
            <tr><td class="text-muted">Cuisine</td><td><?= htmlspecialchars($restaurant['cuisine_type']) ?></td></tr>
            <tr><td class="text-muted">Rating</td><td>⭐ <?= $restaurant['rating'] ?>/5</td></tr>
            <tr><td class="text-muted">Status</td><td><span class="badge" style="background:<?= $restaurant['is_approved'] ? 'var(--accent)' : '#e67e22' ?>"><?= $restaurant['is_approved'] ? 'Approved' : 'Pending Approval' ?></span></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" style="font-family:'Playfair Display',serif">Add Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-600 small">Item Name *</label>
            <input type="text" class="form-control rounded-3" id="item_name">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-600 small">Price (₹) *</label>
            <input type="number" class="form-control rounded-3" id="item_price" min="0">
          </div>
          <div class="col-12">
            <label class="form-label fw-600 small">Description</label>
            <textarea class="form-control rounded-3" id="item_desc" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600 small">Category</label>
            <select class="form-select rounded-3" id="item_category">
              <?php
              $cats = $pdo->prepare("SELECT * FROM menu_categories WHERE restaurant_id=?");
              $cats->execute([$rid]);
              foreach ($cats->fetchAll() as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-600 small">Prep Time (min)</label>
            <input type="number" class="form-control rounded-3" id="item_prep" value="20" min="5">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-600 small">Type</label>
            <select class="form-select rounded-3" id="item_veg">
              <option value="1">🟢 Veg</option>
              <option value="0">🔴 Non-Veg</option>
            </select>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="item_bestseller">
              <label class="form-check-label fw-600">Mark as Bestseller ⭐</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button class="btn rounded-pill fw-600 px-4" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff" onclick="saveMenuItem()">
          <i class="fas fa-plus me-2"></i>Add Item
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
function showTab(name) {
    document.querySelectorAll('.tab-content-area').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    if (name === 'menu') loadMenuItems();
}

function updateStatus(orderId, newStatus, sel) {
    sel.className = 'status-select status-' + newStatus;
    $.post('../api/restaurant_handler.php', {action:'update_status', order_id:orderId, status:newStatus}, function(res) {
        if (res.success && (newStatus === 'delivered' || newStatus === 'cancelled')) {
            setTimeout(() => $('#order-' + orderId).fadeOut(400, function(){ $(this).remove(); }), 1000);
        }
    }, 'json');
}

function loadOrderItems(orderId, btn) {
    $(btn).hide();
    $.get('../api/restaurant_handler.php?action=get_order_items&order_id=' + orderId, function(res) {
        let html = '<ul class="list-unstyled small mt-1 mb-0">';
        res.forEach(i => { html += `<li class="text-muted">• ${i.item_name} × ${i.quantity} — <strong>₹${i.subtotal}</strong></li>`; });
        html += '</ul>';
        $('#items-' + orderId).append(html);
    }, 'json');
}

function loadMenuItems() {
    $.get('../api/restaurant_handler.php?action=get_menu', function(items) {
        if (!items.length) {
            $('#menu-list').html('<div class="text-center py-4 text-muted">No menu items yet. Add your first item!</div>');
            return;
        }
        let html = '<div class="row g-3">';
        items.forEach(item => {
            const dot = item.is_veg == 1 ? '🟢' : '🔴';
            const bs = item.is_bestseller == 1 ? '<span class="badge ms-1" style="background:var(--secondary);font-size:.7rem">⭐ Best</span>' : '';
            html += `<div class="col-md-4"><div class="order-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div><h6 class="fw-bold mb-0">${dot} ${item.name}${bs}</h6>
                    <small class="text-muted">${item.description || ''}</small></div>
                    <span class="fw-bold" style="color:var(--primary)">₹${item.price}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <small class="text-muted"><i class="fas fa-clock me-1"></i>${item.prep_time} min</small>
                    <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteItem(${item.id}, this)"><i class="fas fa-trash"></i></button>
                </div>
            </div></div>`;
        });
        html += '</div>';
        $('#menu-list').html(html);
    }, 'json');
}

function saveMenuItem() {
    $.post('../api/restaurant_handler.php', {
        action: 'add_menu_item',
        name: $('#item_name').val(),
        price: $('#item_price').val(),
        description: $('#item_desc').val(),
        category_id: $('#item_category').val(),
        prep_time: $('#item_prep').val(),
        is_veg: $('#item_veg').val(),
        is_bestseller: $('#item_bestseller').is(':checked') ? 1 : 0
    }, function(res) {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
            loadMenuItems();
        } else alert(res.message || 'Error saving item');
    }, 'json');
}

function deleteItem(id, btn) {
    if (!confirm('Delete this menu item?')) return;
    $.post('../api/restaurant_handler.php', {action:'delete_menu_item', item_id:id}, function(res) {
        if (res.success) $(btn).closest('.col-md-4').fadeOut();
    }, 'json');
}
</script>
</body>
</html>
