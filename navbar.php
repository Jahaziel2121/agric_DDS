<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? 'User';
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
$search_query = htmlspecialchars($_GET['q'] ?? '');

// Determine home URL based on role
$home_url = 'index.php';
if ($is_admin) {
    $home_url = 'admin_dashboard.php';
} elseif ($role === 'farmer') {
    $home_url = 'dashboard.php';
} elseif ($role === 'buyer') {
    $home_url = 'buyer_dashboard.php';
}

// Count unread notifications
$_notif_count = 0;
$_chat_count = 0;
if ((isset($_SESSION['user_id']) || isset($_SESSION['admin'])) && isset($conn)) {
    $current_user_id = isset($_SESSION['admin']) ? 0 : ($_SESSION['user_id'] ?? 0);
    
    $_nq = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id='$current_user_id' AND is_read=0");
    if ($_nq && $_nr = $_nq->fetch_assoc()) {
        $_notif_count = (int)$_nr['c'];
    }
    
    $_cq = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id='$current_user_id' AND is_read=0");
    if ($_cq && $_cr = $_cq->fetch_assoc()) {
        $_chat_count = (int)$_cr['c'];
    }
    
    if (!isset($_SESSION['admin'])) {
        $_u_q = $conn->query("SELECT profile_picture, role FROM users WHERE id='$current_user_id'");
        if ($_u_q && $_ur = $_u_q->fetch_assoc()) {
            $_user_pic = $_ur['profile_picture'];
        }
    }
    
    // Count pending orders
    $_order_count = 0;
    $_user_role = $_SESSION['role'] ?? '';
    if ($_user_role === 'farmer') {
        $_oq = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE seller_id='$current_user_id' AND status='Pending'");
    } else {
        $_oq = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE buyer_id='$current_user_id' AND status='Pending'");
    }
    if (isset($_oq) && $_oq && $_or = $_oq->fetch_assoc()) {
        $_order_count = (int)$_or['c'];
    }
}
$_notif_badge = "<span class='notif-badge' id='badge-notif' style='display:" . ($_notif_count > 0 ? "inline-block" : "none") . "'>$_notif_count</span>";
$_chat_badge = "<span class='notif-badge' id='badge-chat' style='display:" . ($_chat_count > 0 ? "inline-block" : "none") . "'>$_chat_count</span>";
$_order_badge = "<span class='notif-badge' id='badge-orders' style='display:" . ($_order_count > 0 ? "inline-block" : "none") . "'>$_order_count</span>";
?>

<!-- FontAwesome Icons (Uniform Color & Style) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Global Animations -->
<link rel="stylesheet" href="animations.css">
<script src="animations.js" defer></script>

