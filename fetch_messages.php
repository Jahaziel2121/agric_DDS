<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['contact_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$contact_id = intval($_GET['contact_id']);

// Mark incoming messages as read
$conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = '$contact_id' AND receiver_id = '$user_id'");

// Fetch messages between user and contact
$sql = "SELECT * FROM messages 
        WHERE (sender_id = '$user_id' AND receiver_id = '$contact_id') 
           OR (sender_id = '$contact_id' AND receiver_id = '$user_id') 
        ORDER BY created_at ASC";

$result = $conn->query($sql);
$messages = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_id' => $row['sender_id'],
            'message' => htmlspecialchars($row['message']),
            'time' => date('h:i A', strtotime($row['created_at'])),
            'is_mine' => ($row['sender_id'] == $user_id)
        ];
    }
}

echo json_encode(['status' => 'success', 'messages' => $messages]);
?>
