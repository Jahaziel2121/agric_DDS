<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'Buyer';

// Fetch User Profile Data
$user_query = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = ($user_query && $user_query->num_rows > 0) ? $user_query->fetch_assoc() : [];

// -----------------------------------------------------
// GET BUYER DASHBOARD STATISTICS
// -----------------------------------------------------
$pending_orders = 0;
$completed_orders = 0;
$total_spent = 0;
$unique_sellers = 0;

// 1. Pending Orders
$pending_q = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE buyer_id='$user_id' AND status='Pending'");
if($pending_q && $pending_q->num_rows > 0){
    $pending_orders = $pending_q->fetch_assoc()['total'];
}

// 2. Completed Purchases
$completed_q = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE buyer_id='$user_id' AND status='Completed'");
if($completed_q && $completed_q->num_rows > 0){
    $completed_orders = $completed_q->fetch_assoc()['total'];
}

// 3. Total Spent (from Completed Orders)
$spent_q = $conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE buyer_id='$user_id' AND status='Completed'");
if($spent_q && $spent_q->num_rows > 0){
    $total_spent = $spent_q->fetch_assoc()['total'];
}

// 4. Unique Farmers interacted with
$farmers_q = $conn->query("SELECT COUNT(DISTINCT seller_id) AS total FROM orders WHERE buyer_id='$user_id'");
if($farmers_q && $farmers_q->num_rows > 0){
    $unique_sellers = $farmers_q->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Buyer Dashboard | AGRIC DSS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f6; color: #333; margin: 0; padding-bottom: 50px; }

.dashboard-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.welcome-header {
    margin-bottom: 30px;
}
.welcome-header h2 { font-weight: 800; color: #1565c0; margin: 0; }
.welcome-header p { color: #666; font-size: 15px; margin: 5px 0 0; }

/* QUICK PROFILE STRIP */
.profile-strip {
    background: linear-gradient(to right, #1565c0, #1976d2);
    border-radius: 12px;
    padding: 20px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}
.profile-info { display: flex; align-items: center; gap: 15px; }
.profile-avatar { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.profile-text h4 { margin: 0 0 3px; font-weight: 700; font-size: 18px; }
.profile-text p { margin: 0; font-size: 13px; opacity: 0.85; }

/* STATS GRID */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #eee;
    transition: 0.3s;
}
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 15px;
}
.stat-icon.orange { background: #fff3e0; color: #ef6c00; }
.stat-icon.green { background: #e8f5e9; color: #2e7d32; }
.stat-icon.blue { background: #e3f2fd; color: #1565c0; }
.stat-icon.purple { background: #f3e5f5; color: #7b1fa2; }

.stat-info h4 { margin: 0; font-size: 26px; font-weight: 800; color: #333; }
.stat-info p { margin: 0; font-size: 13px; color: #777; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

/* ACTION CARDS */
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.action-card {
    background: white;
    border-radius: 16px;
    padding: 35px 25px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #eee;
    transition: 0.3s;
    position: relative;
    overflow: hidden;
}
.action-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }

.action-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin: 0 auto 20px;
    color: white;
}
.action-icon.market { background: linear-gradient(135deg, #2e7d32, #4caf50); }
.action-icon.orders { background: linear-gradient(135deg, #f57f17, #ffb300); }
.action-icon.request { background: linear-gradient(135deg, #1565c0, #42a5f5); }

.action-card h3 { font-weight: 700; color: #333; margin-bottom: 15px; font-size: 20px; }
.action-card p { color: #666; font-size: 14.5px; line-height: 1.5; margin-bottom: 25px; }

.btn-action {
    display: inline-block;
    padding: 12px 30px;
    border-radius: 30px;
    font-weight: 700;
    text-decoration: none;
    transition: 0.3s;
    width: 100%;
}
.btn-action.market { background: #e8f5e9; color: #2e7d32; }
.btn-action.market:hover { background: #2e7d32; color: white; }

.btn-action.orders { background: #fff8e1; color: #f57f17; }
.btn-action.orders:hover { background: #f57f17; color: white; }

.btn-action.request { background: #e3f2fd; color: #1565c0; }
.btn-action.request:hover { background: #1565c0; color: white; }
</style>

</head>
<body>

<?php include 'navbar.php'; ?>

<div class="dashboard-container">

    <div class="welcome-header">
        <h2>Welcome back, <?= htmlspecialchars($user_name) ?>! 👋</h2>
        <p>Ready to source the best agricultural products today?</p>
    </div>

    <!-- QUICK PROFILE STRIP -->
    <div class="profile-strip">
        <div class="profile-info">
            <div class="profile-avatar"><i class="fas fa-shopping-basket"></i></div>
            <div class="profile-text">
                <h4><?= htmlspecialchars($user['name'] ?? 'Buyer') ?></h4>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email'] ?? 'No email') ?> &nbsp; | &nbsp; <i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone'] ?? 'No phone') ?></p>
            </div>
        </div>
        <a href="profile.php" class="btn btn-light btn-sm" style="border-radius:20px; font-weight:600; color:#1565c0;"><i class="fas fa-edit"></i> Edit Profile</a>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h4><?= $pending_orders ?></h4>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <h4><?= $completed_orders ?></h4>
                <p>Completed Purchases</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h4><?= $unique_sellers ?></h4>
                <p>Connected Farmers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-wallet"></i></div>
            <div class="stat-info">
                <h4>₵<?= number_format($total_spent, 2) ?></h4>
                <p>Total Spent</p>
            </div>
        </div>
    </div>

    <!-- ACTION CARDS -->
    <h3 style="font-weight:800; color:#1565c0; margin-bottom:20px;">Marketplace Actions</h3>
    
    <div class="action-grid">
        <div class="action-card">
            <div class="action-icon market"><i class="fas fa-wheat-awn"></i></div>
            <h3>Browse Farm Produce</h3>
            <p>Explore the marketplace to find high-quality crops, livestock, and agricultural services directly from verified farmers.</p>
            <a href="browse_products.php" class="btn-action market">Enter Marketplace</a>
        </div>

        <div class="action-card">
            <div class="action-icon orders"><i class="fas fa-box-open"></i></div>
            <h3>My Orders</h3>
            <p>Track your ongoing purchases, communicate with sellers, and confirm deliveries to complete your transactions.</p>
            <a href="my_orders.php" class="btn-action orders">Track Orders</a>
        </div>

        <div class="action-card">
            <div class="action-icon request"><i class="fas fa-bullhorn"></i></div>
            <h3>Post Buy Request</h3>
            <p>Tell farmers exactly what you need! Specify the product, quantity range, and budget so they can match their harvest to you.</p>
            <a href="buyer_request_form.php" class="btn-action request">Post a Request</a>
        </div>
    </div>

</div>

</body>
</html>