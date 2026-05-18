<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// -----------------------------------------------------
// GET DASHBOARD STATISTICS
// -----------------------------------------------------
$stats = [
    'farmers' => 0,
    'buyers' => 0,
    'pending_loans' => 0,
    'pending_orders' => 0,
    'revenue' => 0
];

// Farmers & Buyers count
$u_q = $conn->query("SELECT role, COUNT(*) as c FROM users GROUP BY role");
if ($u_q) {
    while ($r = $u_q->fetch_assoc()) {
        if ($r['role'] == 'farmer') $stats['farmers'] = $r['c'];
        if ($r['role'] == 'buyer') $stats['buyers'] = $r['c'];
    }
}

// Pending Loans
$l_q = $conn->query("SELECT COUNT(*) as c FROM loans WHERE status='Pending'");
if ($l_q && $lr = $l_q->fetch_assoc()) $stats['pending_loans'] = $lr['c'];

// Pending Orders
$o_q = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Pending'");
if ($o_q && $or = $o_q->fetch_assoc()) $stats['pending_orders'] = $or['c'];

// Total Revenue from Completed Transactions
$t_q = $conn->query("SELECT SUM(total) as sum FROM orders WHERE status='Completed'");
if ($t_q && $tr = $t_q->fetch_assoc()) $stats['revenue'] = $tr['sum'] ?: 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard | AGRIC DSS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f6; color: #333; margin: 0; padding-bottom: 50px; }

