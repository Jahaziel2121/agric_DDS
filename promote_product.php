<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// Process promotion payment
if (isset($_POST['promote'])) {
    $product_id = $_POST['product_id'];
    $payment_method = $_POST['payment_method'];
    $payment_ref = $_POST['payment_ref'];
    $amount = 20.00;

    if (empty($payment_ref)) {
        $message = "<i class='fas fa-times-circle'></i> Please enter a valid transaction reference.";
        $msg_type = "error";
    } else {
        // Record the promotion (status is 'pending' by default in schema)
        $stmt = $conn->prepare("INSERT INTO promotions (product_id, user_id, amount, payment_method, payment_ref, expires_at, status) VALUES (?, ?, ?, ?, ?, datetime('now', '+30 days'), 'pending')");
        $stmt->bind_param("iidss", $product_id, $user_id, $amount, $payment_method, $payment_ref);
        $stmt->execute();

        // Notify Admin
        $farmer_name = $_SESSION['name'] ?? 'Farmer';
        $promo_id = $stmt->insert_id; // Get the newly created promotion ID
        
        $admin_msg = "<div style='margin-bottom:10px;'><a href='admin_dashboard.php?tab=promos' style='text-decoration:none; color:inherit;'><i class=\"fas fa-bullhorn\"></i> Promotion payment received from <b>$farmer_name</b>. Ref: <b>$payment_ref</b>.</a></div>";
        $admin_msg .= "<div><a href=\"approve_promotion.php?id=$promo_id\" class=\"btn btn-sm btn-success\" style=\"border-radius:20px; font-weight:600; font-size:12px; padding:4px 12px; margin-right:5px; text-decoration:none;\"><i class=\"fas fa-check\"></i> Approve</a> ";
        $admin_msg .= "<a href=\"reject_promotion.php?id=$promo_id\" class=\"btn btn-sm btn-danger\" style=\"border-radius:20px; font-weight:600; font-size:12px; padding:4px 12px; text-decoration:none;\"><i class=\"fas fa-times\"></i> Reject</a></div>";
        
        $n_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (0, ?)");
        $n_stmt->bind_param("s", $admin_msg);
        $n_stmt->execute();

        $message = "<i class='fas fa-info-circle'></i> Payment details submitted successfully! The admin is verifying your transaction ID. You will be notified once approved.";
        $msg_type = "success";
    }
}

// Get farmer's products
$products = $conn->query("SELECT * FROM products WHERE user_id='$user_id' ORDER BY is_promoted DESC, id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Promote Products</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: linear-gradient(135deg, #fff8e1, #fff3e0); min-height: 100vh; }

.promo-header {
    background: linear-gradient(135deg, #ff8f00, #ffa726);
    color: white;
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 25px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(255,143,0,0.3);
}

.product-card {
    background: white;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border-left: 4px solid #e0e0e0;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.product-card.promoted {
    border-left: 4px solid #ff8f00;
    background: linear-gradient(to right, #fffde7, white);
}

.promoted-badge {
    background: linear-gradient(135deg, #ff8f00, #ffa726);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.btn-promote {
    background: linear-gradient(135deg, #ff8f00, #ffa726);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-promote:hover {
    background: linear-gradient(135deg, #e65100, #ff8f00);
    color: white;
    transform: translateY(-1px);
}

.promo-form {
    background: #fffde7;
    border: 1px solid #ffe082;
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
}

.price-box {
    background: white;
    border: 2px solid #ff8f00;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    margin-bottom: 20px;
}

.price-box .amount {
    font-size: 32px;
    font-weight: 700;
    color: #e65100;
}

.alert-msg {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 15px;
    font-weight: 500;
}
.alert-msg.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.alert-msg.error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container" style="max-width: 700px;">

<div class="promo-header">
    <h3><i class='fas fa-star'></i> Promote Your Products</h3>
    <p style="margin: 0; opacity: 0.9;">Boost your listing to the top of the marketplace</p>
</div>

<?php if ($message): ?>
    <div class="alert-msg <?= $msg_type ?>"><?= $message ?></div>
<?php endif; ?>

<div class="price-box">
    <p style="margin: 0; color: #888; font-size: 13px;">PROMOTION FEE</p>
    <div class="amount">GHS 20 <small style="font-size: 14px; color: #999;">/product</small></div>
    <p style="margin: 0; color: #666; font-size: 13px;">Your product stays at the top for <b>30 days</b></p>
</div>

<h5 class="mb-3"><i class='fas fa-box'></i> Your Products</h5>

<?php if ($products && $products->num_rows > 0): ?>
    <?php while ($p = $products->fetch_assoc()): ?>
        <div class="product-card <?= $p['is_promoted'] ? 'promoted' : '' ?>">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h5 style="margin: 0;">
                        <?= htmlspecialchars($p['product_name'] ?: $p['type']) ?>
                        <?php if ($p['is_promoted']): ?>
                            <span class="promoted-badge"><i class='fas fa-star'></i> PROMOTED</span>
                        <?php endif; ?>
                    </h5>
                    <p style="margin: 5px 0 0; color: #666; font-size: 14px;">
                        <i class='fas fa-box'></i> <?= $p['quantity'] ?> <?= $p['unit'] ?> &nbsp;|&nbsp;
                        <i class='fas fa-money-bill-wave'></i> GHS <?= number_format($p['price'] ?? 0, 2) ?>
                    </p>
                </div>

                <?php if (!$p['is_promoted']): ?>
                    <button class="btn-promote" onclick="document.getElementById('form-<?= $p['id'] ?>').style.display = document.getElementById('form-<?= $p['id'] ?>').style.display === 'none' ? 'block' : 'none'">
                        <i class='fas fa-star'></i> Promote
                    </button>
                <?php else: ?>
                    <span style="color: #ff8f00; font-weight: 600; font-size: 13px;">📢 Active</span>
                <?php endif; ?>
            </div>

            <?php if (!$p['is_promoted']): ?>
            <div id="form-<?= $p['id'] ?>" class="promo-form" style="display: none;">
                <p style="font-size: 13px; color: #e65100;">
                    <i class='fas fa-mobile-alt'></i> Send <b>GHS 20.00</b> to <b>0543809952</b> (AGRIC DSS), then enter your transaction details:
                </p>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">

                    <select name="payment_method" class="form-select mb-2" required>
                        <option value="MTN Mobile Money">MTN Mobile Money</option>
                        <option value="Vodafone Cash">Vodafone Cash</option>
                        <option value="AirtelTigo Money">AirtelTigo Money</option>
                    </select>

                    <input type="text" name="payment_ref" class="form-control mb-2" placeholder="Transaction Reference / ID" required>

                    <button type="submit" name="promote" class="btn-promote w-100">
                        <i class='fas fa-money-bill-wave'></i> Pay GHS 20 & Promote
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-center" style="color: #999;">You don't have any products to promote yet. <a href="sell.php">Upload a product first</a>.</p>
<?php endif; ?>

</div>

</body>
</html>
