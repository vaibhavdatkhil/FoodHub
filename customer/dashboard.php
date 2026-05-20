<?php
require_once '../includes/config.php';
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$cid = $_SESSION['customer_id'];
// Fetch customer info
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$cid]);
$customer = $stmt->fetch();

// Fetch recent orders
$stmt = $pdo->prepare("
    SELECT o.*, r.restaurant_name 
    FROM orders o 
    JOIN restaurants r ON o.restaurant_id = r.id 
    WHERE o.customer_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$cid]);
$recent_orders = $stmt->fetchAll();

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(final_amount) as spent FROM orders WHERE customer_id = ? AND order_status = 'delivered'");
$stmt->execute([$cid]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Dashboard – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--accent:#27ae60;--dark:#1a1a2e;--light:#fff8f5}
body{font-family:'DM Sans',sans-serif;background:#f8f9fa}
.navbar-brand{font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--primary)!important}
.sidebar{background:var(--dark);min-height:calc(100vh - 70px);padding:2rem 0}
.sidebar .nav-link{color:rgba(255,255,255,.7);padding:.75rem 1.5rem;border-radius:0 50px 50px 0;margin-right:1rem;transition:.3s}
.sidebar .nav-link:hover,.sidebar .nav-link.active{background:var(--primary);color:#fff}
.sidebar .nav-link i{width:20px}
.stat-card{background:#fff;border-radius:16px;padding:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,.06);border-left:4px solid var(--primary)}
.order-card{background:#fff;border-radius:16px;padding:1.25rem;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:1rem;transition:.3s}
.order-card:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.1)}
.status-badge{padding:.35rem .85rem;border-radius:50px;font-size:.78rem;font-weight:600}
.badge-placed{background:#fff3e0;color:#e65100}
.badge-confirmed{background:#e3f2fd;color:#1565c0}
.badge-preparing{background:#fce4ec;color:#ad1457}
.badge-out_for_delivery{background:#e8f5e9;color:#2e7d32}
.badge-delivered{background:#e8f5e9;color:#1b5e20}
.badge-cancelled{background:#fbe9e7;color:#bf360c}
.avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;font-family:'Playfair Display',serif}
</style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="../index.php">🍽️ FoodHub</a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <a href="../index.php" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-utensils me-1"></i>Order Food</a>
      <div class="dropdown">
        <button class="btn btn-sm rounded-pill d-flex align-items-center gap-2" style="background:var(--light);border:1px solid #eee" data-bs-toggle="dropdown">
          <i class="fas fa-user-circle" style="color:var(--primary)"></i>
          <?= htmlspecialchars($customer['full_name']) ?>
          <i class="fas fa-chevron-down small"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="orders.php"><i class="fas fa-receipt me-2 text-muted"></i>My Orders</a></li>
          <li><a class="dropdown-item" href="../donate/donate_food.php"><i class="fas fa-heart me-2 text-success"></i>Donate Food</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="../auth/auth_handler.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-lg-2 p-0 sidebar">
      <nav class="nav flex-column mt-2">
        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a class="nav-link" href="orders.php"><i class="fas fa-receipt me-2"></i>My Orders</a>
        <a class="nav-link" href="../index.php"><i class="fas fa-search me-2"></i>Browse Restaurants</a>
        <a class="nav-link" href="../donate/donate_food.php"><i class="fas fa-heart me-2"></i>Donate Food</a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a>
        <hr style="border-color:rgba(255,255,255,.1);margin:.5rem 1rem">
        <a class="nav-link text-danger" href="../auth/auth_handler.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="col-lg-10 p-4">
      <!-- Welcome -->
      <div class="d-flex align-items-center gap-3 mb-4">
        <div class="avatar"><?= strtoupper(substr($customer['full_name'],0,1)) ?></div>
        <div>
          <h3 class="mb-0 fw-700" style="font-family:'Playfair Display',serif">Hello, <?= htmlspecialchars(explode(' ',$customer['full_name'])[0]) ?>! 👋</h3>
          <p class="text-muted mb-0"><?= htmlspecialchars($customer['email']) ?> · <?= htmlspecialchars($customer['city']) ?></p>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">Total Orders</p>
                <h3 class="fw-bold mb-0"><?= $stats['total'] ?? 0 ?></h3>
              </div>
              <i class="fas fa-shopping-bag fa-2x" style="color:var(--primary);opacity:.3"></i>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" style="border-color:var(--accent)">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">Total Spent</p>
                <h3 class="fw-bold mb-0">₹<?= number_format($stats['spent'] ?? 0) ?></h3>
              </div>
              <i class="fas fa-rupee-sign fa-2x" style="color:var(--accent);opacity:.3"></i>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" style="border-color:#e91e63">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">Food Donated</p>
                <h3 class="fw-bold mb-0"><?php
                  $ds = $pdo->prepare("SELECT COUNT(*) FROM food_donations WHERE donor_email=?");
                  $ds->execute([$customer['email']]);
                  echo $ds->fetchColumn();
                ?></h3>
              </div>
              <i class="fas fa-heart fa-2x" style="color:#e91e63;opacity:.3"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="font-family:'Playfair Display',serif">Recent Orders</h5>
        <a href="orders.php" class="btn btn-sm rounded-pill" style="background:var(--light);color:var(--primary);border:1px solid var(--primary)">View All</a>
      </div>

      <?php if (empty($recent_orders)): ?>
      <div class="text-center py-5">
        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
        <p class="text-muted">No orders yet. Start exploring restaurants!</p>
        <a href="../index.php" class="btn rounded-pill px-4" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff">Browse Restaurants</a>
      </div>
      <?php else: ?>
      <?php foreach ($recent_orders as $order): ?>
      <div class="order-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="fw-bold mb-1"><?= htmlspecialchars($order['restaurant_name']) ?></h6>
            <p class="text-muted small mb-1">Order #<?= htmlspecialchars($order['order_number']) ?></p>
            <p class="text-muted small mb-0"><i class="far fa-clock me-1"></i><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
          </div>
          <div class="text-end">
            <span class="status-badge badge-<?= $order['order_status'] ?>"><?= ucwords(str_replace('_',' ',$order['order_status'])) ?></span>
            <p class="fw-bold mt-2 mb-0">₹<?= number_format($order['final_amount'],2) ?></p>
          </div>
        </div>
        <div class="mt-2 d-flex gap-2">
          <a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill">View Details</a>
          <?php if ($order['order_status'] === 'delivered'): ?>
          <a href="../restaurant_menu.php?id=<?= $order['restaurant_id'] ?>" class="btn btn-sm rounded-pill" style="background:var(--primary);color:#fff">Reorder</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
