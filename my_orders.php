<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id'])) {
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
            u.id AS farmer_id,
            u.name AS farmer_name,
            u.phone AS farmer_phone
        FROM orders o
        JOIN products p ON o.product_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE o.buyer_id = '$user_id'
        ORDER BY o.id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f2; }

.container { width: 90%; max-width: 750px; margin: auto; padding-top: 20px; }

.card {
    background: white;
    padding: 20px;
    margin: 12px 0;
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    border-left: 5px solid #e0e0e0;
}

.card.pending { border-left-color: #ff8f00; }
.card.completed { border-left-color: #2e7d32; }
.card.partial { border-left-color: #1565c0; }

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card h3 { margin: 0 0 5px; font-size: 16px; }

.status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status.pending { background: #fff3e0; color: #e65100; }
.status.completed { background: #e8f5e9; color: #2e7d32; }
.status.partial { background: #e3f2fd; color: #1565c0; }

.card p { margin: 4px 0; font-size: 14px; color: #555; }

.code-display {
    background: #fffde7;
    border: 1px dashed #ff8f00;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    margin: 10px 0;
}

.code-display .code {
    font-size: 22px;
    font-weight: 800;
    color: #e65100;
    letter-spacing: 3px;
}

.code-display .hint {
    font-size: 11px;
    color: #999;
}

.checks {
    display: flex;
    gap: 12px;
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
}

.checks .done { color: #2e7d32; }
.checks .waiting { color: #ccc; }

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
    transition: 0.2s;
}

.btn-verify:hover { color: white; transform: translateY(-1px); }
</style>

</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container">

<h2><i class='fas fa-box'></i> My Orders</h2>

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

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $both = $row['buyer_confirmed'] && $row['seller_confirmed'];
        
        if ($both || $row['status'] === 'Completed') {
            $state = 'completed';
            $status_label = "<i class='fas fa-check-circle'></i> Completed";
        } elseif ($row['buyer_confirmed'] || $row['seller_confirmed']) {
            $state = 'partial';
            $status_label = "<i class='fas fa-sync-alt'></i> Awaiting Other Party";
        } else {
            $state = 'pending';
            $status_label = "<i class='fas fa-hourglass-half'></i> Pending";
        }

        $product_name = $row['product_name'] ?: $row['type'];
        echo "<div class='card {$state}'>";
        echo "<div class='card-top'>";
        echo "<h3>{$product_name}</h3>";
        echo "<span class='status {$state}'>{$status_label}</span>";
        echo "</div>";
        echo "<p><i class='fas fa-user-tie'></i> Farmer: <strong>{$row['farmer_name']}</strong> &nbsp;| <i class='fas fa-phone-alt'></i> {$row['farmer_phone']}</p>";
        echo "<p><i class='fas fa-box'></i> {$row['quantity']} {$row['unit']} &nbsp;|&nbsp; <i class='fas fa-money-bill-wave'></i> GHS " . number_format($row['total'], 2) . "</p>";

        if (!empty($row['verification_code'])) {
            echo "<div class='code-display'>";
            echo "<div class='hint'><i class='fas fa-lock'></i> Verification Code</div>";
            echo "<div class='code'>{$row['verification_code']}</div>";
            echo "</div>";

            echo "<div class='checks'>";
            echo "<span class='" . ($row['buyer_confirmed'] ? 'done' : 'waiting') . "'>" . ($row['buyer_confirmed'] ? "<i class='fas fa-check-circle'></i>" : "<i class='far fa-circle'></i>") . " You Confirmed</span>";
            echo "<span class='" . ($row['seller_confirmed'] ? 'done' : 'waiting') . "'>" . ($row['seller_confirmed'] ? "<i class='fas fa-check-circle'></i>" : "<i class='far fa-circle'></i>") . " Farmer Confirmed</span>";
            echo "</div>";

            if (!$both && $row['status'] !== 'Completed') {
                if (!$row['buyer_confirmed']) {
                    echo "<div style='background:#f1f8e9; border-radius:10px; padding:15px; margin-top:12px;'>";
                    echo "<p style='font-size:13px; font-weight:600; color:#2e7d32; margin:0 0 8px;'><i class='fas fa-lock'></i> Verify This Order</p>";
                    echo "<form method='POST' action='verify_transaction.php' style='display:flex; gap:8px;'>";
                    echo "<input type='hidden' name='order_id' value='{$row['id']}'>";
                    echo "<input type='text' name='verification_code' placeholder='Enter code' required maxlength='10' style='flex:1; padding:10px 14px; border:1px solid #c8e6c9; border-radius:8px; font-size:14px; font-weight:600; letter-spacing:1px;'>";
                    echo "<button type='submit' name='verify' style='background:linear-gradient(135deg,#2e7d32,#43a047); color:white; border:none; padding:10px 18px; border-radius:8px; font-weight:700; cursor:pointer; white-space:nowrap;'><i class='fas fa-check'></i> Confirm</button>";
                    echo "</form>";
                    echo "</div>";
                } else {
                    echo "<div style='text-align:center; padding:10px; margin-top:10px; background:#e3f2fd; border-radius:8px; color:#1565c0; font-weight:600; font-size:13px;'><i class='fas fa-check-circle'></i> You confirmed — waiting for farmer</div>";
                }
                echo "<a href='chat.php?user_id={$row['farmer_id']}' style='display:block; text-align:center; background:linear-gradient(135deg,#1565c0,#1976d2); color:white; padding:10px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; margin-top:8px;'><i class='fas fa-comment-dots'></i> Chat with Farmer</a>";
            }
        }

        if ($row['created_at']) {
            echo "<p style='font-size: 12px; color: #aaa; margin-top: 8px;'><i class='far fa-calendar-alt'></i> {$row['created_at']}</p>";
        }
        echo "</div>";
    }
} else {
    echo "<p style='text-align:center; color:#999; margin-top:40px;'>No orders found. <a href='browse_products.php'>Browse products</a></p>";
}
?>

</div>

</body>
</html>