/* LAYOUT */
.admin-container {
    max-width: 1300px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.page-header h2 { font-weight: 800; color: #1b5e20; margin: 0; }
.page-header p { color: #666; margin: 5px 0 0; }

/* STAT CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
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
.stat-icon.blue { background: #e3f2fd; color: #1565c0; }
.stat-icon.green { background: #e8f5e9; color: #2e7d32; }
.stat-icon.orange { background: #fff3e0; color: #ef6c00; }
.stat-icon.purple { background: #f3e5f5; color: #7b1fa2; }
.stat-icon.gold { background: #fff8e1; color: #f57f17; }

.stat-info h4 { margin: 0; font-size: 26px; font-weight: 800; color: #333; }
.stat-info p { margin: 0; font-size: 13px; color: #777; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

/* CONTENT TABS */
.admin-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    background: white;
    padding: 10px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    overflow-x: auto;
}

.tab-btn {
    padding: 12px 25px;
    border: none;
    background: transparent;
    font-weight: 600;
    font-size: 14.5px;
    color: #666;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
    white-space: nowrap;
}
.tab-btn:hover { background: #f5f5f5; color: #333; }
.tab-btn.active { background: #1b5e20; color: white; }

.tab-content { display: none; }
.tab-content.active { display: block; animation: fadeIn 0.3s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* CARDS & TABLES */
.panel-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #eee;
}

.table { margin-bottom: 0; }
.table th { background: #f8f9fa; color: #555; font-weight: 600; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #eee; }
.table td { vertical-align: middle; font-size: 14.5px; color: #444; border-bottom: 1px solid #f5f5f5; padding: 15px 10px; }

.badge-status {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.badge-farmer { background: #e8f5e9; color: #2e7d32; }
.badge-buyer { background: #e3f2fd; color: #1565c0; }
.badge-pending { background: #fff3e0; color: #ef6c00; }
.badge-approved { background: #e8f5e9; color: #2e7d32; }
.badge-rejected { background: #ffebee; color: #c62828; }

.action-btn {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: 0.2s;
    margin: 2px;
    color: white;
    border: none;
    cursor: pointer;
}
.btn-del { background: #ef4444; } .btn-del:hover { background: #dc2626; color:white; }
.btn-lock { background: #f59e0b; } .btn-lock:hover { background: #d97706; color:white; }
.btn-unlock { background: #10b981; } .btn-unlock:hover { background: #059669; color:white; }
.btn-appr { background: #2e7d32; } .btn-appr:hover { background: #1b5e20; color:white; }
.btn-view { background: #3b82f6; } .btn-view:hover { background: #2563eb; color:white; }

/* QUICK LINKS */
.quick-links { display: flex; gap: 15px; margin-top: 30px; }
.quick-link-card {
    flex: 1; background: linear-gradient(135deg, #1b5e20, #2e7d32);
    color: white; padding: 20px; border-radius: 12px;
    text-decoration: none; display: flex; align-items: center;
    transition: 0.3s;
}
.quick-link-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(46,125,50,0.3); color: white; }
.quick-link-card i { font-size: 24px; margin-right: 15px; background: rgba(255,255,255,0.2); padding: 12px; border-radius: 10px; }
.quick-link-card h5 { margin: 0 0 3px; font-weight: 700; }
.quick-link-card p { margin: 0; font-size: 12px; opacity: 0.8; }
.ql-buyer { background: linear-gradient(135deg, #1565c0, #1976d2); }
.ql-buyer:hover { box-shadow: 0 8px 20px rgba(21,101,192,0.3); }

/* Search Box */
.search-wrapper { position: relative; margin-bottom: 20px; }
.search-wrapper i { position: absolute; left: 15px; top: 14px; color: #999; }
.search-wrapper input { width: 100%; max-width: 400px; padding: 12px 15px 12px 40px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; outline: none; font-size: 14px; transition: 0.2s; }
.search-wrapper input:focus { border-color: #2e7d32; background: white; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="admin-container">

    <div class="page-header">
        <div>
            <h2>Admin Control Panel</h2>
            <p>System overview and management dashboard</p>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-tractor"></i></div>
            <div class="stat-info">
                <h4><?= $stats['farmers'] ?></h4>
                <p>Registered Farmers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-shopping-basket"></i></div>
            <div class="stat-info">
                <h4><?= $stats['buyers'] ?></h4>
                <p>Registered Buyers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-box-open"></i></div>
            <div class="stat-info">
                <h4><?= $stats['pending_orders'] ?></h4>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-hand-holding-dollar"></i></div>
            <div class="stat-info">
                <h4><?= $stats['pending_loans'] ?></h4>
                <p>Pending Loans</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <h4>₵<?= number_format($stats['revenue'], 2) ?></h4>
                <p>Completed Volume</p>
            </div>
        </div>
    </div>

    <!-- TABS NAVIGATION -->
    <div class="admin-tabs">
        <button class="tab-btn active" onclick="switchTab('users')"><i class="fas fa-users"></i> User Management</button>
        <button class="tab-btn" onclick="switchTab('subs')"><i class="fas fa-crown"></i> Subscriptions</button>
        <button class="tab-btn" onclick="switchTab('promos')"><i class="fas fa-bullhorn"></i> Promotions</button>
        <button class="tab-btn" onclick="switchTab('orders')"><i class="fas fa-shipping-fast"></i> Order Tracking</button>
        <button class="tab-btn" onclick="switchTab('loans')"><i class="fas fa-money-check-alt"></i> Loan Requests</button>
        <button class="tab-btn" onclick="switchTab('prices')"><i class="fas fa-tags"></i> Price Guidelines</button>
    </div>

    <!-- ================= USERS TAB ================= -->
    <div id="tab-users" class="tab-content active panel-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap;">
            <h3 style="margin:0; font-weight:700; color:#333;">System Users</h3>
            <div class="search-wrapper" style="margin:0;">
                <i class="fas fa-search"></i>
                <input type="text" id="userSearch" placeholder="Search by name or email..." onkeyup="filterTable('userSearch', 'userTable')">
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table" id="userTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = $conn->query("SELECT * FROM users ORDER BY id DESC");
                    if($users && $users->num_rows > 0) {
                        while($u = $users->fetch_assoc()) {
                            $role_class = $u['role'] == 'farmer' ? 'badge-farmer' : 'badge-buyer';
                            $status = isset($u['status']) ? $u['status'] : 'active';
                            $status_color = $status == 'locked' ? 'color:#c62828;' : 'color:#2e7d32;';
                            
                            echo "<tr>";
                            echo "<td><strong>{$u['name']}</strong><br><small style='color:#888;'>Joined: ".date('M d, Y', strtotime($u['created_at']))."</small></td>";
                            echo "<td>{$u['email']}<br><small style='color:#888;'>{$u['phone']}</small></td>";
                            echo "<td><span class='badge-status {$role_class}'>".ucfirst($u['role'])."</span></td>";
                            echo "<td><strong style='{$status_color}'><i class='fas fa-circle' style='font-size:8px;'></i> ".ucfirst($status)."</strong></td>";
                            echo "<td style='text-align:right;'>";
                            if($status == 'locked') {
                                echo "<a href='unlock_user.php?id={$u['id']}' class='action-btn btn-unlock' title='Unlock'><i class='fas fa-unlock'></i></a> ";
                            } else {
                                echo "<a href='lock_user.php?id={$u['id']}' class='action-btn btn-lock' title='Lock Account'><i class='fas fa-lock'></i></a> ";
                            }
                            echo "<a href='delete_user.php?id={$u['id']}' class='action-btn btn-del' title='Delete' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= ORDERS TAB ================= -->
    <div id="tab-orders" class="tab-content panel-card">
        <h3 style="margin:0 0 10px; font-weight:700; color:#333;">Order Tracking</h3>
        <p style="color:#666; margin-bottom:20px; font-size:13px;"><i class="fas fa-info-circle"></i> Transactions and delivery are handled securely between the buyer and farmer off-platform.</p>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product & Qty</th>
                        <th>Buyer</th>
                        <th>Total Amount</th>
                        <th>Verification Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = $conn->query("
                        SELECT o.*, u.name AS buyer_name, p.type AS product_name
                        FROM orders o
                        LEFT JOIN users u ON o.buyer_id = u.id
                        LEFT JOIN products p ON o.product_id = p.id
                        ORDER BY o.id DESC
                    ");
                    if($orders && $orders->num_rows > 0) {
                        while($o = $orders->fetch_assoc()) {
                            $sc = $o['status'] == 'Pending' ? 'badge-pending' : ($o['status'] == 'Completed' ? 'badge-approved' : 'badge-buyer');
                            
                            echo "<tr>";
                            echo "<td>#{$o['id']}</td>";
                            echo "<td><strong>{$o['product_name']}</strong><br><small style='color:#888;'>Qty: {$o['quantity']}</small></td>";
                            echo "<td>{$o['buyer_name']}</td>";
                            echo "<td><strong>₵{$o['total']}</strong></td>";
                            echo "<td><span class='badge-status {$sc}'>{$o['status']}</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= SUBSCRIPTIONS TAB ================= -->
    <div id="tab-subs" class="tab-content panel-card">
        <h3 style="margin:0 0 20px; font-weight:700; color:#333;">Subscription Payments</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Farmer</th>
                        <th>Amount Paid</th>
                        <th>Transaction ID / Ref</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subs = $conn->query("
                        SELECT s.*, u.name, u.phone
                        FROM subscriptions s
                        JOIN users u ON s.user_id = u.id
                        ORDER BY s.id DESC
                    ");
                    if($subs && $subs->num_rows > 0) {
                        while($s = $subs->fetch_assoc()) {
                            $sc = $s['status'] == 'pending' ? 'badge-pending' : ($s['status'] == 'approved' ? 'badge-approved' : 'badge-rejected');
                            echo "<tr>";
                            echo "<td><strong>{$s['name']}</strong><br><small style='color:#888;'>{$s['phone']}</small></td>";
                            echo "<td><strong>₵{$s['amount']}</strong></td>";
                            echo "<td>{$s['payment_ref']}</td>";
                            echo "<td>".date('M d, Y', strtotime($s['created_at']))."</td>";
                            echo "<td><span class='badge-status {$sc}'>".ucfirst($s['status'])."</span></td>";
                            echo "<td style='text-align:right;'>";
                            if($s['status'] == 'pending') {
                                echo "<a href='approve_subscription.php?id={$s['id']}' class='action-btn btn-appr'><i class='fas fa-check'></i> Approve</a> ";
                                echo "<a href='reject_subscription.php?id={$s['id']}' class='action-btn btn-del'><i class='fas fa-times'></i> Reject</a>";
                            } else {
                                echo "<span style='color:#aaa; font-size:13px;'><i class='fas fa-check-double'></i> Processed</span>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No subscription payments found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= PROMOTIONS TAB ================= -->
    <div id="tab-promos" class="tab-content panel-card">
        <h3 style="margin:0 0 20px; font-weight:700; color:#333;">Product Promotion Payments</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Farmer & Product</th>
                        <th>Amount Paid</th>
                        <th>Transaction ID / Ref</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $promos = $conn->query("
                        SELECT pr.*, u.name, p.type AS product_name
                        FROM promotions pr
                        JOIN users u ON pr.user_id = u.id
                        JOIN products p ON pr.product_id = p.id
                        ORDER BY pr.id DESC
                    ");
                    if($promos && $promos->num_rows > 0) {
                        while($pr = $promos->fetch_assoc()) {
                            $sc = $pr['status'] == 'pending' ? 'badge-pending' : ($pr['status'] == 'approved' ? 'badge-approved' : 'badge-rejected');
                            echo "<tr>";
                            echo "<td><strong>{$pr['name']}</strong><br><small style='color:#888;'>{$pr['product_name']}</small></td>";
                            echo "<td><strong>₵{$pr['amount']}</strong></td>";
                            echo "<td>{$pr['payment_ref']}</td>";
                            echo "<td>".date('M d, Y', strtotime($pr['created_at']))."</td>";
                            echo "<td><span class='badge-status {$sc}'>".ucfirst($pr['status'])."</span></td>";
                            echo "<td style='text-align:right;'>";
                            if($pr['status'] == 'pending') {
                                echo "<a href='approve_promotion.php?id={$pr['id']}' class='action-btn btn-appr'><i class='fas fa-check'></i> Approve</a> ";
                                echo "<a href='reject_promotion.php?id={$pr['id']}' class='action-btn btn-del'><i class='fas fa-times'></i> Reject</a>";
                            } else {
                                echo "<span style='color:#aaa; font-size:13px;'><i class='fas fa-check-double'></i> Processed</span>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No promotion payments found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= LOANS TAB ================= -->
    <div id="tab-loans" class="tab-content panel-card">
        <h3 style="margin:0 0 20px; font-weight:700; color:#333;">Loan Requests</h3>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'loan_approved'): ?>
            <div class="alert alert-success d-flex align-items-center" style="border-radius:10px; font-weight:600;">
                <i class="fas fa-check-circle" style="font-size:20px; margin-right:10px;"></i> Loan successfully approved and funds marked for dispatch!
            </div>
        <?php endif; ?>
        
        <?php
        // Fetch Loan Metrics
        $total_approved = $conn->query("SELECT SUM(amount) AS total FROM loans WHERE status='Approved'")->fetch_assoc()['total'] ?? 0;
        $total_pending = $conn->query("SELECT SUM(amount) AS total FROM loans WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
        $total_rejected = $conn->query("SELECT SUM(amount) AS total FROM loans WHERE status='Rejected'")->fetch_assoc()['total'] ?? 0;
        ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #1b5e20, #2e7d32); color:white; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(46,125,50,0.2);">
                    <h5 style="margin:0; opacity:0.9; font-size:14px;"><i class="fas fa-money-bill-wave"></i> Total Approved</h5>
                    <h2 style="margin:5px 0 0; font-weight:800; font-size:24px;">GHS <?= number_format($total_approved, 2) ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #f57c00, #ff9800); color:white; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(245,124,0,0.2);">
                    <h5 style="margin:0; opacity:0.9; font-size:14px;"><i class="fas fa-hourglass-half"></i> Total Pending</h5>
                    <h2 style="margin:5px 0 0; font-weight:800; font-size:24px;">GHS <?= number_format($total_pending, 2) ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #d32f2f, #ef5350); color:white; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(211,47,47,0.2);">
                    <h5 style="margin:0; opacity:0.9; font-size:14px;"><i class="fas fa-times-circle"></i> Total Rejected</h5>
                    <h2 style="margin:5px 0 0; font-weight:800; font-size:24px;">GHS <?= number_format($total_rejected, 2) ?></h2>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Applicant</th>
                        <th>Amount Requested</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $loans = $conn->query("
                        SELECT loans.*, users.name 
                        FROM loans 
                        JOIN users ON loans.user_id = users.id
                        ORDER BY loans.id DESC
                    ");
                    if($loans && $loans->num_rows > 0) {
                        while($l = $loans->fetch_assoc()) {
                            $sc = $l['status'] == 'Pending' ? 'badge-pending' : ($l['status'] == 'Approved' ? 'badge-approved' : 'badge-rejected');
                            $formatted_id = "#LN-" . str_pad($l['id'], 4, '0', STR_PAD_LEFT);
                            echo "<tr>";
                            echo "<td style='font-family:monospace; font-weight:bold; color:#666;'>{$formatted_id}</td>";
                            echo "<td><strong>{$l['name']}</strong></td>";
                            echo "<td><strong>₵{$l['amount']}</strong></td>";
                            echo "<td>{$l['purpose']}</td>";
                            echo "<td><span class='badge-status {$sc}'>{$l['status']}</span></td>";
                            echo "<td style='text-align:right;'>";
                            if($l['status'] == 'Pending') {
                                echo "<a href='approve_loan.php?id={$l['id']}' class='action-btn btn-view'><i class='fas fa-eye'></i> Review & Process</a> ";
                                echo "<a href='reject_loan.php?id={$l['id']}' class='action-btn btn-del'><i class='fas fa-times'></i> Reject</a>";
                            } else {
                                echo "<a href='approve_loan.php?id={$l['id']}' class='action-btn btn-view' style='background:#6c757d;'><i class='fas fa-file-alt'></i> View Details</a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No loan requests found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= PRICES TAB ================= -->
    <div id="tab-prices" class="tab-content panel-card">
        <h3 style="margin:0 0 10px; font-weight:700; color:#333;">Market Price Guidelines</h3>
        <p style="color:#666; margin-bottom:20px;">Manage recommended market prices visible to farmers and buyers.</p>
        
        <a href="manage_prices.php" class="btn-register" style="display:inline-block; text-decoration:none; width:auto; padding:12px 25px;">
            <i class="fas fa-edit"></i> Open Price Manager
        </a>
    </div>

    <!-- QUICK PREVIEW LINKS -->
    <div class="quick-links">
        <a href="view_farmer_dashboard.php" target="_blank" class="quick-link-card">
            <i class="fas fa-tractor"></i>
            <div>
                <h5>Farmer View</h5>
                <p>Preview the dashboard exactly as farmers see it</p>
            </div>
        </a>
        <a href="view_buyer_dashboard.php" target="_blank" class="quick-link-card ql-buyer">
            <i class="fas fa-store"></i>
            <div>
                <h5>Buyer View</h5>
                <p>Preview the marketplace exactly as buyers see it</p>
            </div>
        </a>
    </div>

</div>

<script>
// Tab Switching Logic
function switchTab(tabId, btn = null) {
    // Remove active class from all tabs & contents
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked tab
    if (btn) {
        btn.classList.add('active');
    } else {
        // Find the button with onclick matching the tabId
        let foundBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick').includes(tabId));
        if (foundBtn) foundBtn.classList.add('active');
    }
    document.getElementById('tab-' + tabId).classList.add('active');
}

// Auto-open tab from URL parameter
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        switchTab(tab);
    }
});

// Table filtering logic
function filterTable(inputId, tableId) {
    let input = document.getElementById(inputId);
    let filter = input.value.toLowerCase();
    let table = document.getElementById(tableId);
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) { // Skip header row
        let td = tr[i].getElementsByTagName("td")[0]; // Filter by first column (Name)
        let td2 = tr[i].getElementsByTagName("td")[1]; // Filter by second column (Email)
        if (td || td2) {
            let textValue = (td.textContent || td.innerText) + " " + (td2.textContent || td2.innerText);
            if (textValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

</body>
</html>