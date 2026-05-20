<?php
require_once '../includes/config.php';
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$cid = $_SESSION['customer_id'];
$order_detail = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT o.*, r.restaurant_name, r.address as rest_address
        FROM orders o 
        JOIN restaurants r ON o.restaurant_id = r.id 
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt->execute([$_GET['id'], $cid]);
    $order_detail = $stmt->fetch();

    if ($order_detail) {
        $stmt2 = $pdo->prepare("
            SELECT oi.*, mi.name as item_name, mi.is_veg 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?
        ");
        $stmt2->execute([$order_detail['id']]);
        $order_items = $stmt2->fetchAll();
    }
}

// All orders
$stmt = $pdo->prepare("
    SELECT o.*, r.restaurant_name 
    FROM orders o 
    JOIN restaurants r ON o.restaurant_id = r.id 
    WHERE o.customer_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$cid]);
$all_orders = $stmt->fetchAll();

function statusProgress($status) {
    $steps = ['placed','confirmed','preparing','out_for_delivery','delivered'];
    $idx = array_search($status, $steps);
    return $idx !== false ? $idx : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Orders – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--accent:#27ae60;--dark:#1a1a2e}
body{font-family:'DM Sans',sans-serif;background:#f8f9fa}
.navbar-brand{font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--primary)!important}
.sidebar{background:var(--dark);min-height:calc(100vh - 70px);padding:2rem 0}
.sidebar .nav-link{color:rgba(255,255,255,.7);padding:.75rem 1.5rem;border-radius:0 50px 50px 0;margin-right:1rem;transition:.3s}
.sidebar .nav-link:hover,.sidebar .nav-link.active{background:var(--primary);color:#fff}
.order-row{background:#fff;border-radius:12px;padding:1rem 1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:.75rem;cursor:pointer;transition:.3s;border:2px solid transparent}
.order-row:hover,.order-row.active{border-color:var(--primary);transform:translateX(3px)}
.detail-panel{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);padding:2rem}
.progress-steps{display:flex;justify-content:space-between;position:relative;margin:2rem 0}
.progress-steps::before{content:'';position:absolute;top:20px;left:0;right:0;height:3px;background:#e9ecef;z-index:0}
.progress-fill{position:absolute;top:20px;left:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--secondary));z-index:1;transition:width .5s ease}
.step{display:flex;flex-direction:column;align-items:center;z-index:2;flex:1}
.step-circle{width:40px;height:40px;border-radius:50%;background:#e9ecef;display:flex;align-items:center;justify-content:center;font-size:.9rem;color:#aaa;margin-bottom:.5rem;transition:.3s}
.step.done .step-circle{background:var(--primary);color:#fff}
.step.active .step-circle{background:var(--secondary);color:#fff;box-shadow:0 0 0 4px rgba(247,147,30,.2)}
.step-label{font-size:.7rem;font-weight:600;color:#aaa;text-align:center}
.step.done .step-label,.step.active .step-label{color:var(--dark)}
.item-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px dashed #eee}
.item-row:last-child{border-bottom:none}
.veg-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:6px}
.status-badge{padding:.3rem .8rem;border-radius:50px;font-size:.75rem;font-weight:600}
.badge-placed{background:#fff3e0;color:#e65100}
.badge-confirmed{background:#e3f2fd;color:#1565c0}
.badge-preparing{background:#fce4ec;color:#ad1457}
.badge-out_for_delivery{background:#e8f5e9;color:#2e7d32}
.badge-delivered{background:#e8f5e9;color:#1b5e20}
.badge-cancelled{background:#fbe9e7;color:#bf360c}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="../index.php">🍽️ FoodHub</a>
    <div class="ms-auto">
      <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-2 p-0 sidebar">
      <nav class="nav flex-column mt-2">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a class="nav-link active" href="orders.php"><i class="fas fa-receipt me-2"></i>My Orders</a>
        <a class="nav-link" href="../index.php"><i class="fas fa-search me-2"></i>Browse</a>
        <a class="nav-link" href="../donate/donate_food.php"><i class="fas fa-heart me-2"></i>Donate Food</a>
        <hr style="border-color:rgba(255,255,255,.1);margin:.5rem 1rem">
        <a class="nav-link text-danger" href="../auth/auth_handler.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </nav>
    </div>

    <div class="col-lg-10 p-4">
      <h4 class="fw-bold mb-4" style="font-family:'Playfair Display',serif">My Orders <span class="text-muted fw-normal" style="font-family:'DM Sans',sans-serif;font-size:1rem">(<?= count($all_orders) ?> total)</span></h4>
      
      <div class="row">
        <!-- Orders List -->
        <div class="col-lg-<?= $order_detail ? '4' : '12' ?>">
          <?php if (empty($all_orders)): ?>
          <div class="text-center py-5">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <p class="text-muted">No orders yet!</p>
            <a href="../index.php" class="btn rounded-pill px-4" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff">Start Ordering</a>
          </div>
          <?php else: ?>
          <?php foreach ($all_orders as $o): ?>
          <div class="order-row <?= (isset($_GET['id']) && $_GET['id'] == $o['id']) ? 'active' : '' ?>" onclick="location.href='orders.php?id=<?= $o['id'] ?>'">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="fw-bold mb-0"><?= htmlspecialchars($o['restaurant_name']) ?></h6>
                <small class="text-muted">#<?= htmlspecialchars($o['order_number']) ?></small>
              </div>
              <span class="status-badge badge-<?= $o['order_status'] ?>"><?= ucwords(str_replace('_',' ',$o['order_status'])) ?></span>
            </div>
            <div class="d-flex justify-content-between mt-2">
              <small class="text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></small>
              <strong>₹<?= number_format($o['final_amount'],2) ?></strong>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Order Detail -->
        <?php if ($order_detail): ?>
        <div class="col-lg-8">
          <div class="detail-panel">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="fw-bold mb-1" style="font-family:'Playfair Display',serif"><?= htmlspecialchars($order_detail['restaurant_name']) ?></h5>
                <p class="text-muted mb-0">Order #<?= htmlspecialchars($order_detail['order_number']) ?> · <?= date('d M Y, h:i A', strtotime($order_detail['created_at'])) ?></p>
              </div>
              <span class="status-badge badge-<?= $order_detail['order_status'] ?>"><?= ucwords(str_replace('_',' ',$order_detail['order_status'])) ?></span>
            </div>

            <?php if ($order_detail['order_status'] !== 'cancelled'): ?>
            <!-- Progress Tracker -->
            <?php $prog = statusProgress($order_detail['order_status']); $pct = ($prog/4)*100; ?>
            <div class="progress-steps">
              <div class="progress-fill" style="width:<?= $pct ?>%"></div>
              <?php
              $steps = [
                ['placed','fa-check','Placed'],
                ['confirmed','fa-store','Confirmed'],
                ['preparing','fa-fire','Preparing'],
                ['out_for_delivery','fa-motorcycle','On the way'],
                ['delivered','fa-home','Delivered'],
              ];
              foreach ($steps as $i => [$key,$icon,$label]):
                $cls = $i < $prog ? 'done' : ($i === $prog ? 'active' : '');
              ?>
              <div class="step <?= $cls ?>">
                <div class="step-circle"><i class="fas <?= $icon ?>"></i></div>
                <div class="step-label"><?= $label ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Items -->
            <h6 class="fw-bold mt-3 mb-2">Items Ordered</h6>
            <?php foreach ($order_items as $item): ?>
            <div class="item-row">
              <span>
                <span class="veg-dot" style="background:<?= $item['is_veg'] ? '#27ae60' : '#e74c3c' ?>;border:1.5px solid <?= $item['is_veg'] ? '#27ae60' : '#e74c3c' ?>"></span>
                <?= htmlspecialchars($item['item_name']) ?>
                <span class="text-muted"> × <?= $item['quantity'] ?></span>
              </span>
              <span class="fw-bold">₹<?= number_format($item['subtotal'],2) ?></span>
            </div>
            <?php endforeach; ?>

            <!-- Bill -->
            <div class="mt-3 p-3 rounded-3" style="background:#f8f9fa">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Subtotal</span>
                <span>₹<?= number_format($order_detail['total_amount'],2) ?></span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Delivery fee</span>
                <span>₹<?= number_format($order_detail['delivery_fee'],2) ?></span>
              </div>
              <?php if ($order_detail['discount'] > 0): ?>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-success">Discount (<?= 'COUPON' ?>)</span>
                <span class="text-success">-₹<?= number_format($order_detail['discount'],2) ?></span>
              </div>
              <?php endif; ?>
              <hr>
              <div class="d-flex justify-content-between fw-bold">
                <span>Total Paid</span>
                <span style="color:var(--primary)">₹<?= number_format($order_detail['final_amount'],2) ?></span>
              </div>
              <div class="text-muted small mt-1"><i class="fas fa-credit-card me-1"></i>Payment: <?= ucfirst($order_detail['payment_method']) ?></div>
            </div>

            <?php if ($order_detail['order_status'] === 'delivered'): ?>
            <a href="../restaurant_menu.php?id=<?= $order_detail['restaurant_id'] ?>" class="btn mt-3 w-100 rounded-pill fw-600" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff">
              <i class="fas fa-redo me-2"></i>Reorder
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
