<?php
include 'db.php';

$id = $_GET['id'];

$sql = "SELECT t.*, p.name 
        FROM buy_transactions t
        JOIN buy_products p ON t.product_id = p.id
        WHERE t.id='$id'";

$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
</head>
<body>

<h2>🌿 AGRICULTURE PURCHASE RECEIPT</h2>

<p><b>Product:</b> <?= $row['name'] ?></p>
<p><b>Quantity:</b> <?= $row['quantity'] ?></p>
<p><b>Total Price:</b> GHS <?= $row['total_price'] ?></p>

<p><b>Payment Method:</b> Pay on Delivery 🚚</p>

<p><b>Delivery Status:</b> <?= $row['delivery_status'] ?></p>

<p><b>Payment Status:</b> <?= $row['payment_status'] ?></p>

<p><b>Date:</b> <?= $row['created_at'] ?></p>

<button onclick="window.print()">Print Receipt</button>

</body>
</html>