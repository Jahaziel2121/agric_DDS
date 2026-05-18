<?php
include 'db.php';

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Invalid request");
}

$id = $_GET['id'];
$action = $_GET['action'];

// GET TRANSACTION DETAILS
$t = $conn->query("SELECT * FROM transactions WHERE id='$id'");

if (!$t || $t->num_rows == 0) {
    die("Transaction not found");
}

$data = $t->fetch_assoc();

$product_id = $data['product_id'];
$buyer_id = $data['buyer_id'];

// ==========================
// ACCEPT REQUEST
// ==========================
if ($action == 'accept') {

    // UPDATE TRANSACTION
    $conn->query("
        UPDATE transactions 
        SET buyer_response='accepted', status='completed' 
        WHERE id='$id'
    ");

    // GET SELLER (FOR NOW STATIC USER = 1)
    // Later replace with session login user
    $seller_id = 1;

    // NOTIFICATION MESSAGE
    $message = "Your product request has been ACCEPTED by the buyer.";

    $conn->query("
        INSERT INTO notifications (user_id, message) 
        VALUES ('$seller_id', '$message')
    ");

    echo "Request Accepted Successfully <i class='fas fa-check-circle'></i>";
}

// ==========================
// REJECT REQUEST
// ==========================
elseif ($action == 'reject') {

    $conn->query("
        UPDATE transactions 
        SET buyer_response='rejected', status='rejected' 
        WHERE id='$id'
    ");

    // NOTIFY SELLER
    $seller_id = 1;

    $message = "Your product request has been REJECTED by the buyer.";

    $conn->query("
        INSERT INTO notifications (user_id, message) 
        VALUES ('$seller_id', '$message')
    ");

    echo "Request Rejected <i class='fas fa-times-circle'></i>";
}

// ==========================
// INVALID ACTION
// ==========================
else {
    echo "Invalid action";
}
?>