<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request");
}

/* =========================
   SAFE INPUT
========================= */
$company_id = isset($_POST['company_id']) ? $_POST['company_id'] : null;
$service_type = isset($_POST['service_type']) ? $_POST['service_type'] : '';
$service_name = isset($_POST['service_name']) ? $_POST['service_name'] : '';
$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
$total_price = isset($_POST['total_price']) ? $_POST['total_price'] : 0;

/* VALIDATION */
if (!$company_id || $quantity <= 0) {
    die("Invalid booking data");
}

/* =========================
   INSERT BOOKING
========================= */
$sql = "INSERT INTO bookings 
(company_id, service_type, service_name, quantity, total_price, status, created_at)
VALUES (?, ?, ?, ?, ?, 'pending', datetime('now'))";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL ERROR: " . $conn->error);
}

$stmt->bind_param(
    "issid",
    $company_id,
    $service_type,
    $service_name,
    $quantity,
    $total_price
);

if ($stmt->execute()) {

    $booking_id = $stmt->insert_id;

    header("Location: thank_you.php?booking_id=$booking_id");
    exit();

} else {
    die("Error: " . $stmt->error);
}
?>