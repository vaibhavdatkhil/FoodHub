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
<title>Register – FoodHub</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root { --primary:#FF6B35; --secondary:#F7931E; --accent:#27ae60; --dark:#1a1a2e; }
*, *::before, *::after { box-sizing: border-box; }
body { 
  font-family: 'DM Sans', sans-serif; 
  margin: 0; min-height: 100vh; 
  background-color: #f8f9fc;
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  transition: background-image 0.5s ease-in-out;
}
body::before {
  content: ''; position: fixed; inset: 0;
  background: rgba(26,26,46,0.7); z-index: -1;
}
.reg-header {
  background: rgba(26,26,46,0.8);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  padding: 20px 0; text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.brand-link {
  font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 800;
  color: #fff; text-decoration: none;
}
.brand-link span { color: var(--primary); }

.reg-container {
  max-width: 740px; margin: 40px auto; padding: 0 16px 60px;
}
.reg-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 0 16px 60px rgba(0,0,0,0.2);
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.3);
}
.reg-tabs-header {
  display: flex; border-bottom: 2px solid #f0f0f0;
}
.reg-tab {
  flex: 1; padding: 20px 12px; border: none; background: transparent;
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  font-size: 14px; font-weight: 600; color: #888;
  transition: all 0.25s; display: flex; flex-direction: column; align-items: center; gap: 6px;
  border-bottom: 3px solid transparent; margin-bottom: -2px;
}
.reg-tab .tab-emoji { font-size: 26px; }
.reg-tab:hover { background: #fafafa; color: #555; }
.reg-tab.active { color: var(--primary); border-bottom-color: var(--primary); background: #fff7f0; }
.reg-tab.active.rest-tab { color: #e67e22; border-bottom-color: #e67e22; background: #fff8f0; }
.reg-tab.active.ngo-tab { color: var(--accent); border-bottom-color: var(--accent); background: #f0fff4; }

.reg-body { padding: 40px; }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

.section-head { margin-bottom: 28px; }
.section-head h3 { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--dark); margin-bottom: 4px; }
.section-head p { color: #888; font-size: 14px; }

.form-row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-row-custom.single { grid-template-columns: 1fr; }
@media (max-width: 600px) { .form-row-custom { grid-template-columns: 1fr; } }

.form-group-custom { position: relative; }
.form-label-custom { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; display: block; }
.form-label-custom .req { color: var(--primary); }
.input-wrap { position: relative; }
.input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 15px; pointer-events: none; transition: color 0.2s; }
.form-input {
  width: 100%; padding: 13px 16px 13px 42px;
  border: 2px solid #e8e8e8; border-radius: 12px;
  font-size: 14px; color: var(--dark); outline: none;
  transition: all 0.2s; font-family: 'DM Sans', sans-serif;
  background: #fafafa;
}
.form-input:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(255,107,53,0.07); }
.form-input:focus ~ .input-icon, .input-wrap:focus-within .input-icon { color: var(--primary); }
.form-input.error { border-color: #e74c3c; }
.form-input.success { border-color: var(--accent); }
.pw-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #bbb; cursor: pointer; font-size: 15px; }
.pw-toggle:hover { color: var(--primary); }
.form-hint { font-size: 11px; color: #aaa; margin-top: 5px; }
.form-error { font-size: 12px; color: #e74c3c; margin-top: 5px; display: none; }

/* Strength bar */
.pw-strength { margin-top: 8px; }
.strength-bar { height: 4px; border-radius: 2px; background: #eee; overflow: hidden; margin-bottom: 4px; }
.strength-fill { height: 100%; border-radius: 2px; transition: all 0.3s; width: 0; }
.strength-text { font-size: 11px; color: #aaa; }

/* Cuisine checkboxes */
.cuisine-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.cuisine-chip {
  padding: 6px 14px; border-radius: 50px; border: 2px solid #e8e8e8;
  font-size: 13px; font-weight: 600; cursor: pointer; color: #666; background: #fff;
  transition: all 0.2s; user-select: none;
}
.cuisine-chip.selected { background: var(--primary); border-color: var(--primary); color: #fff; }

.btn-submit {
  width: 100%; padding: 15px; border: none; border-radius: 14px; color: #fff;
  font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s;
  font-family: 'DM Sans', sans-serif; margin-top: 24px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(255,107,53,0.35); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
.btn-submit.rest-submit { background: linear-gradient(135deg, #e67e22, #f39c12); }
.btn-submit.ngo-submit { background: linear-gradient(135deg, var(--accent), #2ecc71); }

.terms-check { display: flex; align-items: flex-start; gap: 10px; margin-top: 16px; }
.terms-check input { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0; }
.terms-check label { font-size: 13px; color: #666; }
.terms-check a { color: var(--primary); text-decoration: none; }

.reg-alert { border-radius: 12px; padding: 12px 16px; margin-bottom: 24px; font-size: 14px; font-weight: 500; display: none; }

.login-link-row { text-align: center; margin-top: 24px; font-size: 15px; color: #888; }
.login-link-row a { color: var(--primary); font-weight: 700; text-decoration: none; }

.step-indicator { display: flex; align-items: center; gap: 8px; margin-bottom: 28px; flex-wrap: wrap; }
.step-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
.step-dot.done { background: var(--accent); color: #fff; }
.step-dot.current { background: var(--primary); color: #fff; }
.step-dot.next { background: #eee; color: #aaa; }
.step-line { flex: 1; height: 2px; background: #eee; min-width: 20px; }
</style>
</head>
<body>


<div class="reg-header">
  <a href="../index.php" class="brand-link">🍽️ Food<span>Hub</span></a>
  <p style="color:rgba(255,255,255,0.6);margin:6px 0 0;font-size:14px;">Create your account</p>
</div>

<div class="reg-container">
  <div class="reg-card">
    <!-- TABS -->
    <div class="reg-tabs-header">
      <button class="reg-tab <?= $defaultType=='customer'?'active':'' ?>" onclick="switchRegTab('customer', this)" id="tab-customer">
        <span class="tab-emoji">👤</span>Customer
        <span style="font-size:10px;color:inherit;opacity:0.7;">Order Food</span>
      </button>
      <button class="reg-tab rest-tab <?= $defaultType=='restaurant'?'active':'' ?>" onclick="switchRegTab('restaurant', this)" id="tab-restaurant">
        <span class="tab-emoji">🍴</span>Restaurant
        <span style="font-size:10px;color:inherit;opacity:0.7;">Sell Food</span>
      </button>
      <button class="reg-tab ngo-tab <?= $defaultType=='ngo'?'active':'' ?>" onclick="switchRegTab('ngo', this)" id="tab-ngo">
        <span class="tab-emoji">🤝</span>NGO
        <span style="font-size:10px;color:inherit;opacity:0.7;">Get Donations</span>
      </button>
    </div>

    <div class="reg-body">
      <div id="regAlert" class="reg-alert alert"></div>

      <!-- ===== CUSTOMER FORM ===== -->
      <div class="tab-pane <?= $defaultType=='customer'?'active':'' ?>" id="pane-customer">
        <div class="section-head">
          <h3>👤 Create Customer Account</h3>
          <p>Order your favourite food from the best restaurants</p>
        </div>
        <form id="customerForm">
          <input type="hidden" name="action" value="customer_register">
          
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Full Name <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="full_name" class="form-input" placeholder="John Doe" required>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Email Address <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-input" id="custEmail" placeholder="you@example.com" required>
              </div>
              <div class="form-hint" id="emailHint">OTP will be sent to this email</div>
            </div>
          </div>
          
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Phone Number <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" name="phone" class="form-input" placeholder="+91 98765 43210" pattern="[0-9]{10}" required>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">City</label>
              <div class="input-wrap">
                <i class="fas fa-city input-icon"></i>
                <select name="city" class="form-input">
                  <option value="">Select City</option>
                  <option>Pune</option><option>Mumbai</option><option>Delhi</option>
                  <option>Bangalore</option><option>Chennai</option><option>Hyderabad</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Password <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" class="form-input pw-field" id="custPw" placeholder="Min. 8 characters" required>
                <button type="button" class="pw-toggle" onclick="togglePwField('custPw', 'custPwEye')"><i class="fas fa-eye" id="custPwEye"></i></button>
              </div>
              <div class="pw-strength">
                <div class="strength-bar"><div class="strength-fill" id="custPwBar"></div></div>
                <span class="strength-text" id="custPwText"></span>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Confirm Password <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="confirm_password" class="form-input" id="custPw2" placeholder="Repeat password" required>
                <button type="button" class="pw-toggle" onclick="togglePwField('custPw2', 'custPw2Eye')"><i class="fas fa-eye" id="custPw2Eye"></i></button>
              </div>
              <div class="form-error" id="custPwMatchErr">Passwords do not match</div>
            </div>
          </div>

          <div class="terms-check">
            <input type="checkbox" id="custTerms" required>
            <label for="custTerms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
          </div>

          <button type="submit" class="btn-submit" id="custSubmitBtn">
            <i class="fas fa-user-plus me-2"></i>Create Account & Send OTP
          </button>
        </form>
      </div>

      <!-- ===== RESTAURANT FORM ===== -->
      <div class="tab-pane <?= $defaultType=='restaurant'?'active':'' ?>" id="pane-restaurant">
        <div class="section-head">
          <h3>🍴 Register Your Restaurant</h3>
          <p>List your restaurant and reach thousands of hungry customers</p>
        </div>
        <form id="restaurantForm">
          <input type="hidden" name="action" value="restaurant_register">

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Owner / Manager Name <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="owner_name" class="form-input" placeholder="Your full name" required>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Restaurant Name <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-store input-icon"></i>
                <input type="text" name="restaurant_name" class="form-input" placeholder="Eg. Spice Garden" required>
              </div>
            </div>
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Business Email <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-input" placeholder="restaurant@email.com" required>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Contact Number <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" name="phone" class="form-input" placeholder="+91 98765 43210" required>
              </div>
            </div>
          </div>

          <div class="form-row-custom single">
            <div class="form-group-custom">
              <label class="form-label-custom">Restaurant Address <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-map-marker-alt input-icon" style="top:20px;transform:none;"></i>
                <textarea name="address" class="form-input" style="padding-top:12px;height:80px;resize:none;" placeholder="Full street address" required></textarea>
              </div>
            </div>
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">City <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-city input-icon"></i>
                <select name="city" class="form-input" required>
                  <option value="">Select City</option>
                  <option>Pune</option><option>Mumbai</option><option>Delhi</option>
                  <option>Bangalore</option><option>Chennai</option><option>Hyderabad</option>
                </select>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">FSSAI / License Number</label>
              <div class="input-wrap">
                <i class="fas fa-id-card input-icon"></i>
                <input type="text" name="fssai_number" class="form-input" placeholder="Optional">
              </div>
            </div>
          </div>

          <div class="form-group-custom" style="margin-bottom:20px;">
            <label class="form-label-custom">Cuisine Types</label>
            <div class="cuisine-grid">
              <?php foreach(['Indian','Chinese','Italian','Mughlai','South Indian','Fast Food','Desserts','Continental','Biryani','Pizza'] as $c): ?>
              <div class="cuisine-chip" onclick="toggleCuisine(this, '<?= $c ?>')"><?= $c ?></div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" name="cuisine_type" id="cuisineInput">
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Password <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" class="form-input" id="restPw" placeholder="Min. 8 characters" required>
                <button type="button" class="pw-toggle" onclick="togglePwField('restPw','restPwEye')"><i class="fas fa-eye" id="restPwEye"></i></button>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Confirm Password <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="confirm_password" class="form-input" id="restPw2" placeholder="Repeat password" required>
                <button type="button" class="pw-toggle" onclick="togglePwField('restPw2','restPw2Eye')"><i class="fas fa-eye" id="restPw2Eye"></i></button>
              </div>
            </div>
          </div>

          <div class="terms-check">
            <input type="checkbox" id="restTerms" required>
            <label for="restTerms">I agree to the <a href="#">Restaurant Partner Terms</a> and confirm this is a registered business</label>
          </div>

          <button type="submit" class="btn-submit rest-submit" id="restSubmitBtn">
            <i class="fas fa-store me-2"></i>Register Restaurant & Send OTP
          </button>
        </form>
      </div>

      <!-- ===== NGO FORM ===== -->
      <div class="tab-pane <?= $defaultType=='ngo'?'active':'' ?>" id="pane-ngo">
        <div class="section-head">
          <h3>🤝 Register Your NGO</h3>
          <p>Join our network to receive food donations and fight hunger</p>
        </div>
        <form id="ngoForm">
          <input type="hidden" name="action" value="ngo_register">

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">NGO Name <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-hands-helping input-icon"></i>
                <input type="text" name="ngo_name" class="form-input" placeholder="Foundation name" required>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Contact Person <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="contact_person" class="form-input" placeholder="Representative name" required>
              </div>
            </div>
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Email Address <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-input" placeholder="ngo@email.com" required>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Phone Number <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" name="phone" class="form-input" placeholder="+91 98765 43210" required>
              </div>
            </div>
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">NGO Registration Number</label>
              <div class="input-wrap">
                <i class="fas fa-certificate input-icon"></i>
                <input type="text" name="registration_number" class="form-input" placeholder="NGO-XXX-YYYY-NNN">
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">City <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-city input-icon"></i>
                <select name="city" class="form-input" required>
                  <option value="">Select City</option>
                  <option>Pune</option><option>Mumbai</option><option>Delhi</option>
                  <option>Bangalore</option><option>Chennai</option><option>Hyderabad</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-row-custom single">
            <div class="form-group-custom">
              <label class="form-label-custom">Address <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-map-marker-alt input-icon" style="top:20px;transform:none;"></i>
                <textarea name="address" class="form-input" style="padding-top:12px;height:80px;resize:none;" placeholder="NGO office address" required></textarea>
              </div>
            </div>
          </div>

          <div class="form-row-custom single">
            <div class="form-group-custom">
              <label class="form-label-custom">About Your NGO</label>
              <div class="input-wrap">
                <i class="fas fa-info-circle input-icon" style="top:20px;transform:none;"></i>
                <textarea name="description" class="form-input" style="padding-top:12px;height:100px;resize:none;" placeholder="Briefly describe your NGO's mission and the communities you serve..."></textarea>
              </div>
            </div>
          </div>

          <div class="form-row-custom">
            <div class="form-group-custom">
              <label class="form-label-custom">Password <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" class="form-input" id="ngoPw" placeholder="Min. 8 characters" required>
                <button type="button" class="pw-toggle" onclick="togglePwField('ngoPw','ngoPwEye')"><i class="fas fa-eye" id="ngoPwEye"></i></button>
              </div>
            </div>
            <div class="form-group-custom">
              <label class="form-label-custom">Confirm Password <span class="req">*</span></label>
              <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="confirm_password" class="form-input" id="ngoPw2" placeholder="Repeat password" required>
                <button type="button" class="pw-toggle" onclick="togglePwField('ngoPw2','ngoPw2Eye')"><i class="fas fa-eye" id="ngoPw2Eye"></i></button>
              </div>
            </div>
          </div>

          <div class="terms-check">
            <input type="checkbox" id="ngoTerms" required>
            <label for="ngoTerms">I certify this is a legitimate NGO and agree to the <a href="#">Partner Terms</a></label>
          </div>

          <button type="submit" class="btn-submit ngo-submit" id="ngoSubmitBtn">
            <i class="fas fa-hands-helping me-2"></i>Register NGO & Send OTP
          </button>
        </form>
      </div>

      <div class="login-link-row">
        Already have an account? <a href="login.php">Sign In</a>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const backgrounds = {
    'customer': 'url("https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1920&q=80")',
    'restaurant': 'url("https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1920&q=80")',
    'ngo': 'url("https://images.unsplash.com/photo-1518391846015-55a9cc003b25?auto=format&fit=crop&w=1920&q=80")'
};

function switchRegTab(type, el) {
    $('.reg-tab').removeClass('active');
    $(el).addClass('active');
    $('.tab-pane').removeClass('active');
    $(`#pane-${type}`).addClass('active');
    $('#regAlert').hide();
    
    document.body.style.backgroundImage = backgrounds[type];
}

// Initial background load
document.body.style.backgroundImage = backgrounds['<?= htmlspecialchars($defaultType) ?>'] || backgrounds['customer'];

function togglePwField(id, eyeId) {
    const el = document.getElementById(id);
    const eye = document.getElementById(eyeId);
    if (el.type === 'password') {
        el.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        el.type = 'password';
        eye.className = 'fas fa-eye';
    }
}

// Password strength
$('#custPw').on('input', function() {
    const pw = $(this).val();
    let strength = 0;
    if (pw.length >= 8) strength++;
    if (/[A-Z]/.test(pw)) strength++;
    if (/[0-9]/.test(pw)) strength++;
    if (/[^A-Za-z0-9]/.test(pw)) strength++;
    
    const colors = ['#e74c3c','#e67e22','#f1c40f','#27ae60'];
    const labels = ['Weak','Fair','Good','Strong'];
    const widths = [25, 50, 75, 100];
    
    if (pw.length > 0) {
        $('#custPwBar').css({ width: widths[strength-1]+'%', background: colors[strength-1] });
        $('#custPwText').text(labels[strength-1]).css('color', colors[strength-1]);
    } else {
        $('#custPwBar').css('width', '0');
        $('#custPwText').text('');
    }
});

// Cuisine toggle
const selectedCuisines = [];
function toggleCuisine(el, name) {
    $(el).toggleClass('selected');
    const idx = selectedCuisines.indexOf(name);
    if (idx >= 0) selectedCuisines.splice(idx, 1);
    else selectedCuisines.push(name);
    $('#cuisineInput').val(selectedCuisines.join(','));
}

function showAlert(type, msg) {
    $('#regAlert').attr('class', `reg-alert alert alert-${type}`).html(msg).show();
    $('html,body').animate({scrollTop: 0}, 300);
}

function handleFormSubmit(formId, btnId) {
    $('#' + formId).on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#' + btnId);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...');
        
        $.ajax({
            url: 'auth_handler.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showAlert('success', '✅ ' + res.message);
                    $btn.html('<i class="fas fa-check me-2"></i>Account Created!');
                    setTimeout(() => window.location.href = res.redirect, 1500);
                } else {
                    showAlert('danger', '❌ ' + res.message);
                    $btn.prop('disabled', false).html($btn.data('original') || 'Register');
                }
            },
            error: function(xhr) {
                let msg = 'Connection error. Please try again.';
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp && resp.message) msg = resp.message;
                } catch(e) {
                    if (xhr.responseText && xhr.responseText.length < 300) {
                        msg = 'Server error: ' + xhr.responseText.replace(/<[^>]+>/g,'').trim().substring(0, 150);
                    }
                }
                showAlert('danger', '❌ ' + msg);
                $btn.prop('disabled', false).html($btn.data('original') || 'Create Account');
            }
        });
    });
}

handleFormSubmit('customerForm', 'custSubmitBtn');
handleFormSubmit('restaurantForm', 'restSubmitBtn');
handleFormSubmit('ngoForm', 'ngoSubmitBtn');

// Auto-select tab from URL param
const urlType = new URLSearchParams(window.location.search).get('type');
if (urlType) {
    const tabEl = document.getElementById(`tab-${urlType}`);
    if (tabEl) switchRegTab(urlType, tabEl);
}
</script>
</body>
</html>
