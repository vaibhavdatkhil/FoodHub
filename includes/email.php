<?php
// includes/email.php
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function getMailer() {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        // Check if credentials exist and aren't default
        if (SMTP_USER == 'your_email@gmail.com' || empty(SMTP_PASS)) {
            error_log("Please configure SMTP_USER and SMTP_PASS in config.php");
        }
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        $mail->setFrom(SMTP_USER, SITE_NAME);
        $mail->isHTML(true);
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer setup failed: " . $e->getMessage());
        return null;
    }
}

function sendOTPEmail($toEmail, $toName, $otp, $userType = 'customer') {
    $subject = "FoodHub - Email Verification OTP";
    
    $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:\'Segoe UI\',Arial,sans-serif;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#FF6B35,#F7931E);padding:40px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:32px;font-weight:800;">🍽️ FoodHub</h1>
    <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;font-size:15px;">Verify Your Email Address</p>
  </div>
  <div style="padding:40px;">
    <h2 style="color:#1a1a2e;margin:0 0 16px;font-size:22px;">Hello, ' . htmlspecialchars($toName) . '!</h2>
    <p style="color:#555;font-size:15px;line-height:1.6;margin:0 0 28px;">
      Thank you for registering with FoodHub as a <strong>' . ucfirst($userType) . '</strong>. 
      Please use the OTP below to verify your email address. This OTP is valid for <strong>' . OTP_EXPIRY_MINUTES . ' minutes</strong>.
    </p>
    <div style="background:#fff7f0;border:2px dashed #FF6B35;border-radius:12px;padding:28px;text-align:center;margin:0 0 28px;">
      <p style="margin:0 0 8px;color:#888;font-size:13px;text-transform:uppercase;letter-spacing:2px;">Your OTP Code</p>
      <div style="font-size:48px;font-weight:800;color:#FF6B35;letter-spacing:12px;">' . $otp . '</div>
    </div>
    <p style="color:#888;font-size:13px;text-align:center;margin:0;">
      If you did not create an account, please ignore this email.<br>
      Never share this OTP with anyone.
    </p>
  </div>
  <div style="background:#f8f9fa;padding:20px;text-align:center;">
    <p style="color:#aaa;font-size:12px;margin:0;">© ' . date('Y') . ' FoodHub. All rights reserved.</p>
  </div>
</div>
</body>
</html>';

    $mail = getMailer();
    if (!$mail) return false;

    try {
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("OTP Email failed: {$mail->ErrorInfo}");
        return false;
    }
}

function sendDonationConfirmation($toEmail, $toName, $donationData) {
    $subject = "FoodHub - Food Donation Received";
    
    $html = '<!DOCTYPE html>
<html>
<body style="background:#f0f4f8;font-family:\'Segoe UI\',Arial,sans-serif;margin:0;padding:0;">
<div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <div style="background:linear-gradient(135deg,#27ae60,#2ecc71);padding:40px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:32px;">🤝 FoodHub</h1>
    <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;">Food Donation Confirmed</p>
  </div>
  <div style="padding:40px;">
    <h2 style="color:#1a1a2e;">Thank you, ' . htmlspecialchars($toName) . '! 💚</h2>
    <p style="color:#555;line-height:1.6;">Your food donation has been received. An NGO will contact you shortly to arrange pickup.</p>
    <div style="background:#f0fff4;border-left:4px solid #27ae60;border-radius:4px;padding:20px;margin:24px 0;">
      <p style="margin:0;color:#155724;font-size:14px;"><strong>Food Type:</strong> ' . htmlspecialchars($donationData['food_type']) . '<br>
      <strong>Quantity:</strong> ' . htmlspecialchars($donationData['food_quantity']) . '<br>
      <strong>Pickup:</strong> ' . htmlspecialchars($donationData['pickup_address']) . '</p>
    </div>
  </div>
</div>
</body>
</html>';

    $mail = getMailer();
    if (!$mail) return false;

    try {
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Donation Confirmation Email failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>
