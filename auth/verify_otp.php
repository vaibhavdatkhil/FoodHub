<?php
require_once '../includes/config.php';
$pending = $_SESSION['pending_verify'] ?? null;
if (!$pending) {
    echo "<script>window.location='../auth/login.php';</script>";
    exit;
}
$email = $pending['email'] ?? '';
$maskedEmail = substr($email, 0, 3) . str_repeat('*', max(0, strpos($email, '@') - 3)) . substr($email, strpos($email, '@'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verify Email – FoodHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root {
  --primary: #FF6B35;
  --primary-dark: #e85a2a;
  --secondary: #F7931E;
  --success: #27ae60;
  --dark: #1a1a2e;
}
* { box-sizing: border-box; }
body {
  font-family: 'DM Sans', sans-serif;
  background: linear-gradient(135deg, #fff7f0 0%, #fff 50%, #fff0f5 100%);
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
}
.verify-card {
  background: #fff;
  border-radius: 24px;
  padding: 48px 40px;
  box-shadow: 0 20px 60px rgba(255,107,53,0.12);
  max-width: 440px; width: 100%;
  text-align: center;
}
.verify-icon {
  width: 80px; height: 80px;
  background: linear-gradient(135deg, #FF6B35, #F7931E);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 24px;
  font-size: 32px; color: #fff;
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(255,107,53,0.4); }
  50% { box-shadow: 0 0 0 16px rgba(255,107,53,0); }
}
.brand { font-family: 'Playfair Display', serif; color: var(--primary); font-size: 28px; margin-bottom: 4px; }
.otp-inputs { display: flex; gap: 12px; justify-content: center; margin: 32px 0; }
.otp-inputs input {
  width: 52px; height: 58px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 24px; font-weight: 700;
  text-align: center; color: var(--dark);
  outline: none; transition: all 0.2s;
}
.otp-inputs input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(255,107,53,0.12); }
.otp-inputs input.filled { border-color: var(--primary); background: #fff7f0; }
.btn-verify {
  background: linear-gradient(135deg, #FF6B35, #F7931E);
  color: #fff; border: none;
  padding: 14px 40px; border-radius: 50px;
  font-size: 16px; font-weight: 600;
  width: 100%; cursor: pointer; transition: all 0.3s;
}
.btn-verify:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,107,53,0.3); }
.btn-verify:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.resend-link { color: var(--primary); cursor: pointer; font-weight: 600; }
.resend-link:hover { text-decoration: underline; }
#timer { font-weight: 600; color: var(--primary); }
#alertBox { display: none; margin-bottom: 16px; border-radius: 12px; }
</style>
</head>
<body>

<div class="verify-card">
  <div class="verify-icon"><i class="fas fa-envelope-open-text"></i></div>
  <div class="brand">FoodHub</div>
  <h3 class="fw-700 mt-2 mb-1" style="color:var(--dark)">Verify Your Email</h3>
  <p class="text-muted mb-0" style="font-size:14px">We sent a 6-digit OTP to<br><strong><?= $maskedEmail ?></strong></p>

  <div id="alertBox" class="alert" role="alert"></div>

  <div class="otp-inputs">
    <input type="text" maxlength="1" class="otp-digit" id="d1" data-index="0" inputmode="numeric">
    <input type="text" maxlength="1" class="otp-digit" id="d2" data-index="1" inputmode="numeric">
    <input type="text" maxlength="1" class="otp-digit" id="d3" data-index="2" inputmode="numeric">
    <input type="text" maxlength="1" class="otp-digit" id="d4" data-index="3" inputmode="numeric">
    <input type="text" maxlength="1" class="otp-digit" id="d5" data-index="4" inputmode="numeric">
    <input type="text" maxlength="1" class="otp-digit" id="d6" data-index="5" inputmode="numeric">
  </div>

  <button class="btn-verify" id="verifyBtn" onclick="verifyOTP()">
    <i class="fas fa-check-circle me-2"></i>Verify OTP
  </button>

  <p class="mt-4 text-muted" style="font-size:14px">
    Didn't receive it? 
    <span id="resendText">Resend in <span id="timer">2:00</span></span>
    <span id="resendBtn" style="display:none"><a class="resend-link" onclick="resendOTP()">Resend OTP</a></span>
  </p>
  <p class="mt-2" style="font-size:13px;color:#aaa">
    <a href="../auth/login.php" style="color:#aaa">← Back to Login</a>
  </p>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// OTP Input navigation
$('.otp-digit').on('input', function() {
    const val = $(this).val().replace(/[^0-9]/g,'');
    $(this).val(val);
    if (val) {
        $(this).addClass('filled');
        const next = parseInt($(this).data('index')) + 1;
        if (next < 6) $(`#d${next+1}`).focus();
    } else {
        $(this).removeClass('filled');
    }
});
$('.otp-digit').on('keydown', function(e) {
    if (e.key === 'Backspace' && !$(this).val()) {
        const prev = parseInt($(this).data('index')) - 1;
        if (prev >= 0) $(`#d${prev+1}`).focus().val('').removeClass('filled');
    }
});
// Paste handler
$('#d1').on('paste', function(e) {
    e.preventDefault();
    const text = (e.originalEvent.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
    text.split('').forEach((ch, i) => {
        $(`#d${i+1}`).val(ch).addClass('filled');
    });
    $(`#d${Math.min(text.length, 6)}`).focus();
});

function getOTPValue() {
    return $('.otp-digit').map(function() { return $(this).val(); }).get().join('');
}

function showAlert(type, msg) {
    $('#alertBox').attr('class', `alert alert-${type}`).text(msg).show();
}

function verifyOTP() {
    const otp = getOTPValue();
    if (otp.length < 6) { showAlert('warning', 'Please enter all 6 digits.'); return; }
    
    $('#verifyBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Verifying...');
    $.post('auth_handler.php', { action: 'verify_otp', otp }, function(res) {
        if (res.success) {
            showAlert('success', res.message);
            $('#verifyBtn').html('<i class="fas fa-check me-2"></i>Verified!');
            setTimeout(() => window.location.href = res.redirect, 1500);
        } else {
            showAlert('danger', res.message);
            $('#verifyBtn').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Verify OTP');
            $('.otp-digit').val('').removeClass('filled').first().focus();
        }
    }, 'json');
}

function resendOTP() {
    $.post('auth_handler.php', { action: 'resend_otp' }, function(res) {
        showAlert(res.success ? 'success' : 'danger', res.message);
        if (res.success) {
            $('#resendBtn').hide();
            $('#resendText').show();
            startTimer(120);
        }
    }, 'json');
}

let timerInterval;
function startTimer(seconds) {
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        $('#timer').text(`${m}:${s.toString().padStart(2,'0')}`);
        seconds--;
        if (seconds < 0) {
            clearInterval(timerInterval);
            $('#resendText').hide();
            $('#resendBtn').show();
        }
    }, 1000);
}
startTimer(120);
</script>
</body>
</html>
