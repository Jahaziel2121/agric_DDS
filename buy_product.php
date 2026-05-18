<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get product ID + quantity
$product_id = $_POST['product_id'] ?? null;
$qty = $_POST['quantity'] ?? 1;

if (!$product_id) {
    die("Invalid request");
}

// Get product + farmer info
$sql = "SELECT p.*, u.name AS farmer_name, u.phone, u.location
        FROM products p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = '$product_id'";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Product not found");
}

$product = $result->fetch_assoc();

// Calculations
$total = $qty * $product['price'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Receipt</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f8;
}

.receipt {
    width: 420px;
    margin: 40px auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.header {
    text-align: center;
    border-bottom: 1px dashed #ccc;
    padding-bottom: 10px;
}

.header h2 {
    color: #2e7d32;
}

.info {
    margin-top: 15px;
    font-size: 14px;
}

.row {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
}

.total {
    font-size: 18px;
    font-weight: bold;
    color: #2e7d32;
    border-top: 1px dashed #ccc;
    margin-top: 15px;
    padding-top: 10px;
}

.badge {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    display: inline-block;
    margin-top: 10px;
}

.buttons {
    margin-top: 20px;
    text-align: center;
}

button {
    padding: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    margin-top: 8px;
    font-weight: bold;
}

.pay {
    background: #2e7d32;
    color: white;
}

.back {
    background: #ccc;
}
</style>

</head>

<body>

<div class="receipt">

    <div class="header">
        <h2><i class='fas fa-wheat-awn'></i> ORDER RECEIPT</h2>
        <p>Agri Market System</p>
    </div>

    <div class="info">

        <div class="row">
            <span>Product:</span>
            <strong><?= $product['type'] ?></strong>
        </div>

        <div class="row">
            <span>Farmer:</span>
            <span><?= $product['farmer_name'] ?></span>
        </div>

        <div class="row">
            <span>Location:</span>
            <span><?= $product['location'] ?></span>
        </div>

        <div class="row">
            <span>Price per unit:</span>
            <span>GHS <?= $product['price'] ?></span>
        </div>

        <div class="row">
            <span>Quantity:</span>
            <span><?= $qty ?> <?= $product['unit'] ?></span>
        </div>

        <div class="row">
            <span>Contact:</span>
            <span><?= $product['phone'] ?></span>
        </div>

        <div class="badge">Order Pending Confirmation</div>

        <div class="total">
            TOTAL: GHS <?= number_format($total, 2) ?>
        </div>

    </div>

    <div class="buttons">

        <!-- Payment Simulation -->
        <form method="POST" action="confirm_order.php">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <input type="hidden" name="qty" value="<?= $qty ?>">
            <input type="hidden" name="total" value="<?= $total ?>">

            <button class="pay"><i class='fas fa-shopping-cart'></i> Confirm Order</button>
        </form>

        <a href="browse_products.php">
            <button class="back" type="button">⬅ Back to Market</button>
        </a>

    </div>

</div>

</body>
</html>