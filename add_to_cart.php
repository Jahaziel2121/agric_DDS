<?php
session_start();
include 'db.php';

$buyer_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

$conn->query("INSERT INTO cart (buyer_id, product_id, quantity)
VALUES ('$buyer_id','$product_id','$quantity')");

header("Location: cart.php");
exit();
?>