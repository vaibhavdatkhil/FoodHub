<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['restaurant_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not authorized']);
    exit;
}
$rid = $_SESSION['restaurant_id'];

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'update_status':
        $order_id = intval($_POST['order_id']);
        $status = $_POST['status'];
        $allowed = ['placed','confirmed','preparing','out_for_delivery','delivered','cancelled'];
        if (!in_array($status, $allowed)) { echo json_encode(['success'=>false]); exit; }
        $stmt = $pdo->prepare("UPDATE orders SET order_status=? WHERE id=? AND restaurant_id=?");
        $stmt->execute([$status, $order_id, $rid]);
        echo json_encode(['success'=>$stmt->rowCount()>0]);
        break;

    case 'get_order_items':
        $order_id = intval($_GET['order_id']);
        $stmt = $pdo->prepare("SELECT oi.quantity, oi.subtotal, mi.name as item_name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id JOIN orders o ON oi.order_id=o.id WHERE oi.order_id=? AND o.restaurant_id=?");
        $stmt->execute([$order_id, $rid]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'get_menu':
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id=? ORDER BY category_id, name");
        $stmt->execute([$rid]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'add_menu_item':
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        if (!$name || $price <= 0) { echo json_encode(['success'=>false,'message'=>'Name and price required']); break; }
        $stmt = $pdo->prepare("INSERT INTO menu_items (restaurant_id, category_id, name, description, price, is_veg, is_bestseller, prep_time, is_available) VALUES (?,?,?,?,?,?,?,?,1)");
        $stmt->execute([
            $rid,
            intval($_POST['category_id'] ?? 0) ?: null,
            $name,
            trim($_POST['description'] ?? ''),
            $price,
            intval($_POST['is_veg'] ?? 1),
            intval($_POST['is_bestseller'] ?? 0),
            intval($_POST['prep_time'] ?? 20)
        ]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
        break;

    case 'delete_menu_item':
        $item_id = intval($_POST['item_id']);
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id=? AND restaurant_id=?");
        $stmt->execute([$item_id, $rid]);
        echo json_encode(['success'=>$stmt->rowCount()>0]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
}
