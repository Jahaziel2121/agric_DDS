<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$message = "";
$msg_type = "";

// Process verification
if (isset($_POST['verify'])) {
    $order_id = $_POST['order_id'];
    $code_entered = strtoupper(trim($_POST['verification_code']));

    // Fetch the order
    $order_q = $conn->query("SELECT * FROM orders WHERE id='$order_id'");
    $order = $order_q ? $order_q->fetch_assoc() : null;

    if (!$order) {
        $message = "<i class='fas fa-times-circle'></i> Order not found.";
        $msg_type = "error";
    } elseif ($code_entered !== $order['verification_code']) {
        $message = "<i class='fas fa-times-circle'></i> Invalid verification code. Please check and try again.";
        $msg_type = "error";
    } else {
        // Determine who is confirming
        $is_buyer = ($user_id == $order['buyer_id']);
        $is_seller = ($user_id == $order['seller_id']);

        if (!$is_buyer && !$is_seller) {
            $message = "<i class='fas fa-times-circle'></i> You are not part of this transaction.";
            $msg_type = "error";
        } else {
            if ($is_buyer && !$order['buyer_confirmed']) {
                $conn->query("UPDATE orders SET buyer_confirmed=1, buyer_confirmed_at=datetime('now') WHERE id='$order_id'");
                $order['buyer_confirmed'] = 1;
                $message = "<i class='fas fa-check-circle'></i> Buyer confirmation recorded!";
                $msg_type = "success";

                // Notify seller that buyer initiated verification
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ('{$order['seller_id']}', '<i class=\"fas fa-check-circle\"></i> Buyer has confirmed order #{$order_id}. Please confirm from your end to complete the transaction.')");

            } elseif ($is_seller && !$order['seller_confirmed']) {
                $conn->query("UPDATE orders SET seller_confirmed=1, seller_confirmed_at=datetime('now') WHERE id='$order_id'");
                $order['seller_confirmed'] = 1;
                $message = "<i class='fas fa-check-circle'></i> Seller confirmation recorded!";
                $msg_type = "success";

                // Notify buyer that farmer initiated verification
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ('{$order['buyer_id']}', '<i class=\"fas fa-check-circle\"></i> Farmer has confirmed order #{$order_id}. Please confirm from your end to complete the transaction.')");

            } elseif (($is_buyer && $order['buyer_confirmed']) || ($is_seller && $order['seller_confirmed'])) {
                $message = "<i class='fas fa-info-circle'></i> You have already confirmed this transaction.";
                $msg_type = "info";
            }

            // Re-fetch to check if both confirmed
            $updated = $conn->query("SELECT * FROM orders WHERE id='$order_id'")->fetch_assoc();

            if ($updated['buyer_confirmed'] && $updated['seller_confirmed']) {
                $conn->query("UPDATE orders SET status='Completed' WHERE id='$order_id'");

                // Record as a completed transaction
                $conn->query("INSERT INTO transactions (product_id, buyer_id, seller_id, quantity, total_price, status)
                    VALUES ('{$updated['product_id']}', '{$updated['buyer_id']}', '{$updated['seller_id']}', '{$updated['quantity']}', '{$updated['total']}', 'completed')");

                // Update product stock
                $conn->query("UPDATE products SET quantity = quantity - {$updated['quantity']} WHERE id='{$updated['product_id']}' AND quantity >= {$updated['quantity']}");

                // Get product name for notifications
                $prod_q = $conn->query("SELECT quantity, product_name, type FROM products WHERE id='{$updated['product_id']}'");
                $pname = 'Product';
                if ($prod_q && $prod = $prod_q->fetch_assoc()) {
                    $pname = $prod['product_name'] ?: $prod['type'];
                    // Check if out of stock to notify farmer
                    if ($prod['quantity'] <= 0) {
                        $safe_pname = $conn->real_escape_string($pname);
                        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('{$updated['seller_id']}', '<i class=\"fas fa-exclamation-triangle\"></i> Your product \"$safe_pname\" is now OUT OF STOCK.')");
                    }
                }
                $safe_pname = $conn->real_escape_string($pname);

                // Notify both parties — transaction completed
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ('{$updated['buyer_id']}', '<i class=\"fas fa-award\"></i> Transaction #{$order_id} for \"$safe_pname\" is now COMPLETE! Both parties have verified.')");
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ('{$updated['seller_id']}', '<i class=\"fas fa-award\"></i> Transaction #{$order_id} for \"$safe_pname\" is now COMPLETE! Both parties have verified.')");

                $message = "<i class='fas fa-award'></i> TRANSACTION COMPLETE! Both buyer and seller have confirmed. The transaction has been officially recorded.";
                $msg_type = "complete";
                $order = $updated;
                $order['status'] = 'Completed';
            }
        }
    }
}

// If this was submitted from an inline form (not on this page), redirect back
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $_SESSION['verify_msg'] = $message;
    $_SESSION['verify_msg_type'] = $msg_type;
    $redirect_to = ($role === 'farmer') ? 'farmer_orders.php' : 'my_orders.php';
    header("Location: $redirect_to");
    exit();
}

