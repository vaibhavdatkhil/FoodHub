<?php
// auth/auth_handler.php
require_once '../includes/config.php';
require_once '../includes/email.php';

// $pdo is available from config.php
// Action comes from POST (forms/AJAX) or GET (logout link)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ══════════════════════════════════════════════════════
    // CUSTOMER REGISTER
    // ══════════════════════════════════════════════════════
    case 'customer_register':
        $name     = sanitize($_POST['full_name']        ?? '');
        $email    = trim($_POST['email']                ?? '');
        $phone    = sanitize($_POST['phone']            ?? '');
        $city     = sanitize($_POST['city']             ?? '');
        $password = $_POST['password']                  ?? '';
        $confirm  = $_POST['confirm_password']          ?? '';

        // Validate
        if (!$name || !$email || !$phone || !$password)
            jsonResponse(['success' => false, 'message' => 'All fields are required.']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['success' => false, 'message' => 'Invalid email address.']);

        if (strlen($password) < 8)
            jsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters.']);

        if ($password !== $confirm)
            jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);

        // Duplicate check
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch())
            jsonResponse(['success' => false, 'message' => 'This email is already registered. Try logging in.']);

        // Insert
        try {
            $otp        = generateOTP();
            $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            $hash       = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                "INSERT INTO customers (full_name, email, phone, city, password_hash, otp_code, otp_expires, is_verified)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0)"
            );
            $stmt->execute([$name, $email, $phone, $city, $hash, $otp, $otpExpires]);
            $customerId = $pdo->lastInsertId();
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }

        // Store session for OTP verification
        $_SESSION['pending_verify'] = [
            'id'    => $customerId,
            'email' => $email,
            'type'  => 'customer',
        ];

        // Send OTP email (non-blocking — we still proceed even if mail fails on localhost)
        $mailSent = sendOTPEmail($email, $name, $otp, 'customer');

        jsonResponse([
            'success'  => true,
            'message'  => $mailSent
                ? 'Account created! Check your email for the OTP.'
                : 'Account created! Your OTP is: <strong>' . $otp . '</strong> (email delivery unavailable on localhost)',
            'redirect' => 'verify_otp.php',
        ]);
        break;

    // ══════════════════════════════════════════════════════
    // RESTAURANT REGISTER
    // ══════════════════════════════════════════════════════
    case 'restaurant_register':
        $ownerName = sanitize($_POST['owner_name']      ?? '');
        $restName  = sanitize($_POST['restaurant_name'] ?? '');
        $email     = trim($_POST['email']               ?? '');
        $phone     = sanitize($_POST['phone']           ?? '');
        $password  = $_POST['password']                 ?? '';
        $confirm   = $_POST['confirm_password']         ?? '';
        $address   = sanitize($_POST['address']         ?? '');
        $city      = sanitize($_POST['city']            ?? '');
        $cuisine   = sanitize($_POST['cuisine_type']    ?? '');

        if (!$ownerName || !$restName || !$email || !$phone || !$password || !$address || !$city)
            jsonResponse(['success' => false, 'message' => 'All required fields must be filled.']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['success' => false, 'message' => 'Invalid email address.']);

        if ($password !== $confirm)
            jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);

        $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch())
            jsonResponse(['success' => false, 'message' => 'This email is already registered.']);

        try {
            $otp        = generateOTP();
            $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            $hash       = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                "INSERT INTO restaurants
                 (owner_name, restaurant_name, email, phone, password_hash, address, city, cuisine_type, otp_code, otp_expires, is_verified, is_approved)
                 VALUES (?,?,?,?,?,?,?,?,?,?,0,0)"
            );
            $stmt->execute([$ownerName, $restName, $email, $phone, $hash, $address, $city, $cuisine, $otp, $otpExpires]);
            $restId = $pdo->lastInsertId();
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }

        $_SESSION['pending_verify'] = ['id' => $restId, 'email' => $email, 'type' => 'restaurant'];
        $mailSent = sendOTPEmail($email, $ownerName, $otp, 'restaurant');

        jsonResponse([
            'success'  => true,
            'message'  => $mailSent
                ? 'Restaurant registered! Check your email for the OTP.'
                : 'Restaurant registered! Your OTP is: <strong>' . $otp . '</strong>',
            'redirect' => 'verify_otp.php',
        ]);
        break;

    // ══════════════════════════════════════════════════════
    // NGO REGISTER
    // ══════════════════════════════════════════════════════
    case 'ngo_register':
        $ngoName = sanitize($_POST['ngo_name']            ?? '');
        $contact = sanitize($_POST['contact_person']      ?? '');
        $email   = trim($_POST['email']                   ?? '');
        $phone   = sanitize($_POST['phone']               ?? '');
        $password= $_POST['password']                     ?? '';
        $confirm = $_POST['confirm_password']             ?? '';
        $address = sanitize($_POST['address']             ?? '');
        $city    = sanitize($_POST['city']                ?? '');
        $regNo   = sanitize($_POST['registration_number'] ?? '');
        $desc    = sanitize($_POST['description']         ?? '');

        if (!$ngoName || !$contact || !$email || !$phone || !$password || !$address || !$city)
            jsonResponse(['success' => false, 'message' => 'All required fields must be filled.']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['success' => false, 'message' => 'Invalid email address.']);

        if ($password !== $confirm)
            jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);

        $stmt = $pdo->prepare("SELECT id FROM ngos WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch())
            jsonResponse(['success' => false, 'message' => 'This email is already registered.']);

        try {
            $otp        = generateOTP();
            $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            $hash       = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                "INSERT INTO ngos
                 (ngo_name, contact_person, email, phone, password_hash, address, city, registration_number, description, otp_code, otp_expires, is_verified, is_approved)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,0,0)"
            );
            $stmt->execute([$ngoName, $contact, $email, $phone, $hash, $address, $city, $regNo, $desc, $otp, $otpExpires]);
            $ngoId = $pdo->lastInsertId();
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }

        $_SESSION['pending_verify'] = ['id' => $ngoId, 'email' => $email, 'type' => 'ngo'];
        $mailSent = sendOTPEmail($email, $ngoName, $otp, 'ngo');

        jsonResponse([
            'success'  => true,
            'message'  => $mailSent
                ? 'NGO registered! Check your email for the OTP.'
                : 'NGO registered! Your OTP is: <strong>' . $otp . '</strong>',
            'redirect' => 'verify_otp.php',
        ]);
        break;

    // ══════════════════════════════════════════════════════
    // VERIFY OTP
    // ══════════════════════════════════════════════════════
    case 'verify_otp':
        $otp     = sanitize($_POST['otp'] ?? '');
        $pending = $_SESSION['pending_verify'] ?? null;

        if (!$pending)
            jsonResponse(['success' => false, 'message' => 'Session expired. Please register again.']);

        $id    = $pending['id'];
        $type  = $pending['type'];
        $now   = date('Y-m-d H:i:s');
        $tables = ['customer' => 'customers', 'restaurant' => 'restaurants', 'ngo' => 'ngos'];
        $table  = $tables[$type] ?? null;

        if (!$table)
            jsonResponse(['success' => false, 'message' => 'Invalid session type.']);

        $stmt = $pdo->prepare("SELECT otp_code, otp_expires FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row)
            jsonResponse(['success' => false, 'message' => 'User not found.']);
        if ($row['otp_expires'] < $now)
            jsonResponse(['success' => false, 'message' => 'OTP has expired. Click Resend OTP.']);
        if ($row['otp_code'] !== $otp)
            jsonResponse(['success' => false, 'message' => 'Incorrect OTP. Please try again.']);

        // Mark verified
        $pdo->prepare("UPDATE $table SET is_verified = 1, otp_code = NULL, otp_expires = NULL WHERE id = ?")->execute([$id]);
        unset($_SESSION['pending_verify']);

        // Set session so user is logged in immediately after verification
        if ($type === 'customer') {
            $u = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $u->execute([$id]);
            $user = $u->fetch();
            $_SESSION['customer_id']    = $user['id'];
            $_SESSION['customer_name']  = $user['full_name'];
            $_SESSION['customer_email'] = $user['email'];
        } elseif ($type === 'restaurant') {
            $u = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
            $u->execute([$id]);
            $user = $u->fetch();
            $_SESSION['restaurant_id']    = $user['id'];
            $_SESSION['restaurant_name']  = $user['restaurant_name'];
            $_SESSION['restaurant_email'] = $user['email'];
        } else {
            $u = $pdo->prepare("SELECT * FROM ngos WHERE id = ?");
            $u->execute([$id]);
            $user = $u->fetch();
            $_SESSION['ngo_id']    = $user['id'];
            $_SESSION['ngo_name']  = $user['ngo_name'];
            $_SESSION['ngo_email'] = $user['email'];
        }

        $redirectMap = [
            'customer'   => '../index.php',
            'restaurant' => '../restaurant/dashboard.php',
            'ngo'        => '../ngo/dashboard.php',
        ];

        jsonResponse(['success' => true, 'message' => 'Email verified! Welcome to FoodHub 🎉', 'redirect' => $redirectMap[$type]]);
        break;

    // ══════════════════════════════════════════════════════
    // RESEND OTP
    // ══════════════════════════════════════════════════════
    case 'resend_otp':
        $pending = $_SESSION['pending_verify'] ?? null;
        if (!$pending)
            jsonResponse(['success' => false, 'message' => 'Session expired. Please register again.']);

        $id    = $pending['id'];
        $type  = $pending['type'];
        $tables = ['customer' => 'customers', 'restaurant' => 'restaurants', 'ngo' => 'ngos'];
        $table  = $tables[$type];

        // Fetch name/email
        $nameCol = ($type === 'customer') ? 'full_name' : (($type === 'restaurant') ? 'owner_name' : 'contact_person');
        $stmt = $pdo->prepare("SELECT email, $nameCol AS display_name FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $otp        = generateOTP();
        $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        $pdo->prepare("UPDATE $table SET otp_code = ?, otp_expires = ? WHERE id = ?")->execute([$otp, $otpExpires, $id]);

        $mailSent = sendOTPEmail($row['email'], $row['display_name'], $otp, $type);
        jsonResponse([
            'success' => true,
            'message' => $mailSent
                ? 'New OTP sent to ' . $row['email']
                : 'New OTP (email unavailable): <strong>' . $otp . '</strong>',
        ]);
        break;

    // ══════════════════════════════════════════════════════
    // LOGIN
    // ══════════════════════════════════════════════════════
    case 'login':
        $email    = trim($_POST['email']     ?? '');
        $password = $_POST['password']       ?? '';
        $type     = sanitize($_POST['user_type'] ?? 'customer');
        $remember = isset($_POST['remember']);

        if (!$email || !$password)
            jsonResponse(['success' => false, 'message' => 'Email and password are required.']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['success' => false, 'message' => 'Invalid email address.']);

        $tables = ['customer' => 'customers', 'restaurant' => 'restaurants', 'ngo' => 'ngos'];
        $table  = $tables[$type] ?? 'customers';

        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash']))
            jsonResponse(['success' => false, 'message' => 'Invalid email or password.']);

        if (!$user['is_verified']) {
            // Resend OTP and send to verify page
            $_SESSION['pending_verify'] = ['id' => $user['id'], 'email' => $email, 'type' => $type];
            jsonResponse(['success' => false, 'message' => 'Please verify your email first. We\'ve resent your OTP.', 'redirect' => 'verify_otp.php']);
        }

        if (($type === 'restaurant' || $type === 'ngo') && !$user['is_approved'])
            jsonResponse(['success' => false, 'message' => 'Your account is pending admin approval. We\'ll notify you by email.']);

        // Set session
        session_regenerate_id(true);
        if ($type === 'customer') {
            $_SESSION['customer_id']    = $user['id'];
            $_SESSION['customer_name']  = $user['full_name'];
            $_SESSION['customer_email'] = $user['email'];
        } elseif ($type === 'restaurant') {
            $_SESSION['restaurant_id']    = $user['id'];
            $_SESSION['restaurant_name']  = $user['restaurant_name'];
            $_SESSION['restaurant_email'] = $user['email'];
        } else {
            $_SESSION['ngo_id']    = $user['id'];
            $_SESSION['ngo_name']  = $user['ngo_name'];
            $_SESSION['ngo_email'] = $user['email'];
        }

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_' . $type, $token, time() + (30 * 24 * 3600), '/', '', false, true);
        }

        $redirectMap = [
            'customer'   => '../index.php',
            'restaurant' => '../restaurant/dashboard.php',
            'ngo'        => '../ngo/dashboard.php',
        ];

        jsonResponse(['success' => true, 'message' => 'Login successful! Redirecting...', 'redirect' => $redirectMap[$type]]);
        break;

    // ══════════════════════════════════════════════════════
    // LOGOUT
    // ══════════════════════════════════════════════════════
    case 'logout':
        session_destroy();
        foreach (['customer', 'restaurant', 'ngo'] as $t) {
            setcookie('remember_' . $t, '', time() - 3600, '/');
        }
        // AJAX call → return JSON; direct link → redirect
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            jsonResponse(['success' => true, 'redirect' => '../index.php']);
        } else {
            header('Location: ../index.php');
            exit();
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action: ' . htmlspecialchars($action)]);
}
