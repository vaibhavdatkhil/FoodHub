<?php
require_once '../includes/config.php';
if (!isset($_SESSION['ngo_id'])) {
    header('Location: ../auth/login.php?tab=ngo');
    exit;
}
$nid = $_SESSION['ngo_id'];
$stmt = $pdo->prepare("SELECT * FROM ngos WHERE id=?");
$stmt->execute([$nid]);
$ngo = $stmt->fetch();

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM food_donations WHERE ngo_id=? AND status='pending'");
$stmt->execute([$nid]); $pending = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM food_donations WHERE ngo_id=? AND status IN ('accepted','picked_up')");
$stmt->execute([$nid]); $active = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM food_donations WHERE ngo_id=? AND status='completed'");
$stmt->execute([$nid]); $completed = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(serves_people),0) FROM food_donations WHERE ngo_id=? AND status='completed'");
$stmt->execute([$nid]); $meals = $stmt->fetchColumn();

// Donations list
$filter = $_GET['filter'] ?? 'pending';
$allowed = ['pending','accepted','picked_up','completed','cancelled'];
if (!in_array($filter, $allowed)) $filter = 'pending';
$stmt = $pdo->prepare("SELECT * FROM food_donations WHERE ngo_id=? AND status=? ORDER BY created_at DESC");
$stmt->execute([$nid, $filter]);
$donations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>NGO Dashboard – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E;--green:#27ae60;--dark:#1a1a2e}
body{font-family:'DM Sans',sans-serif;background:#f0f2f5}
.sidebar{background:var(--dark);width:240px;min-height:100vh;position:fixed;left:0;top:0;z-index:100;padding-top:1rem}
.sidebar .brand{font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--green);padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:.5rem}
.sidebar .nav-link{color:rgba(255,255,255,.7);padding:.7rem 1.5rem;border-radius:0 50px 50px 0;margin-right:1rem;transition:.3s;font-size:.9rem}
.sidebar .nav-link:hover,.sidebar .nav-link.active{background:var(--green);color:#fff}
.main-content{margin-left:240px;padding:2rem}
.stat-card{border-radius:16px;padding:1.5rem;color:#fff;position:relative;overflow:hidden}
.donation-card{background:#fff;border-radius:16px;padding:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,.07);margin-bottom:1rem;border-left:4px solid transparent}
.donation-card.pending{border-left-color:#e67e22}
.donation-card.accepted{border-left-color:var(--green)}
.donation-card.picked_up{border-left-color:#3498db}
.donation-card.completed{border-left-color:#2ecc71}
.filter-tabs .nav-link{border-radius:50px;color:#666;font-size:.85rem;font-weight:600;padding:.4rem 1rem}
.filter-tabs .nav-link.active{background:var(--green);color:#fff}
</style>
</head>
<body>
<div class="sidebar">
  <div class="brand">💚 FoodHub NGO</div>
  <p class="text-center small px-3" style="color:rgba(255,255,255,.5);font-size:.75rem;margin-bottom:1rem"><?= htmlspecialchars($ngo['ngo_name']) ?></p>
  <nav class="nav flex-column">
    <a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a class="nav-link" href="dashboard.php?filter=pending"><i class="fas fa-bell me-2"></i>New Donations <span class="badge rounded-pill ms-1" style="background:var(--green)"><?= $pending ?></span></a>
    <a class="nav-link" href="dashboard.php?filter=accepted"><i class="fas fa-handshake me-2"></i>Active Pickups</a>
    <a class="nav-link" href="dashboard.php?filter=completed"><i class="fas fa-check-circle me-2"></i>Completed</a>
    <a class="nav-link" href="profile.php"><i class="fas fa-building me-2"></i>NGO Profile</a>
    <hr style="border-color:rgba(255,255,255,.1);margin:.5rem 1rem">
    <a class="nav-link text-danger" href="../auth/auth_handler.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
  </nav>
</div>

<div class="main-content">
  <h4 class="fw-bold mb-1" style="font-family:'Playfair Display',serif">Welcome, <?= htmlspecialchars($ngo['ngo_name']) ?>! 💚</h4>
  <p class="text-muted mb-4">Managing food donations and helping those in need.</p>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#e67e22,#f39c12)">
        <p class="mb-1 opacity-75 small">Pending Donations</p>
        <h2 class="fw-bold mb-0"><?= $pending ?></h2>
        <i class="fas fa-clock position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,var(--green),#2ecc71)">
        <p class="mb-1 opacity-75 small">Active Pickups</p>
        <h2 class="fw-bold mb-0"><?= $active ?></h2>
        <i class="fas fa-truck position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#74b9ff)">
        <p class="mb-1 opacity-75 small">Completed</p>
        <h2 class="fw-bold mb-0"><?= $completed ?></h2>
        <i class="fas fa-check-circle position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#e91e63,#f06292)">
        <p class="mb-1 opacity-75 small">Meals Served</p>
        <h2 class="fw-bold mb-0"><?= number_format($meals) ?></h2>
        <i class="fas fa-heart position-absolute" style="right:1.5rem;top:1.2rem;opacity:.3;font-size:2rem"></i>
      </div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <ul class="nav filter-tabs gap-2 mb-3">
    <li class="nav-item"><a class="nav-link <?= $filter==='pending'?'active':'' ?>" href="?filter=pending">⏳ Pending (<?= $pending ?>)</a></li>
    <li class="nav-item"><a class="nav-link <?= $filter==='accepted'?'active':'' ?>" href="?filter=accepted">✅ Accepted</a></li>
    <li class="nav-item"><a class="nav-link <?= $filter==='picked_up'?'active':'' ?>" href="?filter=picked_up">🚚 Picked Up</a></li>
    <li class="nav-item"><a class="nav-link <?= $filter==='completed'?'active':'' ?>" href="?filter=completed">🎉 Completed</a></li>
  </ul>
  
  <!-- Donations List -->
  <?php if (empty($donations)): ?>
  <div class="text-center py-5 bg-white rounded-4 shadow-sm">
    <i class="fas fa-heart fa-3x mb-3" style="color:var(--green);opacity:.3"></i>
    <h5 class="text-muted">No <?= $filter ?> donations.</h5>
  </div>
  <?php else: ?>
  <?php foreach ($donations as $d): ?>
  <div class="donation-card <?= $d['status'] ?>">
    <div class="row align-items-start">
      <div class="col-md-8">
        <div class="d-flex align-items-center gap-2 mb-2">
          <h5 class="fw-bold mb-0"><?= htmlspecialchars($d['donor_name']) ?></h5>
          <span class="badge rounded-pill" style="background:<?= $d['status']==='pending'?'#e67e22':($d['status']==='completed'?'var(--green)':'#3498db') ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span>
        </div>
        <p class="text-muted mb-1"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($d['donor_phone']) ?> · <i class="fas fa-envelope ms-2 me-1"></i><?= htmlspecialchars($d['donor_email']) ?></p>
        <p class="mb-1"><i class="fas fa-box me-2" style="color:var(--primary)"></i><strong><?= htmlspecialchars($d['food_type']) ?></strong> — <?= htmlspecialchars($d['food_quantity']) ?></p>
        <p class="mb-1"><i class="fas fa-users me-2" style="color:var(--green)"></i>Serves ~<strong><?= $d['serves_people'] ?> people</strong></p>
        <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?= htmlspecialchars($d['pickup_address']) ?>, <?= htmlspecialchars($d['pickup_city']) ?></p>
        <p class="mb-0 text-muted"><i class="fas fa-calendar me-2"></i><?= date('d M Y', strtotime($d['pickup_date'])) ?> at <?= $d['pickup_time'] ?></p>
        <?php if (!empty($d['description'])): ?>
        <p class="mt-2 text-muted small"><i class="fas fa-info-circle me-1"></i><?= htmlspecialchars($d['description']) ?></p>
        <?php endif; ?>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <?php if ($d['status'] === 'pending'): ?>
        <button class="btn rounded-pill fw-600 mb-2 w-100" style="background:linear-gradient(135deg,var(--green),#2ecc71);color:#fff" onclick="updateDonation(<?= $d['id'] ?>,'accepted')">
          <i class="fas fa-check me-2"></i>Accept
        </button>
        <button class="btn btn-outline-danger rounded-pill w-100" onclick="updateDonation(<?= $d['id'] ?>,'cancelled')">
          <i class="fas fa-times me-2"></i>Decline
        </button>
        <?php elseif ($d['status'] === 'accepted'): ?>
        <button class="btn rounded-pill fw-600 w-100" style="background:linear-gradient(135deg,#3498db,#74b9ff);color:#fff" onclick="updateDonation(<?= $d['id'] ?>,'picked_up')">
          <i class="fas fa-truck me-2"></i>Mark Picked Up
        </button>
        <?php elseif ($d['status'] === 'picked_up'): ?>
        <button class="btn rounded-pill fw-600 w-100" style="background:linear-gradient(135deg,var(--green),#2ecc71);color:#fff" onclick="updateDonation(<?= $d['id'] ?>,'completed')">
          <i class="fas fa-check-circle me-2"></i>Mark Completed
        </button>
        <?php else: ?>
        <span class="text-success fw-600"><i class="fas fa-check-circle me-1"></i>Completed</span>
        <?php endif; ?>
        <p class="text-muted small mt-2"><?= date('d M Y, h:i A', strtotime($d['created_at'])) ?></p>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
function updateDonation(id, status) {
    $.post('../api/ngo_handler.php', {action:'update_donation',donation_id:id,status:status}, function(res) {
        if (res.success) location.reload();
        else alert(res.message || 'Error updating donation');
    }, 'json');
}
</script>
</body>
</html>
