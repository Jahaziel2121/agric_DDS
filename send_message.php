<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message is empty']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
?>
