<?php
require_once 'includes/config.php';
$search = trim($_GET['q'] ?? '');
$city = trim($_GET['city'] ?? '');
$cuisine = trim($_GET['cuisine'] ?? '');

$where = ['r.is_approved=1', 'r.is_verified=1'];
$params = [];
if ($search) { $where[] = "(r.restaurant_name LIKE ? OR r.cuisine_type LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($city) { $where[] = "r.city=?"; $params[] = $city; }
if ($cuisine) { $where[] = "r.cuisine_type LIKE ?"; $params[] = "%$cuisine%"; }

$sql = "SELECT r.*, COUNT(DISTINCT mi.id) as item_count FROM restaurants r LEFT JOIN menu_items mi ON r.id=mi.restaurant_id WHERE ".implode(' AND ',$where)." GROUP BY r.id ORDER BY r.rating DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();

$cities = $pdo->query("SELECT DISTINCT city FROM restaurants WHERE is_approved=1 ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
$cuisines = ['Indian','Chinese','Pizza','Biryani','Burger','South Indian','Desserts','Veg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>All Restaurants – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--dark:#1a1a2e;--accent:#27ae60}
body{font-family:'DM Sans',sans-serif;background:#f8f9fa}
.navbar-brand{font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--primary)!important}
.rest-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.07);transition:.3s;cursor:pointer;height:100%}
.rest-card:hover{transform:translateY(-5px);box-shadow:0 12px 40px rgba(0,0,0,.14)}
.rest-thumb{height:180px;background:linear-gradient(135deg,var(--primary),var(--secondary));position:relative;display:flex;align-items:center;justify-content:center;font-size:4rem}
.rest-badge{position:absolute;top:.75rem;right:.75rem;background:rgba(0,0,0,.6);color:#fff;padding:.25rem .7rem;border-radius:50px;font-size:.75rem;font-weight:600}
.rest-rating{display:inline-flex;align-items:center;gap:.3rem;background:#e8f5e9;color:#2e7d32;padding:.2rem .6rem;border-radius:50px;font-size:.8rem;font-weight:700}
.filter-chip{border:1px solid #dee2e6;border-radius:50px;padding:.35rem 1rem;font-size:.85rem;cursor:pointer;transition:.3s;background:#fff;font-weight:500}
.filter-chip:hover,.filter-chip.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.page-hero{background:linear-gradient(135deg,var(--dark),#16213e);padding:3rem 0;color:#fff}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">🍽️ FoodHub</a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <a href="donate/donate_food.php" class="btn btn-sm rounded-pill fw-600" style="background:var(--accent);color:#fff"><i class="fas fa-heart me-1"></i>Donate Food</a>
      <?php if (isset($_SESSION['customer_id'])): ?>
      <a href="customer/dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">My Account</a>
      <?php else: ?>
      <a href="auth/login.php" class="btn btn-sm rounded-pill" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="page-hero">
  <div class="container">
    <h2 class="fw-bold mb-2" style="font-family:'Playfair Display',serif">All Restaurants</h2>
    <p class="mb-3 opacity-75">Discover and order from the best restaurants near you</p>
    <form method="GET" class="d-flex gap-2 flex-wrap">
      <input type="text" name="q" class="form-control rounded-pill" style="max-width:300px" placeholder="Search restaurants or cuisine..." value="<?= htmlspecialchars($search) ?>">
      <select name="city" class="form-select rounded-pill" style="max-width:180px">
        <option value="">All Cities</option>
        <?php foreach ($cities as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $city===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn rounded-pill px-4 fw-600" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff"><i class="fas fa-search me-2"></i>Search</button>
      <?php if ($search || $city || $cuisine): ?>
      <a href="restaurants.php" class="btn btn-outline-light rounded-pill">Clear</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="container py-4">
  <!-- Cuisine Filter -->
  <div class="d-flex gap-2 flex-wrap mb-4">
    <a href="restaurants.php<?= $city?"?city=$city":'' ?>" class="filter-chip text-decoration-none <?= !$cuisine?'active':'' ?>">All</a>
    <?php foreach ($cuisines as $c): ?>
    <a href="?cuisine=<?= urlencode($c) ?><?= $city?"&city=$city":'' ?><?= $search?"&q=".urlencode($search):'' ?>" class="filter-chip text-decoration-none <?= $cuisine==$c?'active':'' ?>"><?= $c ?></a>
    <?php endforeach; ?>
  </div>

  <p class="text-muted mb-3"><strong><?= count($restaurants) ?></strong> restaurants found <?= $search?"for \"$search\"":'' ?></p>

  <div class="row g-4">
    <?php if (empty($restaurants)): ?>
    <div class="col-12 text-center py-5">
      <i class="fas fa-search fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">No restaurants found.</h5>
      <a href="restaurants.php" class="btn btn-outline-secondary rounded-pill mt-2">Clear filters</a>
    </div>
    <?php else: ?>
    <?php
    $emojis = ['🍛','🍕','🥘','🍜','🍔','🥗','🍰','🫕'];
    foreach ($restaurants as $i => $r):
      $emoji = $emojis[$i % count($emojis)];
      $cuisineList = array_map('trim', explode(',', $r['cuisine_type']));
    ?>
    <div class="col-md-6 col-lg-4">
      <a href="restaurant_menu.php?id=<?= $r['id'] ?>" class="text-decoration-none">
        <div class="rest-card">
          <div class="rest-thumb">
            <?= $emoji ?>
            <span class="rest-badge"><i class="fas fa-clock me-1"></i>25-40 min</span>
          </div>
          <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($r['restaurant_name']) ?></h6>
              <span class="rest-rating">⭐ <?= $r['rating'] ?></span>
            </div>
            <p class="text-muted small mb-2"><?= implode(' · ', array_map('htmlspecialchars', array_slice($cuisineList,0,3))) ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted small"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($r['city']) ?></span>
              <span class="text-muted small"><i class="fas fa-utensils me-1"></i><?= $r['item_count'] ?> items</span>
            </div>
            <div class="mt-2 d-flex justify-content-between align-items-center">
              <small class="text-muted">Min ₹149 · Free delivery above ₹299</small>
            </div>
          </div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer class="py-4 mt-5" style="background:var(--dark);color:rgba(255,255,255,.7)">
  <div class="container text-center">
    <p class="mb-0">🍽️ <strong style="color:var(--primary)">FoodHub</strong> — Order food. Donate surplus. Feed the world.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
