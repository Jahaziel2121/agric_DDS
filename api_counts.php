<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    echo json_encode(['notif' => 0, 'chat' => 0, 'orders' => 0]);
    exit();
}

$user_id = isset($_SESSION['admin']) ? 0 : $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

$notif_count = 0;
$chat_count = 0;
$order_count = 0;

// Count notifications
$nq = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id='$user_id' AND is_read=0");
if ($nq && $nr = $nq->fetch_assoc()) {
    $notif_count = (int)$nr['c'];
}

// Count chat messages
$cq = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id='$user_id' AND is_read=0");
if ($cq && $cr = $cq->fetch_assoc()) {
    $chat_count = (int)$cr['c'];
}

// Count pending orders
if ($role === 'farmer') {
    $oq = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE seller_id='$user_id' AND status='Pending'");
} else {
    $oq = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE buyer_id='$user_id' AND status='Pending'");
}
if ($oq && $or = $oq->fetch_assoc()) {
    $order_count = (int)$or['c'];
}

echo json_encode([
    'notif' => $notif_count,
    'chat' => $chat_count,
    'orders' => $order_count
]);
?>
