<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            o.id,
            o.quantity,
            o.total,
            o.status,
            o.created_at,
            o.verification_code,
            o.buyer_confirmed,
            o.seller_confirmed,
            p.type,
            p.product_name,
            p.unit,
            p.price,
            buyer.id AS buyer_id,
            buyer.name AS buyer_name,
            buyer.phone AS buyer_phone,
            buyer.location AS buyer_location
        FROM orders o
        JOIN products p ON o.product_id = p.id
        JOIN users buyer ON o.buyer_id = buyer.id
        WHERE o.seller_id = '$user_id' OR p.user_id = '$user_id'
        ORDER BY o.id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Incoming Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
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
    margin-bottom: 20px;
}

.container { width: 90%; max-width: 750px; margin: auto; }

.order-card {
    background: white;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    border-left: 5px solid #e0e0e0;
}

.order-card.pending { border-left-color: #ff8f00; }
.order-card.completed { border-left-color: #2e7d32; }
.order-card.partial { border-left-color: #1565c0; }

.order-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.order-top h4 { margin: 0; font-size: 16px; }

.pill {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.pill.pending { background: #fff3e0; color: #e65100; }
.pill.completed { background: #e8f5e9; color: #2e7d32; }
.pill.partial { background: #e3f2fd; color: #1565c0; }

.order-details { font-size: 14px; color: #555; line-height: 2; }
.order-details strong { color: #333; }

.code-box {
    background: #fffde7;
    border: 1px dashed #ff8f00;
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    margin: 10px 0;
}

.code-box .code {
    font-size: 22px;
    font-weight: 800;
    color: #e65100;
    letter-spacing: 3px;
}

.checks {
    display: flex;
    gap: 15px;
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
}

.checks .done { color: #2e7d32; }
.checks .wait { color: #ccc; }

.btn-verify {
    display: block;
    text-align: center;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    margin-top: 10px;
}

.btn-verify:hover { color: white; }

.done-badge {
    text-align: center;
    margin-top: 10px;
    padding: 10px;
    background: #e8f5e9;
    border-radius: 8px;
    color: #2e7d32;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #aaa;
}

.empty-state .icon { font-size: 50px; margin-bottom: 10px; }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="page-header">
    <h3><i class='fas fa-inbox'></i> Incoming Orders</h3>
    <p style="opacity: 0.85; margin: 0;">Orders from buyers for your farm products</p>
</div>

<div class="container">

<?php
// Show verification flash message
if (isset($_SESSION['verify_msg'])) {
    $vtype = $_SESSION['verify_msg_type'] ?? 'success';
    $vcolor = $vtype === 'error' ? '#c62828' : ($vtype === 'complete' ? '#2e7d32' : '#1565c0');
    $vbg = $vtype === 'error' ? '#ffebee' : ($vtype === 'complete' ? '#e8f5e9' : '#e3f2fd');
    echo "<div style='padding:15px; border-radius:10px; margin-bottom:15px; background:{$vbg}; color:{$vcolor}; font-weight:600; text-align:center;'>{$_SESSION['verify_msg']}</div>";
    unset($_SESSION['verify_msg'], $_SESSION['verify_msg_type']);
}
?>

<?php if ($result->num_rows > 0): ?>
    <?php while ($o = $result->fetch_assoc()):
        $both = $o['buyer_confirmed'] && $o['seller_confirmed'];

        if ($both || $o['status'] === 'Completed') {
            $state = 'completed';
            $label = "<i class='fas fa-check-circle'></i> Completed";
        } elseif ($o['buyer_confirmed'] || $o['seller_confirmed']) {
            $state = 'partial';
            $label = "<i class='fas fa-sync-alt'></i> Partially Confirmed";
        } else {
            $state = 'pending';
            $label = "<i class='fas fa-hourglass-half'></i> Pending";
        }

        $pname = $o['product_name'] ?: $o['type'];
    ?>

    <div class="order-card <?= $state ?>">
        <div class="order-top">
            <h4><i class='fas fa-box'></i> #<?= $o['id'] ?> — <?= htmlspecialchars($pname) ?></h4>
            <span class="pill <?= $state ?>"><?= $label ?></span>
        </div>

        <div class="order-details">
            <strong><i class='fas fa-shopping-cart'></i> Buyer:</strong> <?= htmlspecialchars($o['buyer_name']) ?><br>
            <strong><i class='fas fa-phone-alt'></i> Phone:</strong> <?= $o['buyer_phone'] ?><br>
            <strong><i class='fas fa-map-marker-alt'></i> Location:</strong> <?= htmlspecialchars($o['buyer_location']) ?><br>
            <strong><i class='fas fa-box'></i> Quantity:</strong> <?= $o['quantity'] ?> <?= $o['unit'] ?><br>
            <strong><i class='fas fa-money-bill-wave'></i> Total:</strong> GHS <?= number_format($o['total'], 2) ?>
        </div>

        <?php if (!empty($o['verification_code'])): ?>
            <div class="code-box">
                <div style="font-size: 11px; color: #999;"><i class='fas fa-lock'></i> Verification Code</div>
                <div class="code"><?= $o['verification_code'] ?></div>
            </div>

            <div class="checks">
                <span class="<?= $o['buyer_confirmed'] ? 'done' : 'wait' ?>"><?= $o['buyer_confirmed'] ? "<i class='fas fa-check-circle'></i>" : "<i class='far fa-circle'></i>" ?> Buyer</span>
                <span class="<?= $o['seller_confirmed'] ? 'done' : 'wait' ?>"><?= $o['seller_confirmed'] ? "<i class='fas fa-check-circle'></i>" : "<i class='far fa-circle'></i>" ?> You (Farmer)</span>
            </div>

            <?php if (!$both && $o['status'] !== 'Completed'): ?>
                <?php if (!$o['seller_confirmed']): ?>
                    <div style="background:#f1f8e9; border-radius:10px; padding:15px; margin-top:12px;">
                        <p style="font-size:13px; font-weight:600; color:#2e7d32; margin:0 0 8px;"><i class='fas fa-lock'></i> Verify This Order</p>
                        <form method="POST" action="verify_transaction.php" style="display:flex; gap:8px;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <input type="text" name="verification_code" placeholder="Enter code" required maxlength="10"
                                   style="flex:1; padding:10px 14px; border:1px solid #c8e6c9; border-radius:8px; font-size:14px; font-weight:600; letter-spacing:1px;">
                            <button type="submit" name="verify" style="background:linear-gradient(135deg,#2e7d32,#43a047); color:white; border:none; padding:10px 18px; border-radius:8px; font-weight:700; cursor:pointer; white-space:nowrap;">
                                <i class='fas fa-check'></i> Confirm
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="text-align:center; padding:10px; margin-top:10px; background:#e3f2fd; border-radius:8px; color:#1565c0; font-weight:600; font-size:13px;">
                        <i class='fas fa-check-circle'></i> You confirmed — waiting for buyer
                    </div>
                <?php endif; ?>
                <a href="chat.php?user_id=<?= $o['buyer_id'] ?>" style="display:block; text-align:center; background:linear-gradient(135deg,#1565c0,#1976d2); color:white; padding:10px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; margin-top:8px;">
                    <i class='fas fa-comment-dots'></i> Chat with Buyer
                </a>
            <?php else: ?>
                <div class="done-badge"><i class='fas fa-award'></i> Transaction Verified & Completed</div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($o['created_at']): ?>
            <p style="font-size: 12px; color: #aaa; margin-top: 8px;"><i class='far fa-calendar-alt'></i> <?= $o['created_at'] ?></p>
        <?php endif; ?>
    </div>

    <?php endwhile; ?>
<?php else: ?>
    <div class="empty-state">
        <div class="icon"><i class='fas fa-box-open'></i></div>
        <h4 style="color: #666;">No incoming orders yet</h4>
        <p>When buyers order your products, they will appear here.</p>
    </div>
<?php endif; ?>

</div>

</body>
</html>