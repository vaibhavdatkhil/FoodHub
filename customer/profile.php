<?php
require_once '../includes/config.php';
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$cid = $_SESSION['customer_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if (empty($name) || empty($phone)) {
        $error = 'Name and Phone are required.';
    } else {
        $stmt = $pdo->prepare("UPDATE customers SET full_name=?, phone=?, address=?, city=? WHERE id=?");
        if ($stmt->execute([$name, $phone, $address, $city, $cid])) {
            $success = 'Profile updated successfully!';
            $_SESSION['customer_name'] = $name;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Fetch customer info
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$cid]);
$customer = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Profile – FoodHub</title>
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
.profile-card{background:#fff;border-radius:16px;padding:2rem;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.avatar{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:3rem;color:#fff;font-family:'Playfair Display',serif;margin-bottom:1.5rem}
.form-control{border-radius:10px;padding:0.75rem 1rem;border:2px solid #eee;transition:.3s}
.form-control:focus{border-color:var(--primary);box-shadow:none}
.btn-save{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;border:none;border-radius:50px;padding:0.75rem 2rem;font-weight:600;transition:.3s}
.btn-save:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(255,107,53,0.4)}
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
        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a class="nav-link" href="orders.php"><i class="fas fa-receipt me-2"></i>My Orders</a>
        <a class="nav-link" href="../index.php"><i class="fas fa-search me-2"></i>Browse Restaurants</a>
        <a class="nav-link" href="../donate/donate_food.php"><i class="fas fa-heart me-2"></i>Donate Food</a>
        <a class="nav-link active" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a>
        <hr style="border-color:rgba(255,255,255,.1);margin:.5rem 1rem">
        <a class="nav-link text-danger" href="../auth/auth_handler.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="col-lg-10 p-4">
      <h3 class="fw-bold mb-4" style="font-family:'Playfair Display',serif">My Profile</h3>
      
      <div class="row">
        <div class="col-md-8 col-lg-6">
          <div class="profile-card">
            <?php if($success): ?>
              <div class="alert alert-success rounded-3"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
            <?php endif; ?>
            <?php if($error): ?>
              <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <div class="text-center align-items-center flex-column d-flex">
              <div class="avatar"><?= strtoupper(substr($customer['full_name'],0,1)) ?></div>
              <h5 class="fw-bold mb-1"><?= htmlspecialchars($customer['full_name']) ?></h5>
              <p class="text-muted small"><?= htmlspecialchars($customer['email']) ?></p>
            </div>

            <form method="POST" action="profile.php" class="mt-4">
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted small">Full Name</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" name="full_name" value="<?= htmlspecialchars($customer['full_name']) ?>" required>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted small">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                  <input type="email" class="form-control border-start-0 ps-0 text-muted" value="<?= htmlspecialchars($customer['email']) ?>" readonly disabled>
                </div>
                <div class="form-text small">Email cannot be changed for security reasons.</div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold text-muted small">Phone Number</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-phone"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold text-muted small">City</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-city"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" name="city" value="<?= htmlspecialchars($customer['city']) ?>" required>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold text-muted small">Delivery Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-muted align-items-start pt-2"><i class="fas fa-map-marker-alt"></i></span>
                  <textarea class="form-control border-start-0 ps-0" name="address" rows="3" required><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn-save"><i class="fas fa-save me-2"></i>Save Changes</button>
              </div>
            </form>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
