<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? (json_decode(file_get_contents('php://input'),true)['action'] ?? '');

// Apply coupon (public)
if ($action === 'apply_coupon') {
    $input = json_decode(file_get_contents('php://input'),true);
    $code = strtoupper(trim($input['code'] ?? ''));
    $subtotal = floatval($input['subtotal'] ?? 0);
    
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code=? AND is_active=1 AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) { echo json_encode(['success'=>false,'message'=>'Invalid or expired coupon']); exit; }
    if ($subtotal < $coupon['min_order']) {
        echo json_encode(['success'=>false,'message'=>'Minimum order ₹'.number_format($coupon['min_order']).' required']);
        exit;
    }
    
    $discount = 0;
    if ($coupon['discount_type'] === 'percent') {
        $discount = min($subtotal * $coupon['discount_value'] / 100);
    } else {
        $discount = $coupon['discount_value'];
    }
    
    echo json_encode(['success'=>true,'discount'=>round($discount,2),'message'=>'Coupon applied! You save ₹'.round($discount,2)]);
    exit;
}

// Get restaurants (for cart restaurant check)
if ($action === 'get_menu_item') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id, name, price, is_veg, is_available FROM menu_items WHERE id=? AND is_available=1");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    echo json_encode($item ?: ['error'=>'Item not found']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action']);
