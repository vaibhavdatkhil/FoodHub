<?php
require_once '../includes/config.php';
require_once '../includes/email.php';

$msg = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $type = $_POST['type'] ?? 'customer';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Please enter a valid email address.';
    } else {
        // Check if email exists
        $tables = ['customer'=>'customers','restaurant'=>'restaurants','ngo'=>'ngos'];
        $table = $tables[$type] ?? 'customers';
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Store in a simple way using OTP column for now
            $otp = rand(100000, 999999);
            $stmt = $pdo->prepare("UPDATE $table SET otp_code=?, otp_expires=? WHERE email=?");
            $stmt->execute([$otp, $expires, $email]);
            
            // Send email
            $subject = "Password Reset OTP – FoodHub";
            $body = "Your FoodHub password reset OTP is: <strong>$otp</strong><br>This OTP expires in 1 hour.<br><br>If you didn't request this, please ignore this email.";
            //sendEmail($email, $subject, $body);
            sendOTPEmail($email, 'User', $otp, $type);
            
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_type'] = $type;
            $success = true;
        } else {
            $msg = 'No account found with this email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password – FoodHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--primary:#FF6B35;--secondary:#F7931E}
body{font-family:'DM Sans',sans-serif;background:#f8f9fa;min-height:100vh;display:flex;align-items:center}
.card{border:none;border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,.1);max-width:460px;width:100%}
.brand{font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--primary)}
.btn-primary-custom{background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;border-radius:50px;color:#fff;padding:.8rem 2rem;font-weight:600;width:100%}
</style>
</head>
<body>
<div class="container d-flex justify-content-center py-5">
  <div class="card p-4 p-md-5">
    <div class="text-center mb-4">
      <div class="brand">🍽️ FoodHub</div>
      <?php if (!$success): ?>
      <h5 class="fw-bold mt-2">Forgot Password?</h5>
      <p class="text-muted">Enter your email and we'll send you a reset OTP.</p>
      <?php else: ?>
      <div style="font-size:4rem">📧</div>
      <h5 class="fw-bold mt-2">OTP Sent!</h5>
      <p class="text-muted">We've sent a password reset OTP to your email. Check your inbox.</p>
      <a href="reset_password.php" class="btn btn-primary-custom">Enter OTP & Reset Password</a>
      <?php endif; ?>
    </div>

    <?php if (!$success): ?>
    <?php if ($msg): ?>
    <div class="alert alert-danger rounded-3 small"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-600">Account Type</label>
        <div class="d-flex gap-2">
          <?php foreach (['customer'=>'Customer','restaurant'=>'Restaurant','ngo'=>'NGO'] as $val=>$lbl): ?>
          <label class="flex-1 text-center p-2 rounded-3 border" style="cursor:pointer;flex:1">
            <input type="radio" name="type" value="<?= $val ?>" class="d-none" <?= ($_POST['type']??'customer')===$val?'checked':'' ?>>
            <span><?= $lbl ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-600">Email Address</label>
        <input type="email" name="email" class="form-control rounded-3" required placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email']??'') ?>">
      </div>
      <button type="submit" class="btn-primary-custom btn">
        <i class="fas fa-paper-plane me-2"></i>Send Reset OTP
      </button>
    </form>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="login.php" class="text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
    </div>
  </div>
</div>

<script>
// Radio styling
document.querySelectorAll('input[type=radio][name=type]').forEach(r => {
    r.addEventListener('change', () => {
        document.querySelectorAll('input[type=radio][name=type]').forEach(x => {
            x.closest('label').style.background = '';
            x.closest('label').style.color = '';
        });
        if (r.checked) {
            r.closest('label').style.background = 'linear-gradient(135deg,#FF6B35,#F7931E)';
            r.closest('label').style.color = '#fff';
            r.closest('label').style.borderColor = '#FF6B35';
        }
    });
    if (r.checked) {
        r.closest('label').style.background = 'linear-gradient(135deg,#FF6B35,#F7931E)';
        r.closest('label').style.color = '#fff';
    }
});
</script>
</body>
</html>
