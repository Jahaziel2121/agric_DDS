<?php
session_start();
include 'db.php';
include 'back_button.php';

$query = trim($_GET['q'] ?? '');
$results = [];

if ($query !== '') {
    // Search products
    $products = $conn->query("
        SELECT 'product' AS result_type, p.id, p.product_name AS title, p.type AS subtitle,
               p.price, p.quantity, p.unit, p.image, p.is_promoted,
               u.name AS farmer_name, u.location
        FROM products p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE (p.product_name LIKE '%$query%' OR p.type LIKE '%$query%' OR u.name LIKE '%$query%' OR u.location LIKE '%$query%')
        ORDER BY p.is_promoted DESC, p.id DESC
    ");

    // Search companies / farm services
    $companies = $conn->query("
        SELECT 'service' AS result_type, c.id, c.name AS title, s.service_name AS subtitle,
               s.price, s.service_type, s.description AS detail, c.location, c.phone
        FROM companies c
        JOIN company_services s ON c.id = s.company_id
        WHERE (c.name LIKE '%$query%' OR s.service_name LIKE '%$query%' OR s.service_type LIKE '%$query%' OR c.location LIKE '%$query%')
        ORDER BY s.price ASC
    ");

    // Search farmers
    $farmers = $conn->query("
        SELECT 'farmer' AS result_type, u.id, u.name AS title, u.location AS subtitle, u.phone
        FROM users u
        WHERE u.role = 'farmer' AND (u.name LIKE '%$query%' OR u.location LIKE '%$query%')
        ORDER BY u.id DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Search - AGRIC DSS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f2; }

.search-hero {
    background: linear-gradient(135deg, #1b5e20, #2e7d32, #43a047);
    padding: 40px 20px;
    text-align: center;
    color: white;
}

.search-hero h2 { font-weight: 700; margin-bottom: 5px; }
.search-hero p { opacity: 0.85; margin-bottom: 20px; }

.search-bar {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
    gap: 0;
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 6px 25px rgba(0,0,0,0.2);
}

.search-bar input {
    flex: 1;
    border: none;
    padding: 15px 22px;
    font-size: 15px;
    outline: none;
}

.search-bar button {
    background: #ff8f00;
    color: white;
    border: none;
    padding: 15px 28px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}
.search-bar button:hover { background: #e65100; }

.section-label {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #888;
    margin: 25px 0 12px;
}

.result-card {
    background: white;
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.25s;
    text-decoration: none;
    color: inherit;
    border-left: 4px solid transparent;
}

.result-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    color: inherit;
}

.result-card.product { border-left-color: #2e7d32; }
.result-card.service { border-left-color: #1565c0; }
.result-card.farmer  { border-left-color: #6a1b9a; }
.result-card.promoted { border-left-color: #ff8f00; background: linear-gradient(to right, #fffde7, white); }

.result-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.result-icon.product { background: #e8f5e9; }
.result-icon.service { background: #e3f2fd; }
.result-icon.farmer  { background: #f3e5f5; }

.result-card img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 10px;
    flex-shrink: 0;
}

.result-title { font-weight: 600; font-size: 15px; margin: 0; }
.result-sub { font-size: 13px; color: #888; margin: 2px 0 0; }

.promoted-tag {
    background: #ff8f00;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    margin-left: 6px;
}

.price-tag {
    margin-left: auto;
    font-size: 14px;
    font-weight: 700;
    color: #2e7d32;
    white-space: nowrap;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #aaa;
}

.no-results .icon { font-size: 60px; margin-bottom: 15px; }
.no-results h4 { color: #666; }
</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<!-- SEARCH HERO -->
<div class="search-hero">
    <h2><i class='fas fa-search'></i> Search the Platform</h2>
    <p>Find farm products, services, and farmers across the platform</p>

    <form method="GET" action="search.php" class="search-bar">
        <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($query) ?>"
            placeholder="Search products, farmers, services..."
            autofocus
        >
        <button type="submit">Search</button>
    </form>
</div>

<div class="container py-4" style="max-width: 750px;">

<?php if ($query === ''): ?>
    <div class="no-results">
        <div class="icon"><i class='fas fa-wheat-awn'></i></div>
        <h4>What are you looking for?</h4>
        <p>Search for farm produce, livestock, services, companies, or farmers by name or location.</p>
    </div>

<?php else: ?>

    <?php
    $total_products = $products ? $products->num_rows : 0;
    $total_companies = $companies ? $companies->num_rows : 0;
    $total_farmers   = $farmers  ? $farmers->num_rows  : 0;
    $total = $total_products + $total_companies + $total_farmers;
    ?>

    <p style="color: #888; font-size: 14px;">
        <b><?= $total ?></b> result<?= $total != 1 ? 's' : '' ?> for "<b><?= htmlspecialchars($query) ?></b>"
    </p>

    <?php if ($total === 0): ?>
        <div class="no-results">
            <div class="icon">😔</div>
            <h4>No results found</h4>
            <p>Try a different keyword like "maize", "Kumasi", "tractor", or "chicken".</p>
        </div>
    <?php endif; ?>

    <!-- PRODUCTS -->
    <?php if ($total_products > 0): ?>
        <div class="section-label"><i class='fas fa-wheat-awn'></i> Farm Products (<?= $total_products ?>)</div>
        <?php while ($r = $products->fetch_assoc()): ?>
            <a href="browse_products.php" class="result-card product <?= $r['is_promoted'] ? 'promoted' : '' ?>">
                <?php if (!empty($r['image']) && file_exists("uploads/" . $r['image'])): ?>
                    <img src="uploads/<?= $r['image'] ?>" alt="">
                <?php else: ?>
                    <div class="result-icon product"><i class='fas fa-seedling'></i></div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <p class="result-title">
                        <?= htmlspecialchars($r['title'] ?: $r['subtitle']) ?>
                        <?php if ($r['is_promoted']): ?><span class="promoted-tag"><i class='fas fa-star'></i> TOP</span><?php endif; ?>
                    </p>
                    <p class="result-sub">
                        <i class='fas fa-user-tie'></i> <?= htmlspecialchars($r['farmer_name'] ?? 'Farmer') ?>
                        <?php if ($r['location']): ?>&nbsp;· <i class='fas fa-map-marker-alt'></i> <?= htmlspecialchars($r['location']) ?><?php endif; ?>
                        &nbsp;· <?= $r['quantity'] ?> <?= $r['unit'] ?>
                    </p>
                </div>
                <?php if ($r['price']): ?>
                    <div class="price-tag">GHS <?= number_format($r['price'], 2) ?></div>
                <?php endif; ?>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>

    <!-- SERVICES -->
    <?php if ($total_companies > 0): ?>
        <div class="section-label"><i class='fas fa-seedling'></i> Farm Services (<?= $total_companies ?>)</div>
        <?php while ($r = $companies->fetch_assoc()): ?>
            <a href="buy.php" class="result-card service">
                <div class="result-icon service"><i class='fas fa-tractor'></i></div>
                <div style="flex: 1;">
                    <p class="result-title"><?= htmlspecialchars($r['title']) ?></p>
                    <p class="result-sub">
                        🔧 <?= htmlspecialchars($r['subtitle']) ?>
                        &nbsp;· <?= ucfirst($r['service_type']) ?>
                        <?php if ($r['location']): ?>&nbsp;· <i class='fas fa-map-marker-alt'></i> <?= htmlspecialchars($r['location']) ?><?php endif; ?>
                    </p>
                </div>
                <div class="price-tag">GHS <?= number_format($r['price'], 2) ?></div>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>

    <!-- FARMERS -->
    <?php if ($total_farmers > 0): ?>
        <div class="section-label"><i class='fas fa-user-tie'></i> Farmers (<?= $total_farmers ?>)</div>
        <?php while ($r = $farmers->fetch_assoc()): ?>
            <a href="browse_products.php" class="result-card farmer">
                <div class="result-icon farmer"><i class='fas fa-user'></i></div>
                <div style="flex: 1;">
                    <p class="result-title"><?= htmlspecialchars($r['title']) ?></p>
                    <p class="result-sub"><i class='fas fa-map-marker-alt'></i> <?= htmlspecialchars($r['subtitle'] ?? 'No location') ?> &nbsp;· <i class='fas fa-phone-alt'></i> <?= $r['phone'] ?></p>
                </div>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>

<?php endif; ?>

</div>

</body>
</html>
