<?php
include 'db.php';
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

// Get user ID
$res = $conn->query("SELECT user_id, amount FROM loans WHERE id='$id'");
if($res && $res->num_rows > 0) {
    $loan = $res->fetch_assoc();
    $farmer_id = $loan['user_id'];
    $amt = number_format($loan['amount'], 2);
    
    // Send Notification
    $msg = "Your loan request for GHS {$amt} has been Rejected by the Admin.";
    $date = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO notifications (user_id, message, date) VALUES ('$farmer_id', '$msg', '$date')");
}

$conn->query("UPDATE loans SET status='Rejected' WHERE id='$id'");

header("Location: admin_dashboard.php?tab=loans");
exit();
?>