<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_SESSION['admin']) ? 0 : $_SESSION['user_id'];

// Mark individual as read
if (isset($_GET['mark_read'])) {
    $nid = intval($_GET['mark_read']);
    $conn->query("UPDATE notifications SET is_read=1 WHERE id='$nid' AND user_id='$user_id'");
    
    if (!empty($_GET['redirect'])) {
        header("Location: " . $_GET['redirect']);
    } else {
        header("Location: notifications.php");
    }
    exit();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_id='$user_id'");
    header("Location: notifications.php");
    exit();
}

// Fetch notifications
$sql = "SELECT * FROM notifications WHERE user_id='$user_id' ORDER BY is_read ASC, id DESC";
$result = $conn->query($sql);

// Count unread
$unread_q = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id='$user_id' AND is_read=0");
$unread_count = ($unread_q && $ur = $unread_q->fetch_assoc()) ? (int)$ur['c'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Notifications</title>
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

.page-header h3 { margin: 0 0 5px; }
.page-header p { margin: 0; opacity: 0.85; }

.container { width: 90%; max-width: 700px; margin: auto; }

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.unread-count {
    background: #ef4444;
    color: white;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.mark-all {
    background: #e8f5e9;
    color: #2e7d32;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s;
}

.mark-all:hover { background: #c8e6c9; color: #1b5e20; }

.notif-card {
    background: white;
    padding: 16px 18px;
    border-radius: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: all 0.2s;
    border-left: 4px solid transparent;
}

.notif-card:hover {
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}

.notif-card.unread {
    background: #fffde7;
    border-left-color: #ff8f00;
}

.notif-card.read {
    opacity: 0.7;
    border-left-color: #e0e0e0;
}

.notif-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
}

.notif-dot.unread { background: #ff8f00; }
.notif-dot.read { background: #e0e0e0; }

.notif-content { flex: 1; }

.notif-content .message {
    font-size: 14px;
    color: #333;
    line-height: 1.5;
    margin: 0 0 4px;
}

.notif-content .time {
    font-size: 12px;
    color: #aaa;
}

.notif-action {
    flex-shrink: 0;
}

.notif-action a {
    color: #2e7d32;
    font-size: 12px;
    text-decoration: none;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    background: #e8f5e9;
    transition: 0.2s;
}

.notif-action a:hover { background: #c8e6c9; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #bbb;
}

.empty-state .icon { font-size: 50px; margin-bottom: 10px; }
.empty-state h4 { color: #888; }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="page-header">
    <h3><i class='fas fa-bell'></i> Notifications</h3>
    <p>Stay updated on orders, confirmations & activity</p>
</div>

<div class="container">

<?php if ($result && $result->num_rows > 0): ?>

    <div class="top-bar">
        <span class="unread-count">
            <?= $unread_count ?> unread notification<?= $unread_count != 1 ? 's' : '' ?>
        </span>

        <?php if ($unread_count > 0): ?>
            <a href="notifications.php?mark_all_read=1" class="mark-all"><i class='fas fa-check-circle'></i> Mark All as Read</a>
        <?php endif; ?>
    </div>

    <?php 
    // Helper function to figure out where a notification should go
    function getNotifLink($msg, $role) {
        $lower = strtolower($msg);
        $is_admin = ($role === 'admin');
        
        // Admin: subscription notifications
        if (strpos($lower, 'subscription') !== false && $is_admin) {
            return 'admin_dashboard.php?tab=subs';
        }
        // Admin: promotion notifications  
        if (strpos($lower, 'promotion') !== false && $is_admin) {
            return 'admin_dashboard.php?tab=promos';
        }
        // Admin: loan notifications
        if (strpos($lower, 'loan') !== false && $is_admin) {
            return 'admin_dashboard.php?tab=loans';
        }
        // Farmer: subscription approved/rejected
        if (strpos($lower, 'subscription') !== false && $role === 'farmer') {
            return 'subscription.php';
        }
        // Farmer: promotion approved/rejected
        if (strpos($lower, 'promotion') !== false && $role === 'farmer') {
            return 'promote_product.php';
        }
        // Orders
        if (strpos($lower, 'order') !== false || strpos($lower, 'placed') !== false || strpos($lower, 'delivered') !== false) {
            return $role === 'farmer' ? 'farmer_orders.php' : 'my_orders.php';
        }
        // Products / stock
        if (strpos($lower, 'product') !== false || strpos($lower, 'stock') !== false) {
            return $role === 'farmer' ? 'my_products.php' : 'browse_products.php';
        }
        // Loan
        if (strpos($lower, 'loan') !== false) {
            return 'loan.php';
        }
        // Fallback
        return '#';
    }
    
    // Check if a notification message has embedded action buttons (approve/reject)
    function hasEmbeddedButtons($msg) {
        return strpos($msg, 'approve_') !== false || strpos($msg, 'reject_') !== false;
    }
    
    while ($row = $result->fetch_assoc()):
        $is_unread = !$row['is_read'];
        $current_role = isset($_SESSION['admin']) ? 'admin' : ($_SESSION['role'] ?? '');
        $base_link = getNotifLink($row['message'], $current_role);
        $has_buttons = hasEmbeddedButtons($row['message']);
        $click_url = "notifications.php?mark_read={$row['id']}&redirect=" . urlencode($base_link);
        $card_clickable = (!$has_buttons && $base_link !== '#');
    ?>
        <div class="notif-card <?= $is_unread ? 'unread' : 'read' ?>" style="<?= $card_clickable ? 'cursor:pointer;' : '' ?> transition: 0.2s;" <?= $card_clickable ? "onclick=\"window.location.href='$click_url'\"" : '' ?> onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 3px 10px rgba(0,0,0,0.05)';">
            <div class="notif-dot <?= $is_unread ? 'unread' : 'read' ?>"></div>

            <div class="notif-content">
                <p class="message"><?= $row['message'] ?></p>
                <span class="time"><i class='far fa-calendar-alt'></i> <?= $row['created_at'] ?? 'Just now' ?></span>
            </div>

            <?php if ($has_buttons): ?>
                <!-- Admin notification with embedded Approve/Reject buttons - no extra action needed -->
            <?php elseif ($is_unread): ?>
                <div class="notif-action">
                    <a href="notifications.php?mark_read=<?= $row['id'] ?>&redirect=<?= urlencode($base_link) ?>" class="btn btn-sm" style="background:#e8f5e9; color:#2e7d32; font-weight:600; padding:6px 12px; border-radius:15px; text-decoration:none;" onclick="event.stopPropagation();"><i class="fas fa-check"></i> Mark Read</a>
                </div>
            <?php elseif ($base_link !== '#'): ?>
                <div style="font-size: 13px; margin-left: 15px;">
                    <a href="<?= $base_link ?>" style="color: #1565c0; font-weight: 600; text-decoration: none;" onclick="event.stopPropagation();"><i class='fas fa-external-link-alt'></i> View Details</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

<?php else: ?>
    <div class="empty-state">
        <div class="icon"><i class='fas fa-bell-slash'></i></div>
        <h4>No notifications yet</h4>
        <p>You'll see alerts here when orders are placed, confirmed, or completed.</p>
    </div>
<?php endif; ?>

</div>

</body>
</html>