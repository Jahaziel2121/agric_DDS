<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'Farmer';

// Fetch User Profile Data
$user_query = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = ($user_query && $user_query->num_rows > 0) ? $user_query->fetch_assoc() : [];

// -----------------------------------------------------
// GET FARMER DASHBOARD STATISTICS
// -----------------------------------------------------
$total_products = 0;
$pending_orders = 0;
$completed_orders = 0;
$total_earnings = 0;

// 1. Total Products Listed
$product_q = $conn->query("SELECT COUNT(*) AS total FROM products WHERE user_id='$user_id'");
if($product_q && $product_q->num_rows > 0){
    $total_products = $product_q->fetch_assoc()['total'];
}

// 2. Pending Orders
$pending_q = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE seller_id='$user_id' AND status='Pending'");
if($pending_q && $pending_q->num_rows > 0){
    $pending_orders = $pending_q->fetch_assoc()['total'];
}

// 3. Completed Orders
$completed_q = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE seller_id='$user_id' AND status='Completed'");
if($completed_q && $completed_q->num_rows > 0){
    $completed_orders = $completed_q->fetch_assoc()['total'];
}

// 4. Total Earnings (from Completed Orders)
$earnings_q = $conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE seller_id='$user_id' AND status='Completed'");
if($earnings_q && $earnings_q->num_rows > 0){
    $total_earnings = $earnings_q->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmer Dashboard | AGRIC DSS</title>
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
.welcome-header h2 { font-weight: 800; color: #1b5e20; margin: 0; }
.welcome-header p { color: #666; font-size: 15px; margin: 5px 0 0; }

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
.stat-icon.green { background: #e8f5e9; color: #2e7d32; }
.stat-icon.blue { background: #e3f2fd; color: #1565c0; }
.stat-icon.orange { background: #fff3e0; color: #ef6c00; }
.stat-icon.gold { background: #fff8e1; color: #f57f17; }

.stat-info h4 { margin: 0; font-size: 26px; font-weight: 800; color: #333; }
.stat-info p { margin: 0; font-size: 13px; color: #777; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

/* ACTION CARDS */
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
.action-icon.sell { background: linear-gradient(135deg, #2e7d32, #4caf50); }
.action-icon.services { background: linear-gradient(135deg, #f57f17, #ffb300); }
.action-icon.loan { background: linear-gradient(135deg, #1565c0, #42a5f5); }

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
.btn-action.sell { background: #e8f5e9; color: #2e7d32; }
.btn-action.sell:hover { background: #2e7d32; color: white; }

.btn-action.services { background: #fff8e1; color: #f57f17; }
.btn-action.services:hover { background: #f57f17; color: white; }

.btn-action.loan { background: #e3f2fd; color: #1565c0; }
.btn-action.loan:hover { background: #1565c0; color: white; }

/* Quick Profile Strip */
.profile-strip {
    background: linear-gradient(to right, #1b5e20, #2e7d32);
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
.profile-avatar { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.profile-text h4 { margin: 0 0 3px; font-weight: 700; font-size: 18px; }
.profile-text p { margin: 0; font-size: 13px; opacity: 0.85; }
</style>

</head>
<body>

<?php include 'navbar.php'; ?>

<div class="dashboard-container">

    <div class="welcome-header">
        <h2>Welcome back, <?= htmlspecialchars($user_name) ?>! 👋</h2>
        <p>Here is what's happening with your farm today.</p>
    </div>

    <!-- QUICK PROFILE STRIP -->
    <div class="profile-strip">
        <div class="profile-info">
            <div class="profile-avatar"><i class="fas fa-tractor"></i></div>
            <div class="profile-text">
                <h4><?= htmlspecialchars($user['name'] ?? 'Farmer') ?></h4>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($user['location'] ?? 'Location not set') ?> &nbsp; | &nbsp; <i class="fas fa-leaf"></i> <?= htmlspecialchars($user['farm_size'] ?? 'Size not set') ?></p>
            </div>
        </div>
        <a href="profile.php" class="btn btn-light btn-sm" style="border-radius:20px; font-weight:600;"><i class="fas fa-edit"></i> Edit Profile</a>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <h4><?= $total_products ?></h4>
                <p>Active Products</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-shopping-basket"></i></div>
            <div class="stat-info">
                <h4><?= $pending_orders ?></h4>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h4><?= $completed_orders ?></h4>
                <p>Completed Sales</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <h4>₵<?= number_format($total_earnings, 2) ?></h4>
                <p>Total Earnings</p>
            </div>
        </div>
    </div>

    <!-- ACTION CARDS -->
    <h3 style="font-weight:800; color:#1b5e20; margin-bottom:20px;">Quick Actions</h3>
    
    <div class="action-grid">
        <div class="action-card">
            <div class="action-icon sell"><i class="fas fa-wheat-awn"></i></div>
            <h3>Sell Produce</h3>
            <p>Upload your fresh farm products to the marketplace and track real-time market prices.</p>
            <a href="sell.php" class="btn-action sell">Go to Market</a>
        </div>

        <div class="action-card">
            <div class="action-icon services"><i class="fas fa-tractor"></i></div>
            <h3>Farm Services</h3>
            <p>Hire professionals for land preparation, weeding, harvesting, and equipment rentals.</p>
            <a href="buy.php" class="btn-action services">Browse Services</a>
        </div>

        <div class="action-card">
            <div class="action-icon loan"><i class="fas fa-hand-holding-dollar"></i></div>
            <h3>Apply for Loan</h3>
            <p>Need capital? Request financial support or input credits for your next farming season.</p>
            <a href="loan.php" class="btn-action loan">Request Funds</a>
        </div>
    </div>

</div>

</body>
</html>