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

// Check current subscription status
$sub_check = $conn->query("SELECT is_subscribed FROM users WHERE id='$user_id'");
$user_data = $sub_check->fetch_assoc();
$is_subscribed = $user_data['is_subscribed'] ?? 0;

// Process payment
if (isset($_POST['subscribe'])) {
    $payment_method = $_POST['payment_method'];
    $payment_ref = $_POST['payment_ref'];
    $phone = $_POST['phone'];
    $amount = 50.00;

    if (empty($payment_ref)) {
        $message = "<i class='fas fa-times-circle'></i> Please enter a valid payment reference/transaction ID.";
        $msg_type = "error";
    } else {
        // Record the subscription (status is 'pending' by default in schema)
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, amount, payment_method, payment_ref, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idss", $user_id, $amount, $payment_method, $payment_ref);
        $stmt->execute();

        // Notify Admin (user_id = 0)
        $farmer_name = $_SESSION['name'] ?? 'Farmer';
        $sub_id = $stmt->insert_id; // Get the newly created subscription ID
        
        $admin_msg = "<div style='margin-bottom:10px;'><a href='admin_dashboard.php?tab=subs' style='text-decoration:none; color:inherit;'><i class=\"fas fa-crown\"></i> Subscription payment received from <b>$farmer_name</b> (Phone: $phone). Ref: <b>$payment_ref</b>.</a></div>";
        $admin_msg .= "<div><a href=\"approve_subscription.php?id=$sub_id\" class=\"btn btn-sm btn-success\" style=\"border-radius:20px; font-weight:600; font-size:12px; padding:4px 12px; margin-right:5px; text-decoration:none;\"><i class=\"fas fa-check\"></i> Approve</a> ";
        $admin_msg .= "<a href=\"reject_subscription.php?id=$sub_id\" class=\"btn btn-sm btn-danger\" style=\"border-radius:20px; font-weight:600; font-size:12px; padding:4px 12px; text-decoration:none;\"><i class=\"fas fa-times\"></i> Reject</a></div>";
        
        $n_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (0, ?)");
        $n_stmt->bind_param("s", $admin_msg);
        $n_stmt->execute();

        $message = "<i class='fas fa-info-circle'></i> Payment details submitted successfully! The admin is verifying your transaction ID. You will be notified once approved.";
        $msg_type = "success";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmer Subscription</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); min-height: 100vh; }

.pricing-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 550px;
    margin: 30px auto;
    position: relative;
    overflow: hidden;
}

.pricing-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 6px;
    background: linear-gradient(90deg, #2e7d32, #66bb6a, #43a047);
}

.price-tag {
    font-size: 48px;
    font-weight: 700;
    color: #1b5e20;
    line-height: 1;
}

.price-tag small {
    font-size: 16px;
    color: #666;
    font-weight: 400;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 25px 0;
}

.feature-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 15px;
    color: #444;
}

.feature-list li::before {
    content: '<i class='fas fa-check-circle'></i> ';
}

.payment-form {
    background: #f8fdf8;
    border-radius: 12px;
    padding: 25px;
    margin-top: 20px;
    border: 1px solid #e0e0e0;
}

.payment-form label {
    font-weight: 600;
    font-size: 13px;
    color: #333;
    margin-bottom: 4px;
}

.payment-form .form-control, .payment-form .form-select {
    border-radius: 10px;
    border: 1px solid #ccc;
    padding: 10px 14px;
}

.btn-pay {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-pay:hover {
    background: linear-gradient(135deg, #1b5e20, #2e7d32);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(46,125,50,0.3);
    color: white;
}

.badge-active {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
}

.active-card {
    text-align: center;
    padding: 50px 30px;
}

.active-card .checkmark {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    font-size: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.momo-info {
    background: #fff3e0;
    border: 1px solid #ffe0b2;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13px;
    color: #e65100;
    margin-bottom: 15px;
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

<div class="pricing-card">

<?php if ($message): ?>
    <div class="alert-msg <?= $msg_type ?>"><?= $message ?></div>
<?php endif; ?>

<?php if ($is_subscribed): ?>
    <!-- ALREADY SUBSCRIBED -->
    <div class="active-card">
        <div class="checkmark">✓</div>
        <h3 style="color: #1b5e20;">Account Activated</h3>
        <p style="color: #666;">You have full access to all platform features.</p>
        <span class="badge-active">🟢 Active Subscription</span>

        <div style="margin-top: 30px; text-align: left;">
            <h5>Your Benefits:</h5>
            <ul class="feature-list">
                <li>Sell farm products to verified buyers</li>
                <li>Access farm support services</li>
                <li>Apply for agricultural loans</li>
                <li>View real-time market prices</li>
                <li>Get buyer reputation insights</li>
                <li>Promote products for top visibility</li>
            </ul>
        </div>

        <a href="dashboard.php" class="btn btn-pay mt-3">Go to Dashboard</a>
    </div>

<?php else: ?>
    <!-- SUBSCRIPTION FORM -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h3 style="color: #1b5e20; margin-bottom: 5px;"><i class='fas fa-wheat-awn'></i> Farmer Subscription</h3>
        <p style="color: #888; font-size: 14px;">One-time payment to unlock all platform features</p>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <div class="price-tag">GHS 50 <small>one-time</small></div>
    </div>

    <ul class="feature-list">
        <li>Sell farm products to verified buyers</li>
        <li>Access farm support services & tools</li>
        <li>Apply for agricultural loans</li>
        <li>View real-time market prices</li>
        <li>Get buyer reputation & trust insights</li>
        <li>Promote products for top visibility</li>
        <li>Receive notifications & order alerts</li>
    </ul>

    <div class="payment-form">
        <h5 style="margin-bottom: 15px;"><i class='fas fa-credit-card'></i> Make Payment</h5>

        <div class="momo-info">
            <i class='fas fa-mobile-alt'></i> Send <b>GHS 50.00</b> to <b>0543809952</b> (AGRIC DSS) via Mobile Money, then enter your transaction details below.
        </div>

        <form method="POST">
            <div class="mb-3">
                <label>Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <option value="MTN Mobile Money">MTN Mobile Money</option>
                    <option value="Vodafone Cash">Vodafone Cash</option>
                    <option value="AirtelTigo Money">AirtelTigo Money</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Your Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. 0543809952" required>
            </div>

            <div class="mb-3">
                <label>Transaction Reference / ID</label>
                <input type="text" name="payment_ref" class="form-control" placeholder="e.g. TXN12345678" required>
            </div>

            <button type="submit" name="subscribe" class="btn btn-pay">
                <i class='fas fa-money-bill-wave'></i> Pay GHS 50.00 & Activate Account
            </button>
        </form>
    </div>

<?php endif; ?>

</div>

</body>
</html>