<nav class="agric-nav">
    <div class="nav-container">
        <a href="<?= $home_url ?>" class="nav-brand"><i class='fas fa-wheat-awn'></i> AGRIC DSS</a>

        <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>

        <div class="nav-links">

            <?php if ($is_admin): ?>
                <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>"><i class='fas fa-chart-line'></i> Dashboard</a>
                <a href="view_farmer_dashboard.php" class="<?= $current_page == 'view_farmer_dashboard.php' ? 'active' : '' ?>"><i class='fas fa-wheat-awn'></i> Farmers</a>
                <a href="view_buyer_dashboard.php" class="<?= $current_page == 'view_buyer_dashboard.php' ? 'active' : '' ?>"><i class='fas fa-shopping-cart'></i> Buyers</a>
                <a href="manage_prices.php" class="<?= $current_page == 'manage_prices.php' ? 'active' : '' ?>"><i class='fas fa-dollar-sign'></i> Prices</a>
                <a href="notifications.php" class="nav-notif-link <?= $current_page == 'notifications.php' ? 'active' : '' ?>"><i class='fas fa-bell'></i> Alerts <?= $_notif_badge ?></a>

            <?php elseif ($role === 'farmer'): ?>
                <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class='fas fa-chart-line'></i> Dashboard</a>
                <a href="sell.php" class="<?= $current_page == 'sell.php' ? 'active' : '' ?>"><i class='fas fa-wheat-awn'></i> Sell</a>
                <a href="my_products.php" class="<?= $current_page == 'my_products.php' ? 'active' : '' ?>"><i class='fas fa-box'></i> My Products</a>
                <a href="buy.php" class="<?= $current_page == 'buy.php' ? 'active' : '' ?>"><i class='fas fa-seedling'></i> Services</a>
                <a href="loan.php" class="<?= $current_page == 'loan.php' ? 'active' : '' ?>"><i class='fas fa-money-bill-wave'></i> Loans</a>
                <a href="farmer_orders.php" class="nav-notif-link <?= $current_page == 'farmer_orders.php' ? 'active' : '' ?>"><i class='fas fa-inbox'></i> Orders <?= $_order_badge ?></a>
                <a href="chat.php" class="nav-notif-link <?= $current_page == 'chat.php' ? 'active' : '' ?>"><i class='fas fa-comment-dots'></i> Inbox <?= $_chat_badge ?></a>
                <a href="promote_product.php" class="<?= $current_page == 'promote_product.php' ? 'active' : '' ?>" style="color: #ffd54f !important;"><i class='fas fa-star'></i> Promote</a>
                <a href="subscription.php" class="<?= $current_page == 'subscription.php' ? 'active' : '' ?>" style="color: #80cbc4 !important;"><i class='fas fa-credit-card'></i> Plan</a>
                <a href="notifications.php" class="nav-notif-link <?= $current_page == 'notifications.php' ? 'active' : '' ?>"><i class='fas fa-bell'></i> Alerts <?= $_notif_badge ?></a>

            <?php elseif ($role === 'buyer'): ?>
                <a href="buyer_dashboard.php" class="<?= $current_page == 'buyer_dashboard.php' ? 'active' : '' ?>"><i class='fas fa-chart-line'></i> Dashboard</a>
                <a href="browse_products.php" class="<?= $current_page == 'browse_products.php' ? 'active' : '' ?>"><i class='fas fa-wheat-awn'></i> Marketplace</a>
                <a href="buyer_request_form.php" class="<?= $current_page == 'buyer_request_form.php' ? 'active' : '' ?>"><i class='fas fa-bullhorn'></i> Buy Requests</a>
                <a href="my_orders.php" class="nav-notif-link <?= $current_page == 'my_orders.php' ? 'active' : '' ?>"><i class='fas fa-box'></i> My Orders <?= $_order_badge ?></a>
                <a href="chat.php" class="nav-notif-link <?= $current_page == 'chat.php' ? 'active' : '' ?>"><i class='fas fa-comment-dots'></i> Inbox <?= $_chat_badge ?></a>
                <a href="notifications.php" class="nav-notif-link <?= $current_page == 'notifications.php' ? 'active' : '' ?>"><i class='fas fa-bell'></i> Alerts <?= $_notif_badge ?></a>

            <?php else: ?>
                <a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>"><i class='fas fa-lock'></i> Login</a>
                <a href="register.php" class="<?= $current_page == 'register.php' ? 'active' : '' ?>"><i class='fas fa-edit'></i> Register</a>
            <?php endif; ?>

            <!-- SEARCH BAR (always visible) -->
            <form action="search.php" method="GET" class="nav-search">
                <input type="text" name="q" value="<?= $search_query ?>" placeholder="Search...">
                <button type="submit">Go</button>
            </form>

            <?php if ($role || $is_admin): ?>
                <a href="profile.php" class="nav-user">
                    <?php if (!empty($_user_pic)): ?>
                        <img src="uploads/profiles/<?= htmlspecialchars($_user_pic) ?>" class="nav-user-pic" alt="Profile">
                    <?php else: ?>
                        <i class='fas fa-user-circle' style="font-size:18px;"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($name) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Live Badge Updates Script -->
