<?php
session_start();
include 'db.php';
include 'back_button.php';

$category = isset($_GET['category']) ? $_GET['category'] : "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agri Services Marketplace</title>

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f4f7f6;
    margin: 0;
    padding-bottom: 50px;
}

.page-title {
    text-align: center;
    color: #1b5e20;
    font-weight: 800;
    margin: 30px 0 20px;
    font-size: 28px;
}

.categories {
    text-align: center;
    margin-bottom: 30px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

.categories a {
    padding: 10px 20px;
    background: white;
    color: #555;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eee;
    transition: 0.3s;
}

.categories a:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.categories a.active {
    background: linear-gradient(135deg, #f57c00, #ff9800);
    color: white;
    border: none;
    box-shadow: 0 5px 15px rgba(245,124,0,0.3);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    padding: 0 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.service-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #eee;
    transition: 0.3s;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.service-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.service-img-placeholder {
    width: 100%;
    height: 200px;
    background: #e8f5e9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #81c784;
    font-size: 50px;
    border-bottom: 1px solid #eee;
}

.service-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.service-type {
    display: inline-block;
    padding: 4px 12px;
    background: #fff3e0;
    color: #e65100;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 10px;
    align-self: flex-start;
}

.service-title {
    margin: 0 0 10px;
    font-size: 18px;
    font-weight: 800;
    color: #222;
}

.service-company {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.service-desc {
    color: #777;
    font-size: 13px;
    line-height: 1.5;
    margin: 10px 0 15px;
    flex: 1;
}

.service-price {
    font-size: 22px;
    color: #2e7d32;
    font-weight: 800;
    margin-bottom: 15px;
}

.btn-book {
    background: linear-gradient(135deg, #2e7d32, #4caf50);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.btn-book:hover {
    background: #1b5e20;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46,125,50,0.3);
}
</style>

</head>
<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<h2 class="page-title"><i class='fas fa-tractor'></i> Agricultural Services</h2>

<!-- FILTER -->
<div class="categories">
    <a href="buy.php" class="<?= $category=='' ? 'active' : '' ?>">All Services</a>
    <a href="buy.php?category=labor" class="<?= $category=='labor' ? 'active' : '' ?>"><i class="fas fa-users"></i> Labor</a>
    <a href="buy.php?category=inputs" class="<?= $category=='inputs' ? 'active' : '' ?>"><i class="fas fa-seedling"></i> Inputs</a>
    <a href="buy.php?category=tools" class="<?= $category=='tools' ? 'active' : '' ?>"><i class="fas fa-tools"></i> Tools & Rentals</a>
    <a href="buy.php?category=all_in_one" class="<?= $category=='all_in_one' ? 'active' : '' ?>"><i class="fas fa-layer-group"></i> All-in-One</a>
</div>

<?php
/* =========================
   SAFE JOIN QUERY
========================= */

$sql = "SELECT c.*, s.service_type, s.service_name, s.price, s.description AS service_desc, s.image
        FROM companies c
        JOIN company_services s ON c.id = s.company_id";

if ($category != "" && $category != "all") {
    $sql .= " WHERE s.service_type = '$category'";
}

$sql .= " ORDER BY s.price ASC";

$result = $conn->query($sql);

if ($result === false) {
    die("SQL ERROR: " . $conn->error);
}
?>

<div class="services-grid">

<?php if ($result->num_rows > 0) { ?>

    <?php while ($row = $result->fetch_assoc()) { ?>

        <div class="service-card">
            
            <?php if (!empty($row['image'])): ?>
                <img src="images/<?= htmlspecialchars($row['image']) ?>" alt="Service Image" class="service-img">
            <?php else: ?>
                <div class="service-img-placeholder">
                    <i class="fas fa-tractor"></i>
                </div>
            <?php endif; ?>

            <div class="service-content">
                <span class="service-type"><?= htmlspecialchars(ucfirst($row['service_type'])) ?></span>
                
                <h3 class="service-title"><?= htmlspecialchars($row['service_name']) ?></h3>
                
                <div class="service-company">
                    <i class="fas fa-building text-primary"></i> <?= htmlspecialchars($row['name']) ?>
                </div>
                <div class="service-company">
                    <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($row['location']) ?>
                </div>
                
                <p class="service-desc"><?= htmlspecialchars($row['service_desc']) ?></p>
                
                <div class="service-price">GHS <?= number_format($row['price'], 2) ?></div>
                
                <button class="btn-book" onclick="window.location.href='service_booking.php?company_id=<?= $row['id'] ?>'">
                    <i class="fas fa-calendar-check"></i> Book Now
                </button>
            </div>
            
        </div>

    <?php } ?>

<?php } else { ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px dashed #ccc;">
        <i class="fas fa-box-open fs-1 text-muted mb-3 opacity-50"></i>
        <h4 style="color: #666; margin:0;">No services found in this category.</h4>
    </div>
<?php } ?>

</div>

</body>
</html>