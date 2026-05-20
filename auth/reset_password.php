<?php
require_once '../includes/config.php';
$msg = '';
$msgType = 'danger';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_SESSION['reset_email'] ?? '';
    $type     = $_SESSION['reset_type']  ?? 'customer';
    $otp      = trim($_POST['otp']       ?? '');
    $password = $_POST['password']       ?? '';
    $confirm  = $_POST['confirm']        ?? '';

    $tables = ['customer' => 'customers', 'restaurant' => 'restaurants', 'ngo' => 'ngos'];
    $table  = $tables[$type] ?? 'customers';

    if (!$email) {
        $msg = 'Session expired. Please start the reset process again.';
    } elseif (strlen($password) < 6) {
        $msg = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $msg = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id, otp_code, otp_expires FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || $user['otp_code'] !== $otp) {
            $msg = 'Invalid OTP. Please check and try again.';
        } elseif (strtotime($user['otp_expires']) < time()) {
            $msg = 'OTP has expired. Please request a new one.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE $table SET password_hash = ?, otp_code = NULL, otp_expires = NULL WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            unset($_SESSION['reset_email'], $_SESSION['reset_type']);
            $done    = true;
            $msg     = 'Password reset successfully! You can now log in.';
            $msgType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root { --primary: #FF6B35; --secondary: #F7931E; }
  body  { font-family: 'DM Sans', sans-serif; background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; }
  .card { border: none; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,.1); max-width: 460px; width: 100%; }
  .brand { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--primary); }
  .btn-primary-custom { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 50px; color: #fff; padding: .8rem 2rem; font-weight: 600; width: 100%; }
  .otp-inputs { display: flex; gap: .5rem; justify-content: center; }
  .otp-inputs input { width: 44px; height: 52px; text-align: center; font-size: 1.3rem; font-weight: 700; border: 2px solid #dee2e6; border-radius: 10px; transition: .2s; }
  .otp-inputs input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,107,53,.15); }
</style>
</head>
<body>
<div class="container d-flex justify-content-center py-5">
  <div class="card p-4 p-md-5">
    <div class="text-center mb-4">
      <div class="brand">🍽️ FoodHub</div>
      <h5 class="fw-bold mt-2">Reset Password</h5>
      <p class="text-muted small">Enter the OTP sent to <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? 'your email') ?></strong> and choose a new password.</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> rounded-3 small"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if (!$done): ?>
    <form method="POST">
      <div class="mb-4">
        <label class="form-label fw-600 small text-center d-block">Enter OTP</label>
        <div class="otp-inputs" id="otpInputs">
          <?php for ($i = 0; $i < 6; $i++): ?>
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit">
          <?php endfor; ?>
        </div>
        <input type="hidden" name="otp" id="otpHidden">
      </div>

      <div class="mb-3">
        <label class="form-label fw-600 small">New Password</label>
        <input type="password" name="password" class="form-control rounded-3" required minlength="6" placeholder="Minimum 6 characters">
      </div>
      <div class="mb-4">
        <label class="form-label fw-600 small">Confirm New Password</label>
        <input type="password" name="confirm" class="form-control rounded-3" required placeholder="Repeat new password">
      </div>

      <button type="submit" class="btn-primary-custom btn" onclick="compileOtp()">
        <i class="fas fa-lock me-2"></i>Reset Password
      </button>
    </form>
    <?php else: ?>
    <div class="text-center mt-2">
      <a href="login.php" class="btn btn-primary-custom btn">
        <i class="fas fa-sign-in-alt me-2"></i>Go to Login
      </a>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="forgot_password.php" class="text-muted small"><i class="fas fa-arrow-left me-1"></i>Request new OTP</a>
    </div>
  </div>
</div>

<script>
const digits = document.querySelectorAll('.otp-digit');
digits.forEach((inp, i) => {
  inp.addEventListener('input', () => {
    inp.value = inp.value.replace(/[^0-9]/g, '');
    if (inp.value && i < digits.length - 1) digits[i + 1].focus();
  });
  inp.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !inp.value && i > 0) digits[i - 1].focus();
  });
});

function compileOtp() {
  document.getElementById('otpHidden').value = [...digits].map(d => d.value).join('');
}
</script>
</body>
</html>
