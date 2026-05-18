<?php
include 'db.php';

// get user id from URL
$id = $_GET['id'];

// delete user from database
$conn->query("DELETE FROM users WHERE id='$id'");

// redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>