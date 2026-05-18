<?php
session_start();
include 'db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// Fetch all buyers with their stats
$buyers = $conn->query("
    SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE buyer_id=u.id) AS total_orders,
        (SELECT COUNT(*) FROM orders WHERE buyer_id=u.id AND status='Completed') AS completed_orders,
        (SELECT COALESCE(SUM(total),0) FROM orders WHERE buyer_id=u.id AND status='Completed') AS total_spent,
        (SELECT COUNT(DISTINCT seller_id) FROM orders WHERE buyer_id=u.id) AS connected_farmers
    FROM users u 
    WHERE u.role='buyer' 
    ORDER BY u.id DESC
");

$total_buyers = 0;
$buyer_list = [];
if ($buyers) {
    while ($b = $buyers->fetch_assoc()) {
        $buyer_list[] = $b;
    }
    $total_buyers = count($buyer_list);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Buyer Accounts | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f6; margin: 0; padding-bottom: 50px; }

.page-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

.page-header {
    background: linear-gradient(135deg, #0d47a1, #1565c0, #1976d2);
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

/* Buyer Cards */
.buyer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.buyer-card {
    background: white;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.04);
    border: 1px solid #eee;
    transition: all 0.3s;
}
.buyer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.buyer-card .card-top {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}

.buyer-avatar {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #1565c0;
    flex-shrink: 0;
}
.buyer-avatar img {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
}

.buyer-info h4 { margin: 0 0 3px; font-weight: 700; font-size: 16px; color: #333; }
.buyer-info p { margin: 0; font-size: 13px; color: #888; }

.buyer-stats {
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

.buyer-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
    font-size: 13px;
    color: #666;
}
.buyer-meta span i { margin-right: 4px; color: #999; }

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
.btn-orders { background: #e3f2fd; color: #1565c0; }
.btn-orders:hover { background: #1565c0; color: white; }
.btn-chat { background: #f3e5f5; color: #7b1fa2; }
.btn-chat:hover { background: #7b1fa2; color: white; }
.btn-delete { background: #ffebee; color: #c62828; }
.btn-delete:hover { background: #c62828; color: white; }

.activity-tag {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    position: absolute;
    top: 15px;
    right: 15px;
}
.activity-tag.high { background: #e8f5e9; color: #2e7d32; }
.activity-tag.medium { background: #fff8e1; color: #f57f17; }
.activity-tag.low { background: #f5f5f5; color: #999; }

.buyer-card { position: relative; overflow: hidden; }

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
            <h2><i class="fas fa-shopping-basket"></i> Buyer Accounts</h2>
            <p>Manage all registered buyers on the platform</p>
        </div>
        <div class="header-stat">
            <h3><?= $total_buyers ?></h3>
            <small>Total Buyers</small>
        </div>
    </div>

    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="buyerSearch" placeholder="Search by name, location, or phone..." onkeyup="filterBuyers()">
    </div>

    <?php if ($total_buyers > 0): ?>
    <div class="buyer-grid" id="buyerGrid">
        <?php foreach ($buyer_list as $b): 
            $activity = $b['total_orders'] >= 5 ? 'high' : ($b['total_orders'] >= 1 ? 'medium' : 'low');
            $activity_label = $b['total_orders'] >= 5 ? 'Active Buyer' : ($b['total_orders'] >= 1 ? 'New Buyer' : 'Inactive');
        ?>
        <div class="buyer-card" data-search="<?= strtolower(htmlspecialchars($b['name'] . ' ' . $b['email'] . ' ' . $b['location'] . ' ' . $b['phone'])) ?>">
            
            <span class="activity-tag <?= $activity ?>"><?= $activity_label ?></span>

            <div class="card-top">
                <div class="buyer-avatar">
                    <?php if (!empty($b['profile_picture'])): ?>
                        <img src="uploads/profiles/<?= htmlspecialchars($b['profile_picture']) ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="buyer-info">
                    <h4><?= htmlspecialchars($b['name']) ?></h4>
                    <p><?= htmlspecialchars($b['email']) ?></p>
                </div>
            </div>

            <div class="buyer-stats">
                <div class="mini-stat">
                    <h5><?= $b['total_orders'] ?></h5>
                    <small>Orders</small>
                </div>
                <div class="mini-stat">
                    <h5><?= $b['connected_farmers'] ?></h5>
                    <small>Farmers</small>
                </div>
                <div class="mini-stat">
                    <h5>₵<?= number_format($b['total_spent'], 0) ?></h5>
                    <small>Spent</small>
                </div>
            </div>

            <div class="buyer-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($b['location'] ?? 'N/A') ?></span>
                <span><i class="fas fa-phone"></i> <?= htmlspecialchars($b['phone'] ?? 'N/A') ?></span>
                <span><i class="fas fa-calendar"></i> Joined <?= date('M Y', strtotime($b['created_at'] ?? 'now')) ?></span>
            </div>

            <div class="card-actions">
                <a href="buyer_orders.php?buyer_id=<?= $b['id'] ?>" class="btn-orders"><i class="fas fa-box-open"></i> Orders</a>
                <a href="chat.php?user_id=<?= $b['id'] ?>" class="btn-chat"><i class="fas fa-comment"></i> Chat</a>
                <a href="delete_user.php?id=<?= $b['id'] ?>" class="btn-delete" onclick="return confirm('Delete this buyer?')"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-shopping-basket"></i>
        <h4>No buyers registered yet</h4>
        <p>Buyers will appear here once they sign up.</p>
    </div>
    <?php endif; ?>

</div>

<script>
function filterBuyers() {
    const query = document.getElementById('buyerSearch').value.toLowerCase();
    document.querySelectorAll('.buyer-card').forEach(card => {
        const data = card.getAttribute('data-search');
        card.style.display = data.includes(query) ? '' : 'none';
    });
}
</script>

</body>
</html>