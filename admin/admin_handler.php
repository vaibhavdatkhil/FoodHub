<?php
require_once '../includes/config.php';

// Handle logout BEFORE session check
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Must be logged in as admin for all other actions
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'message' => 'Not authorized.']);
}

$type   = $_POST['type']   ?? '';   // 'restaurant' or 'ngo'
$id     = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';   // 'approve', 'reject', 'revoke'

if (!$type || !$id || !$action) {
    jsonResponse(['success' => false, 'message' => 'Missing parameters.']);
}

$tables = ['restaurant' => 'restaurants', 'ngo' => 'ngos'];
$table  = $tables[$type] ?? null;

if (!$table) {
    jsonResponse(['success' => false, 'message' => 'Invalid type.']);
}

// Fetch the record
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    jsonResponse(['success' => false, 'message' => ucfirst($type) . ' not found.']);
}

try {
    switch ($action) {
        case 'approve':
            $pdo->prepare("UPDATE $table SET is_approved = 1 WHERE id = ?")->execute([$id]);
            $name = $type === 'restaurant' ? $record['restaurant_name'] : $record['ngo_name'];
            jsonResponse(['success' => true, 'message' => "✅ {$name} approved successfully!"]);
            break;

        case 'reject':
            // Delete the record (they can re-register) or just mark as rejected
            $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true, 'message' => ucfirst($type) . ' application rejected and removed.']);
            break;

        case 'revoke':
            $pdo->prepare("UPDATE $table SET is_approved = 0 WHERE id = ?")->execute([$id]);
            $name = $type === 'restaurant' ? $record['restaurant_name'] : $record['ngo_name'];
            jsonResponse(['success' => true, 'message' => "{$name} approval revoked."]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Unknown action.']);
    }
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
