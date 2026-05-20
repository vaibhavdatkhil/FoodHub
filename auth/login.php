<?php
require_once '../includes/config.php';
if (isLoggedIn()) redirect('../index.php');
$defaultType = $_GET['type'] ?? 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – FoodHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root {
  --primary: #FF6B35;
  --secondary: #F7931E;
  --accent: #27ae60;
  --dark: #1a1a2e;
}
*, *::before, *::after { box-sizing: border-box; }
body {
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh; margin: 0;
  display: flex;
  background-color: #f8f9fc;
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  transition: background-image 0.5s ease-in-out;
}
body::before {
  content: ''; position: fixed; inset: 0;
  background: rgba(26,26,46,0.6); z-index: -1;
}
.auth-left {
  width: 45%;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 60px 48px; position: relative; overflow: hidden;
}
.auth-left::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23FF6B35' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.auth-blob {
  position: absolute; border-radius: 50%; pointer-events: none;
  background: radial-gradient(circle, rgba(255,107,53,0.12), transparent 70%);
}
.auth-blob-1 { width: 400px; height: 400px; top: -150px; left: -100px; }
.auth-blob-2 { width: 300px; height: 300px; bottom: -100px; right: -80px; }

.brand-big {
  font-family: 'Playfair Display', serif;
  font-size: 42px; font-weight: 800; color: #fff;
  text-align: center; margin-bottom: 12px;
}
.brand-big span { color: var(--primary); }
.left-tagline { color: rgba(255,255,255,0.7); font-size: 17px; text-align: center; line-height: 1.6; max-width: 340px; }
.left-features { margin-top: 48px; display: flex; flex-direction: column; gap: 20px; width: 100%; max-width: 320px; }
.left-feature {
  display: flex; align-items: center; gap: 16px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 16px 20px;
  color: #fff;
}
.left-feature-icon { font-size: 28px; flex-shrink: 0; }
.left-feature-title { font-weight: 600; font-size: 15px; }
.left-feature-desc { font-size: 12px; color: rgba(255,255,255,0.55); margin-top: 2px; }

