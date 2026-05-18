<?php
include 'db.php';

$request_id = $_POST['request_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$action = $_POST['action'];

if ($action == "approve") {

    // CHECK STOCK
    $product = $conn->query("SELECT quantity FROM products WHERE id='$product_id'");
    $row = $product->fetch_assoc();

    if ($row['quantity'] < $quantity) {
        die("Not enough stock");
    }

    // UPDATE REQUEST
    $conn->query("
        UPDATE buy_requests 
        SET status='approved', farmer_response='approved'
        WHERE id='$request_id'
    ");

    // REDUCE STOCK
    $newQty = $row['quantity'] - $quantity;

    $conn->query("
        UPDATE products 
        SET quantity='$newQty'
        WHERE id='$product_id'
    ");

    echo "Request approved successfully!";
}

elseif ($action == "reject") {

    $conn->query("
        UPDATE buy_requests 
        SET status='rejected', farmer_response='rejected'
        WHERE id='$request_id'
    ");

    echo "Request rejected!";
}
?>