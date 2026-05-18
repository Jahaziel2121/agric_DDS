<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

$delivery_date = date('Y-m-d', strtotime('+3 days'));

/* =========================
   CHECK IF FORM WAS SUBMITTED
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $buyer_id = $_SESSION['user_id'] ?? null;
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if ($buyer_id && $product_id && $quantity) {

        $conn->query("
            INSERT INTO orders (buyer_id, product_id, quantity)
            VALUES ('$buyer_id', '$product_id', '$quantity')
        ");

        ?>

        <!DOCTYPE html>
        <html>
        <head>
            <title>Order Confirmation</title>

            <style>
                body {
                    font-family: Arial;
                    background: #f4f6f9;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }

                .box {
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 500px;
                }

                h2 { color: #2e7d32; }

                .date {
                    font-size: 18px;
                    font-weight: bold;
                    color: #1565c0;
                }

                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 10px 15px;
                    background: #2e7d32;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                }
            </style>
        </head>

        <body>

        <div class="box">
            <h2><i class='fas fa-award'></i> Thank You for Your Order!</h2>

            <p>Your order has been placed successfully.</p>

            <p>🚚 Delivery Date:</p>
            <p class="date"><?= $delivery_date ?></p>

            <p>Your products will arrive within 3 days.</p>

            <a href="dashboard.php">Go Back</a>
        </div>

        </body>
        </html>

        <?php
    } else {
        echo "<i class='fas fa-times-circle'></i> Missing order data or user not logged in.";
    }

} else {
    echo "
    <h3 style='text-align:center;color:red;margin-top:50px;'>
        This page is not meant to be opened directly.
    </h3>";
}
?>