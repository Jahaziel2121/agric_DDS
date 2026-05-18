<?php
include 'db.php';

// TEMP: assume buyer ID = 1 (later we use login)
$buyer_id = 1;

$sql = "SELECT t.*, p.name AS product_name 
FROM transactions t
JOIN products p ON t.product_id = p.id
WHERE t.buyer_id='$buyer_id' AND t.buyer_response='pending'";

$result = $conn->query($sql);

echo "<h2>Incoming Purchase Requests</h2>";

while ($row = $result->fetch_assoc()) {

    echo "
    <div style='border:1px solid #000; padding:10px; margin:10px;'>

        <p><b>Product:</b> {$row['product_name']}</p>
        <p><b>Quantity:</b> {$row['quantity']}</p>
        <p><b>Total Price:</b> GHS {$row['total_price']}</p>

        <a href='respond.php?id={$row['id']}&action=accept'>
            <button style='background:green;color:white;'>Accept</button>
        </a>

        <a href='respond.php?id={$row['id']}&action=reject'>
            <button style='background:red;color:white;'>Reject</button>
        </a>

    </div>
    ";
}
?>