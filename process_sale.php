<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$product_id = intval($_POST['product_id'] ?? 0);
$buyer_id   = intval($_POST['buyer_id'] ?? 0);
$quantity   = intval($_POST['quantity'] ?? 0);
$price_offer = floatval($_POST['price_offer'] ?? 0);

/* VALIDATION */
if ($product_id <= 0 || $buyer_id <= 0 || $quantity <= 0) {
    die("Missing or invalid data");
}

/* GET PRODUCT */
$product = $conn->query("SELECT * FROM products WHERE id=$product_id");

if (!$product || $product->num_rows == 0) {
    die("Product not found");
}

$row = $product->fetch_assoc();

if ($row['quantity'] < $quantity) {
    die("Not enough stock");
}

/* PRICE FIX */
if ($price_offer <= 0) {
    $price_offer = 10; // fallback
}

$total_price = $price_offer * $quantity;

/* INSERT TRANSACTION (SAFE) */
$stmt = $conn->prepare("
    INSERT INTO transactions 
    (product_id, buyer_id, quantity, unit_price, total_price, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'Completed', datetime('now'))
");

$stmt->bind_param(
    "iiidd",
    $product_id,
    $buyer_id,
    $quantity,
    $price_offer,
    $total_price
);

$stmt->execute();

/* GET REAL ID */
$transaction_id = $stmt->insert_id;

/* DEBUG SAFETY */
if (!$transaction_id) {
    die("<i class='fas fa-times-circle'></i> Insert failed - no transaction ID generated");
}

/* UPDATE STOCK */
$conn->query("UPDATE products SET quantity = quantity - $quantity WHERE id=$product_id");

/* REDIRECT */
header("Location: receipt.php?id=$transaction_id");
exit();
?>s