<?php
session_start();
include 'db.php';

$buyer_id = $_SESSION['user_id'];

$sql = "SELECT c.*, p.type, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.buyer_id='$buyer_id'";

$result = $conn->query($sql);

$total = 0;

echo "<h2>Your Cart</h2>";

while($row = $result->fetch_assoc()) {

$subtotal = $row['price'] * $row['quantity'];
$total += $subtotal;

echo "
<p>{$row['type']} - {$row['quantity']} x {$row['price']} = {$subtotal}</p>
";
}

echo "<h3>Total: GHS $total</h3>";

echo "<a href='checkout.php'>Proceed to Checkout</a>";
?>