<script>
function updateNavBadges() {
    fetch('api_counts.php')
        .then(res => res.json())
        .then(data => {
            const notifBadge = document.getElementById('badge-notif');
            const chatBadge = document.getElementById('badge-chat');
            const ordersBadge = document.getElementById('badge-orders');
            const ordersBadgeMobile = document.getElementById('badge-orders-mobile');

            if (notifBadge) {
                notifBadge.textContent = data.notif;
                notifBadge.style.display = data.notif > 0 ? 'inline-block' : 'none';
            }
            if (chatBadge) {
                chatBadge.textContent = data.chat;
                chatBadge.style.display = data.chat > 0 ? 'inline-block' : 'none';
            }
            if (ordersBadge) {
                ordersBadge.textContent = data.orders;
                ordersBadge.style.display = data.orders > 0 ? 'inline-block' : 'none';
            }
            if (ordersBadgeMobile) {
                ordersBadgeMobile.textContent = data.orders;
                ordersBadgeMobile.style.display = data.orders > 0 ? 'inline-block' : 'none';
            }
        })
        .catch(err => console.error("Error fetching badge counts", err));
}

// Update badges every 3 seconds
setInterval(updateNavBadges, 3000);
</script>

<style>
.agric-nav {
    background: linear-gradient(135deg, #1b5e20, #2e7d32, #43a047);
    padding: 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1300px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    flex-wrap: nowrap;
}

.nav-brand {
    color: white;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    padding: 14px 0;
    margin-right: 30px;
    white-space: nowrap;
    flex-shrink: 0;
}

.nav-brand:hover { color: #c8e6c9; }

.nav-toggle {
    display: none;
    background: none;
    border: 2px solid rgba(255,255,255,0.5);
    color: white;
    font-size: 20px;
    padding: 3px 10px;
    border-radius: 6px;
    cursor: pointer;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 8px;
    color: white;
    font-size: 13px;
    font-weight: 600;
    margin-left: 10px;
    padding: 5px 10px;
    background: rgba(255,255,255,0.15);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.3);
    text-decoration: none;
    transition: 0.2s;
}

.nav-user:hover {
    background: rgba(255,255,255,0.25);
    color: white;
}

.nav-user-pic {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(255,255,255,0.8);
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 2px;
    flex-wrap: nowrap;
    overflow-x: auto;
}

.nav-links a {
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    padding: 7px 10px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nav-links a:hover {
    background: rgba(255,255,255,0.15);
    color: white;
}

.nav-links a.active {
    background: rgba(255,255,255,0.25);
    color: white;
    font-weight: 700;
}

/* SEARCH BAR IN NAV */
.nav-search {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.12);
    border-radius: 20px;
    overflow: hidden;
    margin: 0 6px;
    border: 1px solid rgba(255,255,255,0.25);
}

.nav-search input {
    background: transparent;
    border: none;
    color: white;
    padding: 6px 12px;
    font-size: 12.5px;
    width: 140px;
    outline: none;
}

.nav-search input::placeholder { color: rgba(255,255,255,0.65); }

.nav-search button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.nav-search button:hover { background: rgba(255,255,255,0.35); }

.nav-user {
    color: #c8e6c9;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 10px;
    border-left: 1px solid rgba(255,255,255,0.25);
    margin-left: 4px;
    white-space: nowrap;
}

.nav-logout {
    background: rgba(198, 40, 40, 0.7) !important;
    color: white !important;
    border-radius: 7px;
    margin-left: 2px;
}

.nav-logout:hover { background: rgba(198, 40, 40, 1) !important; }

/* NOTIFICATION BADGE */
.notif-badge {
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 3px;
    min-width: 16px;
    text-align: center;
    display: inline-block;
    line-height: 14px;
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

.nav-notif-link { position: relative; }

@media (max-width: 900px) {
    .nav-toggle { display: block; }

    .nav-links {
        display: none;
        flex-direction: column;
        width: 100%;
        padding: 10px 0;
        gap: 2px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .nav-links.open { display: flex; }
    .nav-container { flex-wrap: wrap; }

    .nav-links a {
        width: 100%;
        padding: 10px 15px;
        border-radius: 0;
    }

    .nav-search {
        width: 90%;
        margin: 8px 5%;
        background: rgba(255,255,255,0.15);
    }

    .nav-search input { width: 100%; }

    .nav-user {
        border-left: none;
        border-top: 1px solid rgba(255,255,255,0.2);
        margin-left: 0;
        width: 100%;
        padding: 10px 15px;
    }
}
</style>