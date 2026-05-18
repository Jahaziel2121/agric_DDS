<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

$product_id = $_POST['product_id'];
$qty = $_POST['qty'];
$total = $_POST['total'];

// Generate unique verification code
$verification_code = 'TXN-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

// Get the seller (farmer) ID from the product
$seller_q = $conn->query("SELECT user_id FROM products WHERE id='$product_id'");
$seller_row = $seller_q->fetch_assoc();
$seller_id = $seller_row['user_id'] ?? 0;

// Save order with verification code
$sql = "INSERT INTO orders (buyer_id, seller_id, product_id, quantity, total, status, verification_code)
        VALUES ('$buyer_id', '$seller_id', '$product_id', '$qty', '$total', 'Pending', '$verification_code')";

if ($conn->query($sql)) {
    $order_id = $conn->insert_id;
} else {
    die("Order failed: " . $conn->error);
}

// Get full order details for display
$query = "SELECT o.*, p.type, p.product_name, u.name AS farmer_name, u.phone AS farmer_phone
          FROM orders o
          JOIN products p ON o.product_id = p.id
          JOIN users u ON p.user_id = u.id
          WHERE o.id = '$order_id'";

$result = $conn->query($query);
$order = $result->fetch_assoc();

// Notify seller about the new order
$pname = $conn->real_escape_string($order['product_name'] ?: $order['type']);
$conn->query("INSERT INTO notifications (user_id, message) VALUES ('$seller_id', '<i class=\'fas fa-box\'></i> New order received! Product: $pname, Qty: $qty. Verification Code: $verification_code')");

// Notify buyer that order was placed
$conn->query("INSERT INTO notifications (user_id, message) VALUES ('$buyer_id', '<i class=\'fas fa-check-circle\'></i> Your order #$order_id for $pname has been placed! Contact the farmer to arrange delivery & payment.')");
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Placed</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); min-height: 100vh; }

.order-card {
    background: white;
    max-width: 480px;
    margin: 30px auto;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12);
}

.order-header {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    text-align: center;
    padding: 30px 20px;
}

.order-header .icon { font-size: 50px; }
.order-header h3 { margin: 10px 0 5px; }

.order-body {
    padding: 25px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}

.detail-row:last-of-type { border-bottom: none; }
.detail-row .label { color: #888; }
.detail-row .value { font-weight: 600; color: #333; }

.code-box {
    background: linear-gradient(135deg, #fff8e1, #fff3e0);
    border: 2px dashed #ff8f00;
    border-radius: 14px;
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.code-box .label {
    font-size: 12px;
    font-weight: 600;
    color: #e65100;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.code-box .code {
    font-size: 36px;
    font-weight: 800;
    color: #e65100;
    letter-spacing: 4px;
    margin: 8px 0;
}

.code-box .hint {
    font-size: 12px;
    color: #999;
}

.status-badge {
    display: inline-block;
    background: #fff3e0;
    color: #e65100;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.total-row {
    background: #f1f8e9;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0;
}

.total-row .amount {
    font-size: 22px;
    font-weight: 700;
    color: #2e7d32;
}

.info-box {
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 10px;
    padding: 14px;
    font-size: 13px;
    color: #1565c0;
    margin: 15px 0;
    line-height: 1.6;
}

.btn-main {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 13px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    text-decoration: none;
    display: block;
    text-align: center;
    margin-top: 8px;
    transition: 0.2s;
}

.btn-main:hover { color: white; transform: translateY(-1px); }

.btn-secondary {
    background: #f5f5f5;
    color: #555;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-size: 14px;
    width: 100%;
    cursor: pointer;
    text-decoration: none;
    display: block;
    text-align: center;
    margin-top: 8px;
}

.btn-secondary:hover { color: #333; background: #eee; }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="order-card">

    <div class="order-header">
        <div class="icon"><i class='fas fa-check-circle'></i></div>
        <h3>Order Placed Successfully!</h3>
        <p style="opacity: 0.85; margin: 0;">Your order has been sent to the farmer</p>
    </div>

    <div class="order-body">

        <div class="detail-row">
            <span class="label">Order ID</span>
            <span class="value">#<?= $order_id ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Product</span>
            <span class="value"><?= htmlspecialchars($order['product_name'] ?: $order['type']) ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Farmer</span>
            <span class="value"><?= htmlspecialchars($order['farmer_name']) ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Farmer Phone</span>
            <span class="value"><?= $order['farmer_phone'] ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Quantity</span>
            <span class="value"><?= $order['quantity'] ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Status</span>
            <span class="status-badge"><i class='fas fa-hourglass-half'></i> Pending Verification</span>
        </div>

        <div class="total-row">
            <span style="color: #555;">Total Amount</span>
            <span class="amount">GHS <?= number_format($order['total'], 2) ?></span>
        </div>

        <!-- VERIFICATION CODE -->
        <div class="code-box">
            <div class="label"><i class='fas fa-lock'></i> Verification Code</div>
            <div class="code"><?= $verification_code ?></div>
            <div class="hint">Share this code with the farmer after payment & delivery</div>
        </div>

        <div class="info-box">
            <b><i class='fas fa-clipboard-list'></i> How It Works:</b><br>
            1. Contact the farmer and arrange delivery & payment<br>
            2. After you receive the goods and pay, both you <b>AND</b> the farmer enter this code<br>
            3. Once <b>BOTH confirm</b>, the transaction is marked <b>Complete <i class='fas fa-check-circle'></i></b>
        </div>

        <!-- INLINE VERIFY FORM -->
        <div style="background: #f1f8e9; border-radius: 12px; padding: 20px; margin: 15px 0;">
            <p style="font-size: 14px; font-weight: 600; color: #2e7d32; margin: 0 0 10px;">
                <i class='fas fa-lock'></i> Verify This Transaction Now
            </p>
            <form method="POST" action="verify_transaction.php" style="display: flex; gap: 10px;">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <input type="text" name="verification_code" placeholder="e.g. TXN-XXXXXX" required maxlength="10"
                       style="flex:1; padding: 12px 15px; border: 1px solid #c8e6c9; border-radius: 8px; font-size: 14px; font-weight: 600; letter-spacing: 1px;">
                <button type="submit" name="verify" style="background: linear-gradient(135deg, #2e7d32, #43a047); color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; white-space: nowrap;">
                    <i class='fas fa-check'></i> Confirm
                </button>
            </form>
        </div>

        <!-- ACTION BUTTONS -->
        <div style="display: flex; gap: 10px;">
            <a href="chat.php?user_id=<?= $seller_id ?>" class="btn-main" style="flex: 1; background: linear-gradient(135deg, #1565c0, #1976d2);">
                <i class='fas fa-comment-dots'></i> Chat with Farmer
            </a>
            <a href="my_orders.php" class="btn-main" style="flex: 1;">
                <i class='fas fa-box'></i> My Orders
            </a>
        </div>
        <a href="browse_products.php" class="btn-secondary"><i class='fas fa-arrow-left'></i> Continue Shopping</a>
    </div>
</div>

</body>
</html>