<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in. Please login first.']);
    exit;
}
$cid = $_SESSION['customer_id'];

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'place_order':  placeOrder($input, $cid);           break;
    case 'cancel_order': cancelOrder($input['order_id'] ?? 0, $cid); break;
    default: echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
}

// ─────────────────────────────────────────────────────────
function placeOrder($data, $cid) {
    global $pdo;
    try {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
            return;
        }

        $pdo->beginTransaction();

        // ── Insert order ──────────────────────────────────
        // 10 columns  →  10 ? placeholders  (order_status has a DEFAULT in the table)
        $order_number = 'FH-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $stmt = $pdo->prepare("
            INSERT INTO orders
                (order_number, customer_id, restaurant_id,
                 total_amount, delivery_fee, discount, final_amount,
                 payment_method, delivery_address, special_instructions)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_number,
            $cid,
            intval($data['restaurant_id']   ?? 0),
            floatval($data['subtotal']      ?? 0),
            floatval($data['delivery_fee']  ?? 40),
            floatval($data['discount']      ?? 0),
            floatval($data['total']         ?? 0),
            $data['payment_method']         ?? 'cod',
            $data['delivery_address']       ?? '',
            $data['instructions']           ?? null,
        ]);
        $order_id = $pdo->lastInsertId();

        // ── Insert order items ────────────────────────────
        // order_items schema: id, order_id, menu_item_id, item_name, item_price, quantity, subtotal
        // That is 6 data columns → 6 ? placeholders
        $stmt2 = $pdo->prepare("
            INSERT INTO order_items
                (order_id, menu_item_id, item_name, item_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($items as $item) {
            $qty   = intval($item['qty']      ?? $item['quantity'] ?? 1);
            $price = floatval($item['price']  ?? 0);
            $stmt2->execute([
                $order_id,
                intval($item['id']            ?? 0),
                $item['name']                 ?? 'Item',
                $price,
                $qty,
                $price * $qty,
            ]);
        }

        $pdo->commit();
        echo json_encode([
            'success'      => true,
            'order_number' => $order_number,
            'order_id'     => $order_id,
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

// ─────────────────────────────────────────────────────────
function cancelOrder($order_id, $cid) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE orders SET order_status = 'cancelled'
        WHERE id = ? AND customer_id = ?
          AND order_status IN ('placed', 'confirmed')
    ");
    $stmt->execute([intval($order_id), $cid]);
    echo json_encode(['success' => $stmt->rowCount() > 0]);
}
