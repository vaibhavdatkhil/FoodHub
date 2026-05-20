<?php
require_once '../includes/config.php';
require_once '../includes/email.php';
$db = getDB();
$ngos = $db->query("SELECT * FROM ngos WHERE is_approved=1")->fetchAll();
$recentDonations = $db->query("SELECT donor_name, food_type, food_quantity, status, created_at FROM food_donations ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Donate Food – FoodHub | Help NGOs</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root { --green:#27ae60; --green-light:#2ecc71; --dark:#1a1a2e; --primary:#FF6B35; }
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; margin: 0; background: #f0fff4; color: var(--dark); }

/* HERO */
.donate-hero {
  background: linear-gradient(135deg, #0d4d0d 0%, #1a3a1a 50%, #0a2e0a 100%);
  padding: 100px 0 80px; text-align: center; position: relative; overflow: hidden;
}
.donate-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2327ae60' fill-opacity='0.06'%3E%3Cpath d='M20 20.5V18H0v5h20v20.5h5V23h20v-5H25V2.5h-5v18z'/%3E%3C/g%3E%3C/svg%3E");
}
.donate-hero-emoji { font-size: 80px; animation: heartbeat 1.5s ease-in-out infinite; }
@keyframes heartbeat { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); } }
.donate-hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(32px, 5vw, 56px); font-weight: 800; color: #fff;
  margin: 16px 0 12px;
}
.donate-hero-title em { color: #4ade80; font-style: italic; }
.donate-hero-sub { color: rgba(255,255,255,0.7); font-size: 17px; max-width: 560px; margin: 0 auto; line-height: 1.7; }
.hero-stats { display: flex; justify-content: center; gap: 48px; margin-top: 40px; flex-wrap: wrap; }
.hero-stat { text-align: center; color: #fff; }
.hero-stat-num { font-family: 'Playfair Display', serif; font-size: 36px; font-weight: 800; color: #4ade80; }
.hero-stat-lbl { font-size: 12px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; }

/* MAIN LAYOUT */
.donate-main { max-width: 1100px; margin: -40px auto 60px; padding: 0 16px; position: relative; z-index: 10; }

.donate-form-card {
  background: #fff; border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.12);
  overflow: hidden;
}
.donate-form-header {
  background: linear-gradient(135deg, var(--green), var(--green-light));
  padding: 28px 40px; display: flex; align-items: center; gap: 16px;
}
.donate-form-header h3 { color: #fff; font-family: 'Playfair Display', serif; font-size: 22px; margin: 0; }
.donate-form-header p { color: rgba(255,255,255,0.85); font-size: 14px; margin: 4px 0 0; }
.donate-form-header-icon { font-size: 40px; }

.donate-form-body { padding: 40px; }
.section-label {
  display: flex; align-items: center; gap: 10px;
  font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
  color: var(--green); margin-bottom: 20px; padding-bottom: 12px;
  border-bottom: 2px solid #e8f7ee;
}
.section-label i { font-size: 16px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
.form-row.single { grid-template-columns: 1fr; }
@media (max-width: 600px) { .form-row, .form-row.triple { grid-template-columns: 1fr; } }

.fg { display: flex; flex-direction: column; gap: 6px; }
.fg label { font-size: 13px; font-weight: 600; color: #555; }
.fg label .req { color: var(--primary); }
.inp-wrap { position: relative; }
.inp-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 14px; pointer-events: none; }
.inp-icon.top { top: 18px; transform: none; }
.fld {
  width: 100%; padding: 13px 16px 13px 42px;
  border: 2px solid #e8e8e8; border-radius: 12px;
  font-size: 14px; color: var(--dark); outline: none;
  transition: all 0.2s; font-family: 'DM Sans', sans-serif;
  background: #fafafa;
}
.fld:focus { border-color: var(--green); background: #fff; box-shadow: 0 0 0 4px rgba(39,174,96,0.08); }
.fld.textarea { height: 100px; resize: none; padding-top: 12px; }
.fld-hint { font-size: 11px; color: #aaa; }

/* Food type grid */
.food-type-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.food-type-chip {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 18px; border-radius: 50px;
  border: 2px solid #e8e8e8; background: #fff;
  font-size: 13px; font-weight: 600; cursor: pointer;
  transition: all 0.2s; color: #666; user-select: none;
}
.food-type-chip .chip-emoji { font-size: 18px; }
.food-type-chip.selected { background: #e8f7ee; border-color: var(--green); color: var(--green); }

/* NGO Selection */
.ngo-select-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
@media (max-width: 600px) { .ngo-select-grid { grid-template-columns: 1fr; } }
.ngo-card {
  border: 2px solid #e8e8e8; border-radius: 14px; padding: 16px;
  cursor: pointer; transition: all 0.2s; background: #fff;
}
.ngo-card:hover { border-color: var(--green); background: #f9fffe; }
.ngo-card.selected { border-color: var(--green); background: #e8f7ee; }
.ngo-card-name { font-weight: 700; font-size: 14px; color: var(--dark); }
.ngo-card-city { font-size: 12px; color: #888; }
.ngo-card-radio { width: 18px; height: 18px; accent-color: var(--green); }

/* Submit btn */
.btn-donate-submit {
  width: 100%; padding: 18px; border: none; border-radius: 16px;
  background: linear-gradient(135deg, var(--green), var(--green-light));
  color: #fff; font-size: 18px; font-weight: 700; cursor: pointer;
  transition: all 0.3s; font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; justify-content: center; gap: 12px;
  box-shadow: 0 8px 32px rgba(39,174,96,0.3);
}
.btn-donate-submit:hover { transform: translateY(-3px); box-shadow: 0 16px 48px rgba(39,174,96,0.4); }
.btn-donate-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

/* Sidebar */
.donate-sidebar { display: flex; flex-direction: column; gap: 20px; }
.sidebar-card {
  background: #fff; border-radius: 20px;
  padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.06);
}
.sidebar-card h5 { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--dark); margin-bottom: 16px; }
.impact-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
.impact-icon {
  width: 40px; height: 40px; border-radius: 10px;
  background: #e8f7ee; display: flex; align-items: center; justify-content: center;
  font-size: 18px; flex-shrink: 0;
}
.impact-title { font-weight: 700; font-size: 14px; color: var(--dark); margin-bottom: 2px; }
.impact-desc { font-size: 12px; color: #888; line-height: 1.5; }

.recent-donation { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
.recent-donation:last-child { border-bottom: none; padding-bottom: 0; }
.donation-avatar {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #e8f7ee, #b7f5d0);
  display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
}
.donation-name { font-weight: 600; font-size: 14px; color: var(--dark); }
.donation-detail { font-size: 12px; color: #888; }
.donation-time { font-size: 11px; color: #aaa; margin-left: auto; flex-shrink: 0; }

/* Alert */
.donate-alert { border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; display: none; }

/* Success state */
.success-state { text-align: center; padding: 48px 32px; display: none; }
.success-state .success-icon { font-size: 80px; margin-bottom: 24px; }
.success-state h3 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--green); margin-bottom: 12px; }
.success-state p { color: #555; font-size: 16px; max-width: 400px; margin: 0 auto 24px; }
</style>
</head>
<body>


<!-- NAVBAR -->
<nav style="background:rgba(26,26,46,0.97);padding:14px 0;position:sticky;top:0;z-index:100;">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../index.php" style="font-family:'Playfair Display',serif;font-size:24px;font-weight:800;color:#fff;text-decoration:none;">
      🍽️ Food<span style="color:#FF6B35;">Hub</span>
    </a>
    <div class="d-flex gap-3 align-items-center">
      <a href="../index.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;font-weight:600;">Home</a>
      <a href="../restaurants.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;font-weight:600;">Restaurants</a>
      <a href="../auth/login.php" style="background:linear-gradient(135deg,#FF6B35,#F7931E);color:#fff;padding:8px 20px;border-radius:50px;text-decoration:none;font-weight:700;font-size:14px;">Login</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="donate-hero">
  <div class="container position-relative">
    <div class="donate-hero-emoji">🤝</div>
    <h1 class="donate-hero-title">Share Food, <em>Spread Joy</em></h1>
    <p class="donate-hero-sub">
      Have leftover food from a wedding, event, or any occasion?
      Don't let it go to waste — connect with our NGO partners and feed someone who needs it.
    </p>
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-num">12,450</div>
        <div class="hero-stat-lbl">Meals Donated</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num">48</div>
        <div class="hero-stat-lbl">NGO Partners</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-num">3,200</div>
        <div class="hero-stat-lbl">Families Helped</div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="donate-main">
  <div class="row g-4 align-items-start">
    <!-- FORM COLUMN -->
    <div class="col-lg-8">
      <div class="donate-form-card">
        <div class="donate-form-header">
          <div class="donate-form-header-icon">🫶</div>
          <div>
            <h3>Food Donation Form</h3>
            <p>Fill in the details and we'll arrange free pickup by an NGO near you</p>
          </div>
        </div>
        <div class="donate-form-body">
          <!-- Alert -->
          <div id="donateAlert" class="donate-alert alert"></div>
          
          <!-- Success state (hidden by default) -->
          <div class="success-state" id="successState">
            <div class="success-icon">✅</div>
            <h3>Donation Request Submitted!</h3>
            <p>Thank you for your kindness! An NGO partner will contact you within 2 hours to arrange the food pickup.</p>
            <a href="../index.php" class="btn" style="background:linear-gradient(135deg,var(--green),var(--green-light));color:#fff;padding:14px 32px;border-radius:50px;font-weight:700;text-decoration:none;">
              Back to Home
            </a>
          </div>

          <!-- Donation Form -->
          <form id="donateForm">
            <!-- SECTION 1: Donor Info -->
            <div class="section-label"><i class="fas fa-user"></i> Your Information</div>
            <div class="form-row">
              <div class="fg">
                <label>Full Name <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-user inp-icon"></i>
                  <input type="text" name="donor_name" class="fld" placeholder="Your name" required>
                </div>
              </div>
              <div class="fg">
                <label>Email Address <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-envelope inp-icon"></i>
                  <input type="email" name="donor_email" class="fld" placeholder="your@email.com" required>
                </div>
              </div>
            </div>
            <div class="form-row single" style="margin-bottom:32px;">
              <div class="fg">
                <label>Contact Number <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-phone inp-icon"></i>
                  <input type="tel" name="donor_phone" class="fld" placeholder="+91 98765 43210" required>
                </div>
              </div>
            </div>

            <!-- SECTION 2: Food Details -->
            <div class="section-label"><i class="fas fa-utensils"></i> Food Details</div>
            
            <label style="font-size:13px;font-weight:600;color:#555;margin-bottom:10px;display:block;">Type of Food <span class="req">*</span></label>
            <div class="food-type-grid" id="foodTypeGrid">
              <?php
              $foodTypes = [
                ['emoji'=>'🍛','label'=>'Indian Food'],
                ['emoji'=>'🍕','label'=>'Pizza/Italian'],
                ['emoji'=>'🍱','label'=>'Packed Meals'],
                ['emoji'=>'🍚','label'=>'Rice/Biryani'],
                ['emoji'=>'🥗','label'=>'Salads/Veg'],
                ['emoji'=>'🍰','label'=>'Sweets/Desserts'],
                ['emoji'=>'🥪','label'=>'Sandwiches'],
                ['emoji'=>'🍜','label'=>'Curries/Gravies'],
              ];
              foreach($foodTypes as $ft):
              ?>
              <div class="food-type-chip" onclick="selectFoodType(this, '<?= $ft['label'] ?>')">
                <span class="chip-emoji"><?= $ft['emoji'] ?></span><?= $ft['label'] ?>
              </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" name="food_type" id="foodTypeInput" required>

            <div class="form-row triple">
              <div class="fg">
                <label>Food Quantity <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-weight inp-icon"></i>
                  <input type="text" name="food_quantity" class="fld" placeholder="e.g. 5 kg / 50 pieces" required>
                </div>
              </div>
              <div class="fg">
                <label>Approx. Serves (People)</label>
                <div class="inp-wrap">
                  <i class="fas fa-users inp-icon"></i>
                  <input type="number" name="serves_people" class="fld" placeholder="e.g. 30" min="1">
                </div>
              </div>
              <div class="fg">
                <label>Food Prepared Time</label>
                <div class="inp-wrap">
                  <i class="fas fa-clock inp-icon"></i>
                  <input type="time" name="prepared_time" class="fld">
                </div>
              </div>
            </div>

            <div class="form-row single" style="margin-bottom:32px;">
              <div class="fg">
                <label>Food Description (optional)</label>
                <div class="inp-wrap">
                  <i class="fas fa-info-circle inp-icon top"></i>
                  <textarea name="food_description" class="fld textarea" placeholder="Describe the food — cuisine type, ingredients, allergens, packaging, etc."></textarea>
                </div>
              </div>
            </div>

            <!-- SECTION 3: Pickup -->
            <div class="section-label"><i class="fas fa-map-marker-alt"></i> Pickup Information</div>
            <div class="form-row single">
              <div class="fg">
                <label>Pickup Address <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-map-marker-alt inp-icon top"></i>
                  <textarea name="pickup_address" class="fld textarea" placeholder="Full address where food can be picked up (venue, street, landmark)" required></textarea>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="fg">
                <label>City <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-city inp-icon"></i>
                  <select name="pickup_city" class="fld" required>
                    <option value="">Select City</option>
                    <option>Pune</option><option>Mumbai</option><option>Delhi</option>
                    <option>Bangalore</option><option>Chennai</option><option>Hyderabad</option>
                  </select>
                </div>
              </div>
              <div class="fg">
                <label>Your PIN Code</label>
                <div class="inp-wrap">
                  <i class="fas fa-map-pin inp-icon"></i>
                  <input type="text" name="pincode" class="fld" placeholder="e.g. 411001" maxlength="6">
                </div>
              </div>
            </div>
            <div class="form-row" style="margin-bottom:32px;">
              <div class="fg">
                <label>Pickup Date <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-calendar inp-icon"></i>
                  <input type="date" name="pickup_date" class="fld" id="pickupDate" required>
                </div>
              </div>
              <div class="fg">
                <label>Pickup Time <span class="req">*</span></label>
                <div class="inp-wrap">
                  <i class="fas fa-clock inp-icon"></i>
                  <input type="time" name="pickup_time" class="fld" required>
                </div>
              </div>
            </div>

            <!-- SECTION 4: NGO Selection -->
            <div class="section-label"><i class="fas fa-hands-helping"></i> Select NGO Partner</div>
            <p style="font-size:13px;color:#888;margin-bottom:16px;">Choose your preferred NGO or leave it for auto-assignment</p>
            <div class="ngo-select-grid">
              <div class="ngo-card" onclick="selectNGO(0, this)">
                <div class="d-flex align-items-center gap-12">
                  <input type="radio" name="ngo_id" value="" class="ngo-card-radio" checked>
                  <div>
                    <div class="ngo-card-name">🤖 Auto-Assign</div>
                    <div class="ngo-card-city">We'll find the nearest available NGO</div>
                  </div>
                </div>
              </div>
              <?php foreach($ngos as $ngo): ?>
              <div class="ngo-card" onclick="selectNGO(<?= $ngo['id'] ?>, this)">
                <div class="d-flex align-items-center gap-3">
                  <input type="radio" name="ngo_id" value="<?= $ngo['id'] ?>" class="ngo-card-radio">
                  <div>
                    <div class="ngo-card-name">🤝 <?= htmlspecialchars($ngo['ngo_name']) ?></div>
                    <div class="ngo-card-city"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ngo['city']) ?></div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Terms -->
            <div style="display:flex;align-items:flex-start;gap:10px;margin:24px 0;">
              <input type="checkbox" id="donateTerms" required style="width:18px;height:18px;margin-top:2px;accent-color:var(--green);cursor:pointer;flex-shrink:0;">
              <label for="donateTerms" style="font-size:13px;color:#666;">
                I confirm that the food is safe, freshly prepared, and I consent to an NGO collecting it at the provided location.
              </label>
            </div>

            <button type="submit" class="btn-donate-submit" id="donateSubmitBtn">
              <span style="font-size:22px;">🫶</span>
              Submit Donation Request
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- SIDEBAR -->
    <div class="col-lg-4">
      <div class="donate-sidebar">
        <!-- Why Donate -->
        <div class="sidebar-card">
          <h5>💚 Why Donate Food?</h5>
          <div class="impact-item">
            <div class="impact-icon">🍽️</div>
            <div>
              <div class="impact-title">Reduce Food Waste</div>
              <div class="impact-desc">India wastes 68 million tons of food annually. Every donation helps fight this crisis.</div>
            </div>
          </div>
          <div class="impact-item">
            <div class="impact-icon">👨‍👩‍👧‍👦</div>
            <div>
              <div class="impact-title">Feed Families</div>
              <div class="impact-desc">One wedding's leftovers can feed 50+ underprivileged families for a day.</div>
            </div>
          </div>
          <div class="impact-item">
            <div class="impact-icon">🌱</div>
            <div>
              <div class="impact-title">Help the Environment</div>
              <div class="impact-desc">Preventing food waste reduces greenhouse gas emissions significantly.</div>
            </div>
          </div>
          <div class="impact-item" style="margin-bottom:0;">
            <div class="impact-icon">😊</div>
            <div>
              <div class="impact-title">Free & Effortless</div>
              <div class="impact-desc">Our NGO partners handle the pickup — completely free of charge.</div>
            </div>
          </div>
        </div>

        <!-- Recent Donations -->
        <div class="sidebar-card">
          <h5>🕒 Recent Donations</h5>
          <?php if (empty($recentDonations)): ?>
          <p style="color:#aaa;font-size:14px;text-align:center;padding:20px 0;">Be the first to donate today!</p>
          <?php else: ?>
          <?php
          $emojiList = ['🍛','🍕','🍱','🍚','🥗','🍰','🥪','🍜'];
          foreach($recentDonations as $i => $rd):
            $timeAgo = time() - strtotime($rd['created_at']);
            if ($timeAgo < 3600) $ago = round($timeAgo/60) . 'm ago';
            elseif ($timeAgo < 86400) $ago = round($timeAgo/3600) . 'h ago';
            else $ago = round($timeAgo/86400) . 'd ago';
          ?>
          <div class="recent-donation">
            <div class="donation-avatar"><?= $emojiList[$i % count($emojiList)] ?></div>
            <div>
              <div class="donation-name">
                <?= htmlspecialchars(substr($rd['donor_name'],0,12)) ?>...
                <?php
                // Status badge
                $bColor = '#e8f7ee'; $tColor = '#1a7a40'; $lbl = 'Pending';
                if($rd['status'] == 'completed' || $rd['status'] == 'picked_up'){ $bColor = '#e8f7ee'; $tColor = '#1a7a40'; $lbl = 'Completed'; }
                elseif($rd['status'] == 'accepted'){ $bColor = '#fff3e0'; $tColor = '#e65100'; $lbl = 'Accepted'; }
                elseif($rd['status'] == 'pending'){ $bColor = '#e3f2fd'; $tColor = '#1565c0'; $lbl = 'Waiting'; }
                elseif($rd['status'] == 'cancelled'){ $bColor = '#fbe9e7'; $tColor = '#bf360c'; $lbl = 'Cancelled'; }
                ?>
                <span style="font-size:10px;background:<?= $bColor ?>;color:<?= $tColor ?>;padding:2px 6px;border-radius:4px;margin-left:4px;font-weight:700;"><i class="fas fa-circle me-1" style="font-size:6px;"></i><?= $lbl ?></span>
              </div>
              <div class="donation-detail"><?= htmlspecialchars($rd['food_type']) ?> • <?= htmlspecialchars($rd['food_quantity']) ?></div>
            </div>
            <div class="donation-time"><?= $ago ?></div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Quick Guide -->
        <div class="sidebar-card" style="background:linear-gradient(135deg,#1a3a1a,#0d4d0d);">
          <h5 style="color:#fff;">📋 Quick Guide</h5>
          <?php
          $steps = [
            ['icon'=>'1️⃣', 'title'=>'Fill the form', 'desc'=>'Enter your details and food information'],
            ['icon'=>'2️⃣', 'title'=>'NGO accepts', 'desc'=>'A nearby NGO reviews and accepts your request'],
            ['icon'=>'3️⃣', 'title'=>'Free pickup', 'desc'=>'Volunteers arrive at your location for collection'],
            ['icon'=>'4️⃣', 'title'=>'Food distributed', 'desc'=>'Needy people receive fresh, warm meals'],
          ];
          foreach($steps as $s):
          ?>
          <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;">
            <span style="font-size:20px;"><?= $s['icon'] ?></span>
            <div>
              <div style="color:#fff;font-weight:700;font-size:14px;"><?= $s['title'] ?></div>
              <div style="color:rgba(255,255,255,0.6);font-size:12px;"><?= $s['desc'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
          <a href="tel:+919876543210" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.1);border-radius:10px;padding:12px;color:#4ade80;text-decoration:none;font-weight:600;font-size:14px;margin-top:8px;">
            <i class="fas fa-phone"></i> Helpline: +91 98765 43210
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// Set min date to today
document.getElementById('pickupDate').min = new Date().toISOString().split('T')[0];

// Food type selection
const selectedFoodTypes = [];
function selectFoodType(el, type) {
    $(el).toggleClass('selected');
    const idx = selectedFoodTypes.indexOf(type);
    if (idx >= 0) selectedFoodTypes.splice(idx, 1);
    else selectedFoodTypes.push(type);
    $('#foodTypeInput').val(selectedFoodTypes.join(', '));
}

// NGO selection
function selectNGO(id, el) {
    $('.ngo-card').removeClass('selected');
    $(el).addClass('selected');
    $(el).find('input[type=radio]').prop('checked', true);
}

function showDonateAlert(type, msg) {
    $('#donateAlert').attr('class', `donate-alert alert alert-${type}`).html(msg).show();
    $('html,body').animate({scrollTop: $('#donateAlert').offset().top - 80}, 400);
}

$('#donateForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!$('#foodTypeInput').val()) {
        showDonateAlert('warning', '⚠️ Please select at least one food type.');
        return;
    }

    const $btn = $('#donateSubmitBtn');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');

    $.ajax({
        url: 'donate_handler.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#donateForm').slideUp(400);
                $('#successState').slideDown(400);
                $('html,body').animate({scrollTop: 0}, 400);
            } else {
                showDonateAlert('danger', '❌ ' + res.message);
                $btn.prop('disabled', false).html('<span style="font-size:22px;">🫶</span> Submit Donation Request');
            }
        },
        error: () => {
            showDonateAlert('danger', 'Network error. Please try again.');
            $btn.prop('disabled', false).html('<span style="font-size:22px;">🫶</span> Submit Donation Request');
        }
    });
});
</script>
</body>
</html>
