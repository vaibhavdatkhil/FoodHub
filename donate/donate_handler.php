<?php
// donate/donate_handler.php
require_once '../includes/config.php';
require_once '../includes/email.php';

header('Content-Type: application/json');

$db = getDB();

$donorName    = sanitize($_POST['donor_name'] ?? '');
$donorEmail   = filter_var($_POST['donor_email'] ?? '', FILTER_VALIDATE_EMAIL);
$donorPhone   = sanitize($_POST['donor_phone'] ?? '');
$foodType     = sanitize($_POST['food_type'] ?? '');
$foodQty      = sanitize($_POST['food_quantity'] ?? '');
$servePeople  = intval($_POST['serves_people'] ?? 0);
$pickupAddr   = sanitize($_POST['pickup_address'] ?? '');
$pickupCity   = sanitize($_POST['pickup_city'] ?? '');
$pickupDate   = $_POST['pickup_date'] ?? '';
$pickupTime   = $_POST['pickup_time'] ?? '';
$foodDesc     = sanitize($_POST['food_description'] ?? '');
$ngoId        = intval($_POST['ngo_id'] ?? 0) ?: null;

if (!$donorName || !$donorEmail || !$donorPhone || !$foodType || !$foodQty || !$pickupAddr || !$pickupCity || !$pickupDate || !$pickupTime) {
    jsonResponse(['success' => false, 'message' => 'Please fill all required fields.']);
}

if (!$donorEmail) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.']);
}

if (strtotime($pickupDate) < strtotime('today')) {
    jsonResponse(['success' => false, 'message' => 'Pickup date cannot be in the past.']);
}

$stmt = $db->prepare("INSERT INTO food_donations (donor_name, donor_email, donor_phone, food_type, food_quantity, serves_people, pickup_address, pickup_city, pickup_date, pickup_time, food_description, ngo_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
$stmt->execute([$donorName, $donorEmail, $donorPhone, $foodType, $foodQty, $servePeople, $pickupAddr, $pickupCity, $pickupDate, $pickupTime, $foodDesc, $ngoId]);

$donationData = [
    'food_type' => $foodType,
    'food_quantity' => $foodQty,
    'pickup_address' => $pickupAddr . ', ' . $pickupCity,
];

sendDonationConfirmation($donorEmail, $donorName, $donationData);

// Notify NGO if selected
if ($ngoId) {
    $ngoStmt = $db->prepare("SELECT email, ngo_name, contact_person FROM ngos WHERE id = ?");
    $ngoStmt->execute([$ngoId]);
    $ngo = $ngoStmt->fetch();
    if ($ngo) {
        $ngoSubject = "FoodHub - New Food Donation Available";
        $ngoHtml = "<h2>New Donation Alert!</h2><p>A food donation is available for pickup.</p><ul><li>Donor: $donorName</li><li>Food: $foodType — $foodQty</li><li>Pickup: $pickupAddr, $pickupCity</li><li>Date: $pickupDate at $pickupTime</li><li>Contact: $donorPhone</li></ul>";
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: FoodHub <" . SITE_EMAIL . ">\r\n";
        mail($ngo['email'], $ngoSubject, $ngoHtml, $headers);
    }
}

jsonResponse(['success' => true, 'message' => 'Donation submitted! Thank you for your generosity.']);
