<?php
require_once '../includes/config.php';

// Already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

// ── First-run: if no admins exist, show setup form ────────
$adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

$error   = '';
$success = '';

// ── First-run setup: create initial admin ─────────────────
if ($adminCount == 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    if (!$fullName || !$username || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name) VALUES (?,?,?,?)");
        $stmt->execute([$username, $email, $hash, $fullName]);
        $success = 'Admin account created! You can now log in.';
        $adminCount = 1;
    }
}

// ── Normal login ──────────────────────────────────────────
if ($adminCount > 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if (!$username || !$password) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root { --primary:#FF6B35; --secondary:#F7931E; --dark:#1a1a2e; }
body { font-family:'DM Sans',sans-serif; background:linear-gradient(135deg,var(--dark) 0%,#16213e 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
.login-card { background:#fff; border-radius:24px; box-shadow:0 30px 80px rgba(0,0,0,.4); width:100%; max-width:440px; overflow:hidden; }
.login-header { background:linear-gradient(135deg,var(--primary),var(--secondary)); padding:2.5rem; text-align:center; color:#fff; }
.login-header h1 { font-family:'Playfair Display',serif; font-size:2rem; margin:0; }
.login-body { padding:2rem 2.5rem 2.5rem; }
.form-label { font-weight:600; font-size:.875rem; color:#444; }
.form-control { border-radius:10px; padding:.75rem 1rem; border:2px solid #e9ecef; transition:.2s; }
.form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(255,107,53,.12); }
.input-icon-wrap { position:relative; }
.input-icon-wrap .icon { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:#aaa; }
.input-icon-wrap .form-control { padding-left:2.5rem; }
.btn-admin { background:linear-gradient(135deg,var(--primary),var(--secondary)); border:none; border-radius:50px; color:#fff; padding:.85rem; font-weight:700; font-size:1rem; width:100%; transition:.3s; }
.btn-admin:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(255,107,53,.4); color:#fff; }
.setup-badge { background:#e8f5e9; color:#2e7d32; border-radius:50px; padding:.3rem 1rem; font-size:.8rem; font-weight:700; display:inline-block; margin-bottom:1rem; }
</style>
</head>
<body>
<div class="login-card">
  <div class="login-header">
    <div style="font-size:3rem;margin-bottom:.5rem">🛡️</div>
    <h1>FoodHub Admin</h1>
    <p class="mb-0 opacity-75"><?= $adminCount == 0 ? 'First-time Setup' : 'Management Portal' ?></p>
  </div>
  <div class="login-body">

    <?php if ($error): ?>
    <div class="alert alert-danger rounded-3 small mb-3">
      <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success rounded-3 small mb-3">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($adminCount == 0): ?>
    <!-- ── FIRST-RUN SETUP ── -->
    <div class="text-center mb-3">
      <span class="setup-badge"><i class="fas fa-star me-1"></i>Create Your Admin Account</span>
    </div>
    <p class="text-muted small text-center mb-3">No admin account found. Set one up now.</p>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <div class="input-icon-wrap">
          <i class="fas fa-id-card icon"></i>
          <input type="text" name="full_name" class="form-control" placeholder="Super Admin" required value="<?= htmlspecialchars($_POST['full_name']??'') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-icon-wrap">
          <i class="fas fa-user icon"></i>
          <input type="text" name="username" class="form-control" placeholder="admin" required value="<?= htmlspecialchars($_POST['username']??'') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <div class="input-icon-wrap">
          <i class="fas fa-envelope icon"></i>
          <input type="email" name="email" class="form-control" placeholder="admin@foodhub.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock icon"></i>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Confirm Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock icon"></i>
          <input type="password" name="confirm" class="form-control" placeholder="Repeat password" required>
        </div>
      </div>
      <button type="submit" name="setup" class="btn-admin btn">
        <i class="fas fa-user-plus me-2"></i>Create Admin Account
      </button>
    </form>

    <?php else: ?>
    <!-- ── NORMAL LOGIN ── -->
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username or Email</label>
        <div class="input-icon-wrap">
          <i class="fas fa-user icon"></i>
          <input type="text" name="username" class="form-control" placeholder="admin" required autofocus
                 value="<?= htmlspecialchars($_POST['username']??'') ?>">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock icon"></i>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" name="login" class="btn-admin btn">
        <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
      </button>
    </form>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
