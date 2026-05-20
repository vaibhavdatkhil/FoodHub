<?php
// includes/config.php — must be included at the very top of every file

// ── Database ──────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'foodhub_db');
define('DB_CHARSET', 'utf8mb4');

// ── Site ──────────────────────────────────────────────────
define('SITE_NAME', 'FoodHub');
define('SITE_URL', 'http://localhost/foodhub');
define('SITE_EMAIL', 'noreply@foodhub.com');

// ── Email / SMTP ───────────────────────────────────────────
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'prathmesh111801@gmail.com');
define('SMTP_PASS', 'Akola@321');

// ── App settings ──────────────────────────────────────────
define('OTP_EXPIRY_MINUTES', 10);
define('SESSION_TIMEOUT', 3600);

// ── Session (must happen before any output) ───────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    session_start();
}

// ── Database connection (singleton) ───────────────────────
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        catch (PDOException $e) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            }
            else {
                echo '<h3 style="color:red;font-family:monospace">Database Error: ' . htmlspecialchars($e->getMessage()) . '</h3>';
                echo '<p>Check DB_HOST, DB_USER, DB_PASS, DB_NAME in <b>includes/config.php</b></p>';
            }
            exit();
        }
    }
    return $pdo;
}

// ── Helpers ───────────────────────────────────────────────
function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateOTP($length = 6)
{
    return str_pad(random_int(0, (int)pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function isLoggedIn($role = null)
{
    if ($role === 'customer')
        return isset($_SESSION['customer_id']);
    if ($role === 'restaurant')
        return isset($_SESSION['restaurant_id']);
    if ($role === 'ngo')
        return isset($_SESSION['ngo_id']);
    return isset($_SESSION['customer_id'])
        || isset($_SESSION['restaurant_id'])
        || isset($_SESSION['ngo_id']);
}

function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

/**
 * Send a JSON response and terminate.
 * ob_end_clean() ensures no stray whitespace/warnings corrupt the JSON.
 */
function jsonResponse($data)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

// ── Global $pdo shortcut ──────────────────────────────────
$pdo = getDB();
