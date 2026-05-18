<?php
session_start();
include 'db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// Fetch all farmers with their stats
$farmers = $conn->query("
    SELECT u.*, 
        (SELECT COUNT(*) FROM products WHERE user_id=u.id) AS total_products,
        (SELECT COUNT(*) FROM orders WHERE seller_id=u.id) AS total_orders,
        (SELECT COALESCE(SUM(total),0) FROM orders WHERE seller_id=u.id AND status='Completed') AS total_earnings
    FROM users u 
    WHERE u.role='farmer' 
    ORDER BY u.id DESC
");

$total_farmers = 0;
$farmer_list = [];
if ($farmers) {
    while ($f = $farmers->fetch_assoc()) {
        $farmer_list[] = $f;
    }
    $total_farmers = count($farmer_list);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmer Accounts | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f6; margin: 0; padding-bottom: 50px; }

.page-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

.page-header {
    background: linear-gradient(135deg, #1b5e20, #2e7d32, #43a047);
    border-radius: 16px;
    padding: 30px;
    color: white;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}
.page-header h2 { margin: 0; font-weight: 800; font-size: 24px; }
.page-header p { margin: 5px 0 0; opacity: 0.85; font-size: 14px; }
.header-stat { background: rgba(255,255,255,0.15); padding: 10px 20px; border-radius: 12px; text-align: center; }
.header-stat h3 { margin: 0; font-size: 28px; font-weight: 800; }
.header-stat small { opacity: 0.8; font-size: 12px; }

/* Search */
.search-bar {
    background: white;
    border-radius: 12px;
    padding: 12px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    border: 1px solid #eee;
}
.search-bar i { color: #888; }
.search-bar input {
    border: none;
    outline: none;
    flex: 1;
    font-size: 14px;
    color: #333;
}

/* Farmer Cards */
.farmer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.farmer-card {
    background: white;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.04);
    border: 1px solid #eee;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}
.farmer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.farmer-card .card-top {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}

.farmer-avatar {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #2e7d32;
    flex-shrink: 0;
}
.farmer-avatar img {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
}

.farmer-info h4 { margin: 0 0 3px; font-weight: 700; font-size: 16px; color: #333; }
.farmer-info p { margin: 0; font-size: 13px; color: #888; }

.sub-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.sub-badge.active { background: #e8f5e9; color: #2e7d32; }
.sub-badge.inactive { background: #fff3e0; color: #e65100; }

.farmer-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}

.mini-stat {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 10px;
    text-align: center;
}
.mini-stat h5 { margin: 0; font-size: 18px; font-weight: 800; color: #333; }
.mini-stat small { font-size: 11px; color: #888; font-weight: 600; text-transform: uppercase; }

.farmer-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
    font-size: 13px;
    color: #666;
}
.farmer-meta span i { margin-right: 4px; color: #999; }

.card-actions {
    display: flex;
    gap: 8px;
}
.card-actions a {
    flex: 1;
    text-align: center;
    padding: 9px 10px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: 0.3s;
}
.btn-products { background: #e8f5e9; color: #2e7d32; }
.btn-products:hover { background: #2e7d32; color: white; }
.btn-orders { background: #e3f2fd; color: #1565c0; }
.btn-orders:hover { background: #1565c0; color: white; }
.btn-delete { background: #ffebee; color: #c62828; }
.btn-delete:hover { background: #c62828; color: white; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.empty-state i { font-size: 48px; margin-bottom: 15px; display: block; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-container">

    <div class="page-header">
        <div>
            <h2><i class="fas fa-tractor"></i> Farmer Accounts</h2>
            <p>Manage all registered farmers on the platform</p>
        </div>
        <div class="header-stat">
            <h3><?= $total_farmers ?></h3>
            <small>Total Farmers</small>
        </div>
    </div>

    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="farmerSearch" placeholder="Search by name, location, or phone..." onkeyup="filterFarmers()">
    </div>

    <?php if ($total_farmers > 0): ?>
    <div class="farmer-grid" id="farmerGrid">
        <?php foreach ($farmer_list as $f): ?>
        <div class="farmer-card" data-search="<?= strtolower(htmlspecialchars($f['name'] . ' ' . $f['email'] . ' ' . $f['location'] . ' ' . $f['phone'])) ?>">
            
            <span class="sub-badge <?= ($f['is_subscribed'] ?? 0) ? 'active' : 'inactive' ?>">
                <?= ($f['is_subscribed'] ?? 0) ? '● Subscribed' : '○ Free' ?>
            </span>

            <div class="card-top">
                <div class="farmer-avatar">
                    <?php if (!empty($f['profile_picture'])): ?>
                        <img src="uploads/profiles/<?= htmlspecialchars($f['profile_picture']) ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="farmer-info">
                    <h4><?= htmlspecialchars($f['name']) ?></h4>
                    <p><?= htmlspecialchars($f['email']) ?></p>
                </div>
            </div>

            <div class="farmer-stats">
                <div class="mini-stat">
                    <h5><?= $f['total_products'] ?></h5>
                    <small>Products</small>
                </div>
                <div class="mini-stat">
                    <h5><?= $f['total_orders'] ?></h5>
                    <small>Orders</small>
                </div>
                <div class="mini-stat">
                    <h5>₵<?= number_format($f['total_earnings'], 0) ?></h5>
                    <small>Earnings</small>
                </div>
            </div>

            <div class="farmer-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($f['location'] ?? 'N/A') ?></span>
                <span><i class="fas fa-phone"></i> <?= htmlspecialchars($f['phone'] ?? 'N/A') ?></span>
                <?php if (!empty($f['farm_size'])): ?>
                    <span><i class="fas fa-leaf"></i> <?= htmlspecialchars($f['farm_size']) ?></span>
                <?php endif; ?>
            </div>

            <div class="card-actions">
                <a href="browse_products.php?farmer_id=<?= $f['id'] ?>" class="btn-products"><i class="fas fa-box"></i> Products</a>
                <a href="farmer_orders.php?farmer_id=<?= $f['id'] ?>" class="btn-orders"><i class="fas fa-shopping-basket"></i> Orders</a>
                <a href="delete_user.php?id=<?= $f['id'] ?>" class="btn-delete" onclick="return confirm('Delete this farmer?')"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-tractor"></i>
        <h4>No farmers registered yet</h4>
        <p>Farmers will appear here once they sign up.</p>
    </div>
    <?php endif; ?>

</div>

<script>
function filterFarmers() {
    const query = document.getElementById('farmerSearch').value.toLowerCase();
    document.querySelectorAll('.farmer-card').forEach(card => {
        const data = card.getAttribute('data-search');
        card.style.display = data.includes(query) ? '' : 'none';
    });
}
</script>

</body>
</html>