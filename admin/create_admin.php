<?php
// admin/create_admin.php
// Run this ONCE to create/reset the admin account, then delete this file.
require_once '../includes/config.php';

$username = 'admin';
$email    = 'admin@foodhub.com';
$name     = 'Super Admin';
$password = 'Admin@1234';
$hash     = password_hash($password, PASSWORD_BCRYPT);

// Create table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Delete old admin and insert fresh
$pdo->exec("DELETE FROM admins WHERE username = 'admin'");
$stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $email, $hash, $name]);

echo '<div style="font-family:monospace;padding:2rem;background:#f0fff4;border:2px solid #27ae60;border-radius:12px;max-width:500px;margin:3rem auto">';
echo '<h2 style="color:#27ae60">✅ Admin account created!</h2>';
echo '<p><strong>Username:</strong> admin</p>';
echo '<p><strong>Password:</strong> Admin@1234</p>';
echo '<p><strong>Hash stored:</strong> ' . htmlspecialchars(substr($hash, 0, 30)) . '...</p>';
echo '<hr>';
echo '<p style="color:#e74c3c"><strong>⚠️ Delete this file now!</strong><br>C:\xampp\htdocs\foodhub\admin\create_admin.php</p>';
echo '<a href="index.php" style="display:inline-block;margin-top:1rem;padding:.75rem 2rem;background:#FF6B35;color:#fff;border-radius:50px;text-decoration:none;font-weight:700">→ Go to Admin Login</a>';
echo '</div>';
