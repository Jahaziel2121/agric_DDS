<?php
session_start();
include 'db.php';
include 'back_button.php';

/* <i class='fas fa-check-circle'></i> AUTO IMAGE FUNCTION (UNCHANGED) */
function getProductImage($type) {
    $type = strtolower($type);
    if (strpos($type, 'maize') !== false) return 'maize.jpg';
    if (strpos($type, 'rice') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'beans') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'tomato') !== false) return 'tomato.jpg';
    if (strpos($type, 'onion') !== false) return 'onions.jpeg';
    if (strpos($type, 'pepper') !== false) return 'green,red,yellwo papper.jpg';
    if (strpos($type, 'cabbage') !== false) return 'vegetables.jpg';
    if (strpos($type, 'carrot') !== false) return 'vegetables.jpg';
    if (strpos($type, 'potato') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'garlic') !== false) return 'onions.jpeg';
    if (strpos($type, 'ginger') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'cucumber') !== false) return 'vegetables.jpg';
    if (strpos($type, 'lettuce') !== false) return 'vegetables.jpg';
    if (strpos($type, 'spinach') !== false) return 'vegetables.jpg';
    if (strpos($type, 'okra') !== false) return 'okro.jpg';
    if (strpos($type, 'eggplant') !== false) return 'vegetables.jpg';
    if (strpos($type, 'watermelon') !== false) return 'watermelon.jpg';
    if (strpos($type, 'pineapple') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'mango') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'banana') !== false) return 'food stuffs.jpg';
    if (strpos($type, 'poultry') !== false) return 'chicken.jpg';
    if (strpos($type, 'chicken') !== false) return 'chicken.jpg';
    if (strpos($type, 'livestock') !== false) return 'cattle.jpg';
    if (strpos($type, 'cattle') !== false) return 'cattle.jpg';
    if (strpos($type, 'pig') !== false) return 'pig.jpg';
    if (strpos($type, 'rabbit') !== false) return 'rabbit.jpg';
    if (strpos($type, 'turkey') !== false) return 'turkey.jpg';
    if (strpos($type, 'goat') !== false) return 'goat.jpg';

    // DEFAULT IMAGE
    return "bg.jpg";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmers Marketplace</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    font-family: Arial;
    margin: 0;
    background: #f4f6f8;
}

/* HEADER */
.header {
    background: linear-gradient(90deg, #2e7d32, #43a047);
    color: white;
    padding: 18px;
    text-align: center;
}

.header h2 {
    margin: 0;
}

/* GRID */
.container {
    padding: 20px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

/* CARD */
.card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-6px);
}

/* IMAGE */
.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

/* BODY */
.body {
    padding: 15px;
}

/* TITLE */
.title {
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
}

/* FARMER NAME */
.farmer {
    color: #444;
    font-weight: 600;
    margin-top: 5px;
}

/* LOCATION */
.location {
    font-size: 13px;
    color: #777;
}

/* PRICE */
.price {
    margin-top: 8px;
    font-weight: bold;
    color: #2e7d32;
}

/* BADGE */
.badge {
    display: inline-block;
    background: #e8f5e9;
    color: #2e7d32;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    margin-top: 8px;
}

