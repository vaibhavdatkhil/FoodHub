<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['ngo_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not authorized']);
    exit;
}
$nid = $_SESSION['ngo_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'update_donation') {
    $donation_id = intval($_POST['donation_id']);
    $status = $_POST['status'];
    $allowed = ['accepted','picked_up','completed','cancelled'];
    if (!in_array($status, $allowed)) { echo json_encode(['success'=>false,'message'=>'Invalid status']); exit; }
    $stmt = $pdo->prepare("UPDATE food_donations SET status=? WHERE id=? AND ngo_id=?");
    $stmt->execute([$status, $donation_id, $nid]);
    echo json_encode(['success'=>$stmt->rowCount()>0]);
} elseif ($action === 'get_stats') {
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM food_donations WHERE ngo_id=? GROUP BY status");
    $stmt->execute([$nid]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action']);
}