.auth-right {
  flex: 1;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  display: flex; align-items: center; justify-content: center;
  padding: 40px 24px;
  overflow-y: auto;
}
.auth-form-wrap {
  width: 100%; max-width: 440px;
  background: rgba(255, 255, 255, 0.9);
  padding: 40px;
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}
.auth-form-wrap h2 {
  font-family: 'Playfair Display', serif;
  font-size: 30px; font-weight: 800; color: var(--dark);
  margin-bottom: 4px;
}
.auth-form-wrap p.sub { color: #888; font-size: 15px; margin-bottom: 32px; }

/* User type tabs */
.user-type-tabs {
  display: flex; gap: 8px; margin-bottom: 32px;
  background: #f5f5f5; padding: 6px; border-radius: 16px;
}
.user-tab {
  flex: 1; padding: 12px 8px; border-radius: 12px;
  border: none; background: transparent; cursor: pointer;
  font-weight: 600; font-size: 13px; color: #888;
  transition: all 0.25s; display: flex; flex-direction: column;
  align-items: center; gap: 4px;
}
.user-tab .tab-icon { font-size: 22px; }
.user-tab.active { background: #fff; color: var(--dark); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
.user-tab.active.customer { color: var(--primary); }
.user-tab.active.restaurant { color: #e67e22; }
.user-tab.active.ngo { color: var(--accent); }

/* Input styling */
.form-floating label { color: #aaa; font-size: 14px; }
.form-control-custom {
  width: 100%; padding: 14px 16px 14px 48px;
  border: 2px solid #e8e8e8; border-radius: 14px;
  font-size: 15px; outline: none; transition: all 0.2s;
  font-family: 'DM Sans', sans-serif; color: var(--dark);
}
.form-control-custom:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(255,107,53,0.08); }
.input-group-custom { position: relative; margin-bottom: 20px; }
.input-icon {
  position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
  color: #bbb; font-size: 16px; z-index: 1; transition: color 0.2s;
}
.input-group-custom:focus-within .input-icon { color: var(--primary); }
.toggle-pw {
  position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
  color: #bbb; cursor: pointer; font-size: 16px; z-index: 1;
  background: none; border: none; transition: color 0.2s;
}
.toggle-pw:hover { color: var(--primary); }

.btn-auth {
  width: 100%; padding: 15px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none; border-radius: 14px; color: #fff;
  font-size: 16px; font-weight: 700; cursor: pointer;
  transition: all 0.3s; font-family: 'DM Sans', sans-serif;
}
.btn-auth:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(255,107,53,0.35); }
.btn-auth:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
.btn-auth.ngo-btn { background: linear-gradient(135deg, var(--accent), #2ecc71); }
.btn-auth.ngo-btn:hover { box-shadow: 0 10px 28px rgba(39,174,96,0.35); }

.divider { display: flex; align-items: center; gap: 16px; margin: 24px 0; color: #ccc; font-size: 13px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e8e8e8; }

.auth-alert {
  border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
  font-size: 14px; font-weight: 500; display: none;
}

.remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.form-check-custom { display: flex; align-items: center; gap: 8px; }
.form-check-custom input { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }
.form-check-custom label { font-size: 14px; color: #555; cursor: pointer; }
.forgot-link { font-size: 14px; color: var(--primary); text-decoration: none; font-weight: 600; }
.forgot-link:hover { text-decoration: underline; }

@media (max-width: 768px) {
  .auth-left { display: none; }
  .auth-right { padding: 32px 16px; }
}
</style>
</head>
<body>


<!-- LEFT PANEL -->
<div class="auth-left">
  <div class="auth-blob auth-blob-1"></div>
  <div class="auth-blob auth-blob-2"></div>
  <div class="position-relative">
    <div class="brand-big">🍽️ Food<span>Hub</span></div>
    <p class="left-tagline">Your favourite food, delivered fast. And together, let's fight hunger.</p>
    
    <div class="left-features">
      <div class="left-feature">
        <div class="left-feature-icon">🍕</div>
        <div>
          <div class="left-feature-title">500+ Restaurants</div>
          <div class="left-feature-desc">Choose from the best local eateries</div>
        </div>
      </div>
      <div class="left-feature">
        <div class="left-feature-icon">⚡</div>
        <div>
          <div class="left-feature-title">30-Min Delivery</div>
          <div class="left-feature-desc">Hot food at your doorstep, fast</div>
        </div>
      </div>
      <div class="left-feature">
        <div class="left-feature-icon">🤝</div>
        <div>
          <div class="left-feature-title">Donate & Feed</div>
          <div class="left-feature-desc">Turn leftover food into someone's meal</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="auth-right">
  <div class="auth-form-wrap">
    <h2>Welcome Back!</h2>
    <p class="sub">Sign in to your FoodHub account</p>

    <!-- User Type Tabs -->
    <div class="user-type-tabs">
      <button class="user-tab customer <?= $defaultType=='customer'?'active':'' ?>" onclick="switchTab('customer', this)">
        <span class="tab-icon">👤</span>Customer
      </button>
      <button class="user-tab restaurant <?= $defaultType=='restaurant'?'active':'' ?>" onclick="switchTab('restaurant', this)">
        <span class="tab-icon">🍴</span>Restaurant
      </button>
      <button class="user-tab ngo <?= $defaultType=='ngo'?'active':'' ?>" onclick="switchTab('ngo', this)">
        <span class="tab-icon">🤝</span>NGO
      </button>
    </div>

    <!-- Alert -->
    <div class="auth-alert alert" id="loginAlert" role="alert"></div>

    <!-- Login Form -->
    <form id="loginForm">
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="user_type" id="userTypeInput" value="<?= $defaultType ?>">

      <div class="input-group-custom">
        <i class="fas fa-envelope input-icon"></i>
        <input type="email" name="email" class="form-control-custom" placeholder="Email address" required>
      </div>

      <div class="input-group-custom">
        <i class="fas fa-lock input-icon"></i>
        <input type="password" name="password" id="passwordInput" class="form-control-custom" placeholder="Password" required>
        <button type="button" class="toggle-pw" onclick="togglePw()">
          <i class="fas fa-eye" id="pwEyeIcon"></i>
        </button>
      </div>

      <div class="remember-row">
        <div class="form-check-custom">
          <input type="checkbox" name="remember" id="rememberMe">
          <label for="rememberMe">Remember me</label>
        </div>
        <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
      </div>

      <button type="submit" class="btn-auth" id="loginBtn">
        <i class="fas fa-sign-in-alt me-2"></i>Sign In
      </button>
    </form>

    <div class="divider">or continue with</div>

    <div class="d-flex gap-3 mb-4">
      <button class="btn btn-outline-secondary flex-fill d-flex align-items-center justify-content-center gap-2" style="border-radius:12px;padding:12px;font-weight:600;">
        <img src="https://www.google.com/favicon.ico" width="18"> Google
      </button>
      <button class="btn btn-outline-secondary flex-fill d-flex align-items-center justify-content-center gap-2" style="border-radius:12px;padding:12px;font-weight:600;">
        <i class="fab fa-facebook-f" style="color:#1877f2;"></i> Facebook
      </button>
    </div>

    <p class="text-center text-muted" style="font-size:15px;">
      Don't have an account? 
      <a href="register.php" style="color:var(--primary);font-weight:700;text-decoration:none;">Create Account</a>
    </p>

    <p class="text-center mt-3" style="font-size:13px;">
      <a href="../donate/donate_food.php" style="color:var(--accent);font-weight:600;text-decoration:none;">
        <i class="fas fa-hand-holding-heart me-1"></i>Donate Food without account
      </a>
    </p>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const backgrounds = {
    'customer': 'url("https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1920&q=80")',
    'restaurant': 'url("https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1920&q=80")',
    'ngo': 'url("https://images.unsplash.com/photo-1518391846015-55a9cc003b25?auto=format&fit=crop&w=1920&q=80")'
};

const tabColors = { customer: '#FF6B35', restaurant: '#e67e22', ngo: '#27ae60' };

function switchTab(type, el) {
    $('.user-tab').removeClass('active');
    $(el).addClass('active');
    $('#userTypeInput').val(type);
    
    document.body.style.backgroundImage = backgrounds[type];
    
    const color = tabColors[type];
    document.querySelectorAll('.form-control-custom').forEach(i => {
        i.style.setProperty('--focus-color', color);
    });
    $('.btn-auth').css('background', type === 'ngo' ? 
        'linear-gradient(135deg, #27ae60, #2ecc71)' :
        type === 'restaurant' ? 
        'linear-gradient(135deg, #e67e22, #f39c12)' :
        'linear-gradient(135deg, #FF6B35, #F7931E)'
    );
    $('#loginAlert').hide();
}

function togglePw() {
    const input = $('#passwordInput');
    const icon = $('#pwEyeIcon');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
}

function showAlert(type, msg) {
    $('#loginAlert').attr('class', `auth-alert alert alert-${type}`).html(msg).show();
}

$('#loginForm').on('submit', function(e) {
    e.preventDefault();
    const $btn = $('#loginBtn');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Signing in...');
    
    $.ajax({
        url: 'auth_handler.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                showAlert('success', '<i class="fas fa-check-circle me-2"></i>' + res.message);
                setTimeout(() => window.location.href = res.redirect, 1000);
            } else {
                showAlert('danger', '<i class="fas fa-exclamation-circle me-2"></i>' + res.message);
                $btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                if (res.need_verify) {
                    setTimeout(() => window.location.href = 'verify_otp.php', 2000);
                }
            }
        },
        error: () => {
            showAlert('danger', 'Connection error. Please try again.');
            $btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
        }
    });
});

// Auto-select tab from URL param
const urlType = new URLSearchParams(window.location.search).get('type');
if (urlType && ['customer','restaurant','ngo'].includes(urlType)) {
    const tabEl = document.querySelector(`.user-tab.${urlType}`);
    if (tabEl) switchTab(urlType, tabEl);
}
// Initial background load
document.body.style.backgroundImage = backgrounds['<?= htmlspecialchars($defaultType) ?>'] || backgrounds['customer'];
</script>
</body>
</html>