/* BUTTON */
button {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.buy {
    background: #2e7d32;
    color: white;
}

.no-data {
    text-align: center;
    color: #777;
    margin-top: 50px;
}
</style>

</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="header">
    <h2><i class='fas fa-wheat-awn'></i> Farmers Marketplace</h2>
    <p>Buy directly from farmers</p>

    <form method="GET" action="browse_products.php" class="market-search">
        <div class="market-search-box">
            <span class="search-icon"><i class='fas fa-search'></i></span>
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search by product, farmer or location...">
            <button type="submit">Search</button>
        </div>
    </form>

    <?php if (!empty($_GET['search'])): ?>
        <a href="browse_products.php" class="clear-search">✕ Clear search results</a>
    <?php endif; ?>
</div>

<style>
.market-search {
    max-width: 520px;
    margin: 18px auto 0;
}

.market-search-box {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    border: 2px solid rgba(255,255,255,0.3);
    transition: box-shadow 0.3s;
}

.market-search-box:focus-within {
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    border-color: rgba(255,255,255,0.5);
}

.search-icon {
    padding: 0 0 0 16px;
    font-size: 16px;
    opacity: 0.5;
}

.market-search-box input {
    flex: 1;
    border: none;
    padding: 14px 12px;
    font-size: 14px;
    outline: none;
    background: transparent;
    color: #333;
}

.market-search-box input::placeholder {
    color: #aaa;
}

.market-search-box button {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 14px 24px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    letter-spacing: 0.5px;
    transition: background 0.2s;
    margin: 0;
    width: auto;
    border-radius: 0;
}

.market-search-box button:hover {
    background: linear-gradient(135deg, #1b5e20, #2e7d32);
}

.clear-search {
    color: white;
    font-size: 13px;
    display: inline-block;
    margin-top: 10px;
    opacity: 0.8;
    text-decoration: none;
    background: rgba(255,255,255,0.15);
    padding: 4px 14px;
    border-radius: 20px;
    transition: 0.2s;
}

.clear-search:hover {
    opacity: 1;
    background: rgba(255,255,255,0.25);
    color: white;
}
</style>

<?php
$search = trim($_GET['search'] ?? '');
$search_filter = '';
if ($search !== '') {
    $safe = addslashes($search);
    $search_filter = " WHERE (p.product_name LIKE '%$safe%' OR p.type LIKE '%$safe%' OR u.name LIKE '%$safe%' OR u.location LIKE '%$safe%')";
}
?>

<div class="container">
<div class="grid">

<?php

$sql = "SELECT
            p.id,
            p.type,
            p.quantity,
            p.unit,
            p.price,
            p.image,
            p.is_promoted,
            p.product_name,
            p.user_id AS farmer_id,
            u.name AS farmer_name,
            u.location
        FROM products p
        LEFT JOIN users u ON p.user_id = u.id
        $search_filter
        ORDER BY p.is_promoted DESC, p.id DESC";

$result = $conn->query($sql);

/* <i class='fas fa-check-circle'></i> FIX 1: prevent crash if query fails */
if (!$result) {
    die("SQL ERROR: " . $conn->error);
}

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        /* <i class='fas fa-check-circle'></i> FIX 2: safer farmer name */
        $name = $row['farmer_name'] ?? "Unknown Farmer";

        /* <i class='fas fa-check-circle'></i> FIX 3: safer location */
        $location = $row['location'] ?? "No location";

        $is_promo = $row['is_promoted'] ?? 0;
        $card_style = $is_promo ? "style='border: 2px solid #ff8f00; box-shadow: 0 4px 20px rgba(255,143,0,0.25);'" : "";
        echo "<div class='card' $card_style>";

        /* IMAGE */
        $img_path = strpos($row['image'], 'uploads/') === 0 ? $row['image'] : "uploads/" . $row['image'];
        if (!empty($row['image']) && file_exists($img_path)) {
            echo "<img src='{$img_path}'>";
        } else {
            $img = getProductImage($row['type']);
            echo "<img src='images/{$img}'>";
        }

        /* PROMOTED RIBBON */
        if ($is_promo) {
            echo "<div style='background:linear-gradient(135deg,#ff8f00,#ffa726);color:white;text-align:center;font-size:12px;font-weight:700;padding:5px;'><i class='fas fa-star'></i> PROMOTED LISTING</div>";
        }

        $display_name = $row['product_name'] ?: $row['type'];

        echo "
        <div class='body'>
            <div class='title'>{$display_name}</div>
            <div class='farmer'><i class='fas fa-user-tie'></i> {$name}</div>
            <div class='location'><i class='fas fa-map-marker-alt'></i> {$location}</div>
            <div class='price'><i class='fas fa-box'></i> {$row['quantity']} {$row['unit']}</div>
            <div class='price'><i class='fas fa-money-bill-wave'></i> GHS {$row['price']}</div>
            <div class='badge'>" . ($is_promo ? "<i class='fas fa-star'></i> Top Pick" : 'Fresh Farm Produce') . "</div>";
            
            if ($row['quantity'] > 0) {
                echo "
                <form action='buy_product.php' method='POST' style='display:flex; gap:10px;'>
                    <input type='hidden' name='product_id' value='{$row['id']}'>
                    <input type='number' name='quantity' placeholder='Qty' max='{$row['quantity']}' required
                        style='flex:1;padding:8px;margin-top:10px;border-radius:6px;border:1px solid #ccc;'>
                    <button class='buy' style='flex:1.5; margin-top:10px;'><i class='fas fa-shopping-cart'></i> Buy</button>
                </form>";
            } else {
                echo "
                <div style='margin-top:15px; text-align:center;'>
                    <div style='background:#ffebee; color:#c62828; padding:10px; border-radius:6px; font-weight:700;'><i class='fas fa-ban'></i> Out of Stock</div>
                </div>";
            }
            
            // Add Chat Button
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $row['farmer_id']) {
                echo "<a href='chat.php?user_id={$row['farmer_id']}' style='display:block; text-align:center; background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; padding:8px; border-radius:6px; margin-top:10px; text-decoration:none; font-weight:600; font-size:13px; transition:0.2s;' onmouseover=\"this.style.background='#c8e6c9'\" onmouseout=\"this.style.background='#e8f5e9'\"><i class='fas fa-comment-dots'></i> Chat with Farmer</a>";
            }
            
        echo "</div>
        ";
        echo "</div>";
    }

} else {
    echo "<div class='no-data'><i class='fas fa-times-circle'></i> No products found</div>";
}

?>

</div>
</div>

</body>
</html>