// Get user's orders (as buyer or seller)
$orders = $conn->query("
    SELECT o.*, 
           p.type, p.product_name, p.image,
           buyer.name AS buyer_name,
           seller.name AS seller_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN users buyer ON o.buyer_id = buyer.id
    LEFT JOIN users seller ON o.seller_id = seller.id
    WHERE o.buyer_id = '$user_id' OR o.seller_id = '$user_id'
    ORDER BY o.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify Transaction</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f2; }

.page-header {
    background: linear-gradient(135deg, #1b5e20, #2e7d32);
    color: white;
    padding: 25px;
    text-align: center;
    border-radius: 0 0 20px 20px;
    margin-bottom: 25px;
}

.order-item {
    background: white;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    border-left: 5px solid #e0e0e0;
    transition: 0.2s;
}

.order-item:hover { box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.order-item.pending { border-left-color: #ff8f00; }
.order-item.partial { border-left-color: #1565c0; }
.order-item.completed { border-left-color: #2e7d32; }

.order-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.order-id { font-weight: 700; font-size: 15px; color: #333; }

.status-pill {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-pill.pending { background: #fff3e0; color: #e65100; }
.status-pill.partial { background: #e3f2fd; color: #1565c0; }
.status-pill.completed { background: #e8f5e9; color: #2e7d32; }

.order-details {
    font-size: 14px;
    color: #666;
    line-height: 1.8;
}

.order-details strong { color: #333; }

.confirm-section {
    background: #fffde7;
    border: 1px solid #ffe082;
    border-radius: 10px;
    padding: 15px;
    margin-top: 12px;
}

.confirm-section .code-display {
    font-size: 20px;
    font-weight: 800;
    color: #e65100;
    letter-spacing: 3px;
    text-align: center;
    margin: 8px 0;
}

.confirm-row {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.confirm-row input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-align: center;
}

.btn-confirm {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.btn-confirm:hover { background: #1b5e20; }

.check-marks {
    display: flex;
    gap: 15px;
    margin-top: 12px;
}

.check-mark {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
}

.check-mark.done { color: #2e7d32; }
.check-mark.waiting { color: #bbb; }

.alert-msg {
    max-width: 700px;
    margin: 0 auto 15px;
    padding: 14px 18px;
    border-radius: 10px;
    font-weight: 500;
}

.alert-msg.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.alert-msg.error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
.alert-msg.info { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
.alert-msg.complete { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #1b5e20; border: 2px solid #66bb6a; font-size: 16px; text-align: center; }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="page-header">
    <h3><i class='fas fa-lock'></i> Transaction Verification</h3>
    <p style="opacity: 0.85; margin: 0;">Confirm your transactions with the verification code</p>
</div>

<div class="container" style="max-width: 700px;">

<?php if ($message): ?>
    <div class="alert-msg <?= $msg_type ?>"><?= $message ?></div>
<?php endif; ?>

<?php if ($orders && $orders->num_rows > 0): ?>
    <?php while ($o = $orders->fetch_assoc()):
        $is_buyer = ($user_id == $o['buyer_id']);
        $is_seller = ($user_id == $o['seller_id']);
        $my_confirmed = $is_buyer ? $o['buyer_confirmed'] : $o['seller_confirmed'];
        $other_confirmed = $is_buyer ? $o['seller_confirmed'] : $o['buyer_confirmed'];
        $both_done = $o['buyer_confirmed'] && $o['seller_confirmed'];

        // Determine visual state
        if ($both_done || $o['status'] === 'Completed') {
            $state = 'completed';
            $status_text = "<i class='fas fa-check-circle'></i> Completed";
        } elseif ($o['buyer_confirmed'] || $o['seller_confirmed']) {
            $state = 'partial';
            $status_text = "<i class='fas fa-sync-alt'></i> Partially Confirmed";
        } else {
            $state = 'pending';
            $status_text = "<i class='fas fa-hourglass-half'></i> Pending";
        }
    ?>

    <div class="order-item <?= $state ?>">
        <div class="order-top">
            <span class="order-id">
                <i class='fas fa-box'></i> Order #<?= $o['id'] ?> — <?= htmlspecialchars($o['product_name'] ?: $o['type']) ?>
            </span>
            <span class="status-pill <?= $state ?>"><?= $status_text ?></span>
        </div>

        <div class="order-details">
            <?php if ($is_buyer): ?>
                <strong>Farmer:</strong> <?= htmlspecialchars($o['seller_name'] ?? 'Unknown') ?> &nbsp;|&nbsp;
            <?php else: ?>
                <strong>Buyer:</strong> <?= htmlspecialchars($o['buyer_name'] ?? 'Unknown') ?> &nbsp;|&nbsp;
            <?php endif; ?>
            <strong>Qty:</strong> <?= $o['quantity'] ?> &nbsp;|&nbsp;
            <strong>Total:</strong> GHS <?= number_format($o['total'], 2) ?>
        </div>

        <!-- Confirmation Status Checks -->
        <div class="check-marks">
            <div class="check-mark <?= $o['buyer_confirmed'] ? 'done' : 'waiting' ?>">
                <?= $o['buyer_confirmed'] ? "<i class='fas fa-check-circle'></i>" : "<i class='far fa-circle'></i>" ?> Buyer Confirmed
            </div>
            <div class="check-mark <?= $o['seller_confirmed'] ? 'done' : 'waiting' ?>">
                <?= $o['seller_confirmed'] ? "<i class='fas fa-check-circle'></i>" : "<i class='far fa-circle'></i>" ?> Seller Confirmed
            </div>
        </div>

        <?php if (!$both_done && $o['status'] !== 'Completed'): ?>
            <div class="confirm-section">
                <?php if ($my_confirmed): ?>
                    <p style="text-align: center; color: #2e7d32; font-weight: 600; margin: 0;">
                        <i class='fas fa-check-circle'></i> You have confirmed. Waiting for the other party...
                    </p>
                <?php else: ?>
                    <p style="font-size: 13px; color: #e65100; margin-bottom: 8px;">
                        Enter the verification code to confirm this transaction:
                    </p>
                    <form method="POST" class="confirm-row">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <input type="text" name="verification_code" placeholder="e.g. TXN-A1B2C3" required maxlength="10">
                        <button type="submit" name="verify" class="btn-confirm">✔ Confirm</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 12px; padding: 10px; background: #e8f5e9; border-radius: 8px; color: #2e7d32; font-weight: 600;">
                <i class='fas fa-award'></i> Transaction Verified & Completed
            </div>
        <?php endif; ?>
    </div>

    <?php endwhile; ?>
<?php else: ?>
    <div style="text-align: center; padding: 50px; color: #aaa;">
        <div style="font-size: 50px;"><i class='fas fa-box-open'></i></div>
        <h4 style="color: #666;">No transactions yet</h4>
        <p>Your orders and sales will appear here.</p>
    </div>
<?php endif; ?>

</div>

</body>
</html>
