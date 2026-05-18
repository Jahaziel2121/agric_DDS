<?php
include 'db.php';

$id = $_GET['id'];

$sql = "SELECT t.*, p.name AS product_name, u.name AS buyer_name
FROM transactions t
JOIN products p ON t.product_id = p.id
JOIN users u ON t.buyer_id = u.id
WHERE t.id='$id'";

$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        .receipt {
            width: 400px;
            margin: auto;
            padding: 20px;
            border: 2px solid #333;
            text-align: center;
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="receipt">

    <h2>AGRIC RECEIPT</h2>

    <p><b>Product:</b> <?= $row['product_name'] ?></p>
    <p><b>Buyer:</b> <?= $row['buyer_name'] ?></p>
    <p><b>Quantity:</b> <?= $row['quantity'] ?></p>
    <p><b>Total Price:</b> GHS <?= $row['total_price'] ?></p>
    <p><b>Status:</b> <?= $row['status'] ?></p>
    <p><b>Date:</b> <?= $row['created_at'] ?></p>

    <button onclick="window.print()">Print Receipt</button>

</div>

</body>
</html>