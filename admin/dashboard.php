<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit(); }

// ── Stats ────────────────────────────────────────────────
$stats = [];
$stats['customers']          = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$stats['restaurants_total']  = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$stats['restaurants_pending']= $pdo->query("SELECT COUNT(*) FROM restaurants WHERE is_approved=0 AND is_verified=1")->fetchColumn();
$stats['ngos_total']         = $pdo->query("SELECT COUNT(*) FROM ngos")->fetchColumn();
$stats['ngos_pending']       = $pdo->query("SELECT COUNT(*) FROM ngos WHERE is_approved=0 AND is_verified=1")->fetchColumn();
$stats['orders_today']       = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$stats['revenue_today']      = $pdo->query("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE order_status='delivered' AND DATE(created_at)=CURDATE()")->fetchColumn();
$stats['donations_pending']  = $pdo->query("SELECT COUNT(*) FROM food_donations WHERE status='pending'")->fetchColumn();

// ── Active tab ────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'overview';

// ── Pending restaurants ───────────────────────────────────
$pendingRest = $pdo->query("SELECT * FROM restaurants WHERE is_approved=0 AND is_verified=1 ORDER BY created_at DESC")->fetchAll();
$approvedRest= $pdo->query("SELECT r.*, COUNT(DISTINCT o.id) as order_count FROM restaurants r LEFT JOIN orders o ON r.id=o.restaurant_id WHERE r.is_approved=1 GROUP BY r.id ORDER BY r.created_at DESC")->fetchAll();

// ── Pending NGOs ──────────────────────────────────────────
$pendingNGO  = $pdo->query("SELECT * FROM ngos WHERE is_approved=0 AND is_verified=1 ORDER BY created_at DESC")->fetchAll();
$approvedNGO = $pdo->query("SELECT * FROM ngos WHERE is_approved=1 ORDER BY created_at DESC")->fetchAll();

// ── All customers ─────────────────────────────────────────
$customers   = $pdo->query("SELECT c.*, COUNT(o.id) as order_count FROM customers c LEFT JOIN orders o ON c.id=o.customer_id GROUP BY c.id ORDER BY c.created_at DESC LIMIT 50")->fetchAll();

