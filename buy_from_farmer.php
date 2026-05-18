<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

$conn->query("
    INSERT INTO orders (buyer_id, product_id, quantity)
    VALUES ('$buyer_id', '$product_id', '$quantity')
");

echo "<script>
alert('Order placed successfully!');
window.location='browse_products.php';
</script>";