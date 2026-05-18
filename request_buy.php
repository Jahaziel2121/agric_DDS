<?php
include 'db.php';

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

/* GET PRODUCT */
$product = $conn->query("SELECT * FROM products WHERE id='$product_id'");
$row = $product->fetch_assoc();

if ($row['quantity'] < $quantity) {
    die("Not enough stock available");
}

/* SAVE REQUEST */
$conn->query("
INSERT INTO buy_requests (product_id, quantity, status)
VALUES ('$product_id', '$quantity', 'pending')
");

echo "Request sent to farmer successfully!";
?>