// ── Recent orders ─────────────────────────────────────────
$recentOrders= $pdo->query("SELECT o.*, c.full_name as customer_name, r.restaurant_name FROM orders o JOIN customers c ON o.customer_id=c.id JOIN restaurants r ON o.restaurant_id=r.id ORDER BY o.created_at DESC LIMIT 30")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--accent:#27ae60;--dark:#1a1a2e;--sidebar:220px}
*{box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#f0f2f5;margin:0}
/* Sidebar */
.sidebar{position:fixed;left:0;top:0;width:var(--sidebar);height:100vh;background:var(--dark);z-index:200;display:flex;flex-direction:column;overflow-y:auto}
.sidebar .brand{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,.08)}
.sidebar .brand-title{font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--primary);margin:0}
.sidebar .brand-sub{font-size:.75rem;color:rgba(255,255,255,.4);margin:0}
.sidebar nav{padding:.75rem 0;flex:1}
.nav-item{display:block;padding:.65rem 1.5rem;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;transition:.2s;display:flex;align-items:center;gap:.75rem;border-right:3px solid transparent;position:relative}
.nav-item:hover{background:rgba(255,255,255,.06);color:#fff}
.nav-item.active{background:rgba(255,107,53,.15);color:var(--primary);border-right-color:var(--primary)}
.nav-item i{width:18px;text-align:center}
.nav-badge{margin-left:auto;background:var(--primary);color:#fff;border-radius:50px;font-size:.7rem;padding:.1rem .5rem;font-weight:700}
.nav-badge.green{background:var(--accent)}
.nav-section{padding:.5rem 1.5rem .25rem;font-size:.7rem;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:1.5px;font-weight:700}
/* Main */
.main{margin-left:var(--sidebar);min-height:100vh}
.topbar{background:#fff;padding:1rem 1.75rem;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,.06);position:sticky;top:0;z-index:100}
.topbar-title{font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:700;margin:0}
.content{padding:1.75rem}
/* Stat cards */
.stat-card{border-radius:16px;padding:1.5rem;color:#fff;position:relative;overflow:hidden;cursor:default}
.stat-card .icon{position:absolute;right:1.25rem;top:1.25rem;font-size:2.5rem;opacity:.2}
.stat-card p{margin:0 0 .25rem;font-size:.8rem;opacity:.85;font-weight:500;text-transform:uppercase;letter-spacing:.5px}
.stat-card h2{margin:0;font-size:2rem;font-weight:800}
/* Tables */
.data-card{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.5rem}
.data-card .card-header{padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;background:#fff}
.data-card .card-header h5{margin:0;font-weight:700;font-size:1rem}
.table{margin:0}
.table th{font-size:.78rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;background:#fafafa;border-bottom:2px solid #f0f0f0}
.table td{vertical-align:middle;font-size:.875rem;border-color:#f5f5f5}
/* Badges */
.badge-pending{background:#fff3e0;color:#e65100;padding:.3rem .75rem;border-radius:50px;font-size:.75rem;font-weight:600}
.badge-approved{background:#e8f5e9;color:#2e7d32;padding:.3rem .75rem;border-radius:50px;font-size:.75rem;font-weight:600}
.badge-rejected{background:#fbe9e7;color:#bf360c;padding:.3rem .75rem;border-radius:50px;font-size:.75rem;font-weight:600}
/* Tabs */
.tab-content-area{display:none}
.tab-content-area.active{display:block}
/* Action buttons */
.btn-approve{background:var(--accent);color:#fff;border:none;border-radius:50px;padding:.3rem .9rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:.2s}
.btn-approve:hover{background:#219a52}
.btn-reject{background:#e74c3c;color:#fff;border:none;border-radius:50px;padding:.3rem .9rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:.2s}
.btn-reject:hover{background:#c0392b}
.btn-revoke{background:#e67e22;color:#fff;border:none;border-radius:50px;padding:.3rem .9rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:.2s}
/* Toast */
.toast-container{position:fixed;top:1rem;right:1rem;z-index:9999}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="brand">
    <p class="brand-title">🛡️ FoodHub</p>
    <p class="brand-sub">Admin Panel</p>
  </div>
  <nav>
    <p class="nav-section">Main</p>
    <a class="nav-item <?= $tab==='overview'?'active':'' ?>" href="?tab=overview"><i class="fas fa-tachometer-alt"></i>Overview</a>
    <a class="nav-item <?= $tab==='orders'?'active':'' ?>" href="?tab=orders"><i class="fas fa-receipt"></i>Orders</a>

    <p class="nav-section">Approvals</p>
    <a class="nav-item <?= $tab==='restaurants'?'active':'' ?>" href="?tab=restaurants">
      <i class="fas fa-store"></i>Restaurants
      <?php if ($stats['restaurants_pending']>0): ?>
      <span class="nav-badge"><?= $stats['restaurants_pending'] ?></span>
      <?php endif; ?>
    </a>
    <a class="nav-item <?= $tab==='ngos'?'active':'' ?>" href="?tab=ngos">
      <i class="fas fa-hands-helping"></i>NGOs
      <?php if ($stats['ngos_pending']>0): ?>
      <span class="nav-badge green"><?= $stats['ngos_pending'] ?></span>
      <?php endif; ?>
    </a>

    <p class="nav-section">Users</p>
    <a class="nav-item <?= $tab==='customers'?'active':'' ?>" href="?tab=customers"><i class="fas fa-users"></i>Customers</a>
    <a class="nav-item <?= $tab==='donations'?'active':'' ?>" href="?tab=donations"><i class="fas fa-heart"></i>Donations</a>

    <p class="nav-section">Account</p>
    <a class="nav-item" href="admin_handler.php?logout=1"><i class="fas fa-sign-out-alt"></i>Logout</a>
  </nav>
  <div style="padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.08)">
    <p style="margin:0;font-size:.75rem;color:rgba(255,255,255,.35)">Logged in as</p>
    <p style="margin:0;font-size:.85rem;color:rgba(255,255,255,.7);font-weight:600"><?= htmlspecialchars($_SESSION['admin_name']) ?></p>
  </div>
</aside>

<!-- Main Content -->
<div class="main">
  <!-- Topbar -->
  <div class="topbar">
    <h1 class="topbar-title">
      <?php
      $titles = ['overview'=>'Dashboard Overview','orders'=>'Order Management','restaurants'=>'Restaurant Management','ngos'=>'NGO Management','customers'=>'Customer Management','donations'=>'Food Donations'];
      echo $titles[$tab] ?? 'Dashboard';
      ?>
    </h1>
    <div class="d-flex align-items-center gap-3">
      <?php if ($stats['restaurants_pending']>0): ?>
      <a href="?tab=restaurants" class="btn btn-sm rounded-pill" style="background:#fff3e0;color:#e65100;font-weight:600">
        <i class="fas fa-bell me-1"></i><?= $stats['restaurants_pending'] ?> pending approval<?= $stats['restaurants_pending']>1?'s':'' ?>
      </a>
      <?php endif; ?>
      <span class="text-muted small"><?= date('d M Y, h:i A') ?></span>
    </div>
  </div>

  <div class="content">

  <!-- ═══════════════ OVERVIEW ═══════════════ -->
  <?php if ($tab === 'overview'): ?>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,var(--primary),var(--secondary))">
        <p>Total Customers</p><h2><?= number_format($stats['customers']) ?></h2>
        <i class="fas fa-users icon"></i>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#74b9ff)">
        <p>Restaurants</p><h2><?= $stats['restaurants_total'] ?> <small style="font-size:1rem">(<?= $stats['restaurants_pending'] ?> pending)</small></h2>
        <i class="fas fa-store icon"></i>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,var(--accent),#2ecc71)">
        <p>Today's Revenue</p><h2>₹<?= number_format($stats['revenue_today']) ?></h2>
        <i class="fas fa-rupee-sign icon"></i>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#e91e63,#f06292)">
        <p>Today's Orders</p><h2><?= $stats['orders_today'] ?></h2>
        <i class="fas fa-receipt icon"></i>
      </div>
    </div>
  </div>

  <!-- Pending approvals alert -->
  <?php if ($stats['restaurants_pending'] > 0 || $stats['ngos_pending'] > 0): ?>
  <div class="alert rounded-3 d-flex align-items-center gap-3 mb-4" style="background:#fff3e0;border:1px solid #ffcc80;color:#e65100">
    <i class="fas fa-exclamation-triangle fa-lg"></i>
    <div>
      <strong>Pending Approvals:</strong>
      <?php if ($stats['restaurants_pending']>0): ?>
      <a href="?tab=restaurants" class="fw-bold ms-2" style="color:var(--primary)"><?= $stats['restaurants_pending'] ?> restaurant<?= $stats['restaurants_pending']>1?'s':'' ?></a>
      <?php endif; ?>
      <?php if ($stats['ngos_pending']>0): ?>
      <a href="?tab=ngos" class="fw-bold ms-2" style="color:var(--accent)"><?= $stats['ngos_pending'] ?> NGO<?= $stats['ngos_pending']>1?'s':'' ?></a>
      <?php endif; ?>
      waiting for your review.
    </div>
  </div>
  <?php endif; ?>

  <!-- Quick tables -->
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="data-card">
        <div class="card-header">
          <h5><i class="fas fa-clock me-2" style="color:var(--primary)"></i>Pending Restaurants</h5>
          <a href="?tab=restaurants" class="btn btn-sm btn-outline-secondary rounded-pill">View All</a>
        </div>
        <table class="table">
          <thead><tr><th>Restaurant</th><th>City</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($pendingRest)): ?>
            <tr><td colspan="3" class="text-center text-muted py-3">No pending restaurants 🎉</td></tr>
            <?php else: ?>
            <?php foreach (array_slice($pendingRest,0,5) as $r): ?>
            <tr id="rest-row-<?= $r['id'] ?>">
              <td><strong><?= htmlspecialchars($r['restaurant_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['owner_name']) ?></small></td>
              <td><?= htmlspecialchars($r['city']) ?></td>
              <td>
                <button class="btn-approve" onclick="approve('restaurant',<?= $r['id'] ?>,'approve')">✓ Approve</button>
                <button class="btn-reject ms-1" onclick="approve('restaurant',<?= $r['id'] ?>,'reject')">✗</button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="data-card">
        <div class="card-header">
          <h5><i class="fas fa-receipt me-2" style="color:var(--primary)"></i>Recent Orders</h5>
          <a href="?tab=orders" class="btn btn-sm btn-outline-secondary rounded-pill">View All</a>
        </div>
        <table class="table">
          <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($recentOrders,0,6) as $o): ?>
            <tr>
              <td class="fw-600"><?= htmlspecialchars($o['order_number']) ?></td>
              <td><?= htmlspecialchars($o['customer_name']) ?></td>
              <td>₹<?= number_format($o['final_amount'],0) ?></td>
              <td><span class="badge-<?= in_array($o['order_status'],['delivered']) ? 'approved' : 'pending' ?>"><?= ucwords(str_replace('_',' ',$o['order_status'])) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ═══════════════ RESTAURANTS ═══════════════ -->
  <?php elseif ($tab === 'restaurants'): ?>

  <?php if (!empty($pendingRest)): ?>
  <div class="data-card mb-4">
    <div class="card-header" style="background:#fff8f0">
      <h5><i class="fas fa-hourglass-half me-2" style="color:#e65100"></i>Pending Approval <span class="badge ms-2 rounded-pill" style="background:var(--primary)"><?= count($pendingRest) ?></span></h5>
    </div>
    <table class="table">
      <thead><tr><th>Restaurant</th><th>Owner</th><th>Email</th><th>Phone</th><th>City</th><th>Cuisine</th><th>Registered</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($pendingRest as $r): ?>
        <tr id="rest-row-<?= $r['id'] ?>">
          <td><strong><?= htmlspecialchars($r['restaurant_name']) ?></strong></td>
          <td><?= htmlspecialchars($r['owner_name']) ?></td>
          <td><a href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a></td>
          <td><?= htmlspecialchars($r['phone']) ?></td>
          <td><?= htmlspecialchars($r['city']) ?></td>
          <td><?= htmlspecialchars($r['cuisine_type']) ?></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td>
            <button class="btn-approve" onclick="approve('restaurant',<?= $r['id'] ?>,'approve')"><i class="fas fa-check me-1"></i>Approve</button>
            <button class="btn-reject ms-1" onclick="approve('restaurant',<?= $r['id'] ?>,'reject')"><i class="fas fa-times me-1"></i>Reject</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <div class="data-card">
    <div class="card-header">
      <h5><i class="fas fa-check-circle me-2" style="color:var(--accent)"></i>Approved Restaurants (<?= count($approvedRest) ?>)</h5>
    </div>
    <table class="table">
      <thead><tr><th>Restaurant</th><th>Owner</th><th>City</th><th>Rating</th><th>Orders</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($approvedRest)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No approved restaurants yet.</td></tr>
        <?php else: ?>
        <?php foreach ($approvedRest as $r): ?>
        <tr id="rest-row-<?= $r['id'] ?>">
          <td><strong><?= htmlspecialchars($r['restaurant_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['cuisine_type']) ?></small></td>
          <td><?= htmlspecialchars($r['owner_name']) ?></td>
          <td><?= htmlspecialchars($r['city']) ?></td>
          <td>⭐ <?= $r['rating'] ?></td>
          <td><?= $r['order_count'] ?></td>
          <td><button class="btn-revoke" onclick="approve('restaurant',<?= $r['id'] ?>,'revoke')">Revoke</button></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ═══════════════ NGOS ═══════════════ -->
  <?php elseif ($tab === 'ngos'): ?>

  <?php if (!empty($pendingNGO)): ?>
  <div class="data-card mb-4">
    <div class="card-header" style="background:#f0fff4">
      <h5><i class="fas fa-hourglass-half me-2" style="color:var(--accent)"></i>Pending NGO Approval <span class="badge ms-2 rounded-pill" style="background:var(--accent)"><?= count($pendingNGO) ?></span></h5>
    </div>
    <table class="table">
      <thead><tr><th>NGO Name</th><th>Contact</th><th>Email</th><th>Phone</th><th>City</th><th>Reg. No.</th><th>Registered</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($pendingNGO as $n): ?>
        <tr id="ngo-row-<?= $n['id'] ?>">
          <td><strong><?= htmlspecialchars($n['ngo_name']) ?></strong></td>
          <td><?= htmlspecialchars($n['contact_person']) ?></td>
          <td><a href="mailto:<?= htmlspecialchars($n['email']) ?>"><?= htmlspecialchars($n['email']) ?></a></td>
          <td><?= htmlspecialchars($n['phone']) ?></td>
          <td><?= htmlspecialchars($n['city']) ?></td>
          <td><?= htmlspecialchars($n['registration_number'] ?: '—') ?></td>
          <td><?= date('d M Y', strtotime($n['created_at'])) ?></td>
          <td>
            <button class="btn-approve" onclick="approve('ngo',<?= $n['id'] ?>,'approve')"><i class="fas fa-check me-1"></i>Approve</button>
            <button class="btn-reject ms-1" onclick="approve('ngo',<?= $n['id'] ?>,'reject')"><i class="fas fa-times me-1"></i>Reject</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <div class="data-card">
    <div class="card-header">
      <h5><i class="fas fa-check-circle me-2" style="color:var(--accent)"></i>Approved NGOs (<?= count($approvedNGO) ?>)</h5>
    </div>
    <table class="table">
      <thead><tr><th>NGO Name</th><th>Contact</th><th>City</th><th>Reg. No.</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($approvedNGO)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No approved NGOs yet.</td></tr>
        <?php else: ?>
        <?php foreach ($approvedNGO as $n): ?>
        <tr id="ngo-row-<?= $n['id'] ?>">
          <td><strong><?= htmlspecialchars($n['ngo_name']) ?></strong></td>
          <td><?= htmlspecialchars($n['contact_person']) ?></td>
          <td><?= htmlspecialchars($n['city']) ?></td>
          <td><?= htmlspecialchars($n['registration_number'] ?: '—') ?></td>
          <td><button class="btn-revoke" onclick="approve('ngo',<?= $n['id'] ?>,'revoke')">Revoke</button></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ═══════════════ CUSTOMERS ═══════════════ -->
  <?php elseif ($tab === 'customers'): ?>
  <div class="data-card">
    <div class="card-header">
      <h5><i class="fas fa-users me-2" style="color:var(--primary)"></i>All Customers (<?= count($customers) ?>)</h5>
    </div>
    <table class="table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Orders</th><th>Verified</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['phone']) ?></td>
          <td><?= htmlspecialchars($c['city'] ?: '—') ?></td>
          <td><?= $c['order_count'] ?></td>
          <td><?= $c['is_verified'] ? '<span class="badge-approved">Verified</span>' : '<span class="badge-pending">Unverified</span>' ?></td>
          <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ═══════════════ ORDERS ═══════════════ -->
  <?php elseif ($tab === 'orders'): ?>
  <div class="data-card">
    <div class="card-header">
      <h5><i class="fas fa-receipt me-2" style="color:var(--primary)"></i>All Orders (<?= count($recentOrders) ?> recent)</h5>
    </div>
    <table class="table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Restaurant</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td class="fw-600"><?= htmlspecialchars($o['order_number']) ?></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td><?= htmlspecialchars($o['restaurant_name']) ?></td>
          <td class="fw-600" style="color:var(--accent)">₹<?= number_format($o['final_amount'],2) ?></td>
          <td><?= ucfirst($o['payment_method']) ?></td>
          <td><span class="badge-<?= $o['order_status']==='delivered'?'approved':($o['order_status']==='cancelled'?'rejected':'pending') ?>"><?= ucwords(str_replace('_',' ',$o['order_status'])) ?></span></td>
          <td><?= date('d M y, h:i A', strtotime($o['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ═══════════════ DONATIONS ═══════════════ -->
  <?php elseif ($tab === 'donations'): ?>
  <?php
  $allDonations = $pdo->query("SELECT d.*, n.ngo_name FROM food_donations d LEFT JOIN ngos n ON d.ngo_id=n.id ORDER BY d.created_at DESC LIMIT 50")->fetchAll();
  ?>
  <div class="data-card">
    <div class="card-header">
      <h5><i class="fas fa-heart me-2" style="color:#e91e63"></i>Food Donations (<?= count($allDonations) ?> recent)</h5>
    </div>
    <table class="table">
      <thead><tr><th>Donor</th><th>Food</th><th>Qty</th><th>Serves</th><th>City</th><th>Pickup Date</th><th>NGO</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($allDonations as $d): ?>
        <tr>
          <td><strong><?= htmlspecialchars($d['donor_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($d['donor_phone']) ?></small></td>
          <td><?= htmlspecialchars($d['food_type']) ?></td>
          <td><?= htmlspecialchars($d['food_quantity']) ?></td>
          <td><?= $d['serves_people'] ?> people</td>
          <td><?= htmlspecialchars($d['pickup_city']) ?></td>
          <td><?= date('d M Y', strtotime($d['pickup_date'])) ?></td>
          <td><?= htmlspecialchars($d['ngo_name'] ?: 'Auto-assign') ?></td>
          <td><span class="badge-<?= $d['status']==='completed'?'approved':($d['status']==='cancelled'?'rejected':'pending') ?>"><?= ucwords($d['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  </div><!-- /content -->
</div><!-- /main -->

<!-- Toast -->
<div class="toast-container">
  <div id="toast" class="toast align-items-center border-0 text-white" role="alert" style="min-width:280px">
    <div class="d-flex">
      <div class="toast-body fw-600" id="toast-msg"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
function showToast(msg, success) {
    const t = document.getElementById('toast');
    t.style.background = success ? '#27ae60' : '#e74c3c';
    document.getElementById('toast-msg').textContent = msg;
    new bootstrap.Toast(t, {delay: 3000}).show();
}

function approve(type, id, action) {
    const labels = {approve:'Approve',reject:'Reject',revoke:'Revoke'};
    if (!confirm(labels[action] + ' this ' + type + '?')) return;

    $.post('admin_handler.php', {type, id, action}, function(res) {
        if (res.success) {
            showToast(res.message, true);
            const row = document.getElementById(type + '-row-' + id);
            if (row) {
                row.style.transition = 'opacity .4s';
                row.style.opacity = '0';
                setTimeout(() => { row.remove(); }, 400);
            }
            // Update sidebar badge
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(res.message || 'Error', false);
        }
    }, 'json').fail(function(xhr) {
        showToast('Request failed: ' + (xhr.responseText || 'unknown error'), false);
    });
}
</script>
</body>
</html>
