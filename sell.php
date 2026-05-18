<?php
session_start();
include 'db.php';
include 'back_button.php';

$product_id = $_GET['product_id'] ?? 0;
$show_buyers = isset($_GET['show_buyers']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Sell Products</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #eef2f7, #dfe9f3);
    padding: 20px;
}

h2 {
    text-align: center;
    color: #1b5e20;
}

.sell-form {
    background: white;
    padding: 25px;
    max-width: 650px;
    margin: auto;
    border-radius: 15px;
}

.sell-form input {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    box-sizing: border-box;
}

.sell-form button {
    width: 100%;
    padding: 12px;
    background: #2e7d32;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.buyer-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
}

.buyer-card {
    background: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #eaeaea;
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    align-items: center;
    justify-content: space-between;
    transition: 0.2s;
}
.buyer-card:hover { border-color: #c8e6c9; box-shadow: 0 6px 20px rgba(46,125,50,0.08); }

.buyer-info { flex: 1.5; min-width: 200px; }
.buyer-info h3 { margin: 0 0 5px; font-size: 18px; color: #222; }
.buyer-info p { margin: 0 0 5px; color: #666; font-size: 14px; }

.buyer-req-box { 
    flex: 2; min-width: 260px; background: #f9fbfa; padding: 15px; 
    border-radius: 10px; font-size: 13.5px; border: 1px dashed #cfd8dc; 
}
.buyer-req-box p { margin: 0 0 6px; color: #444; }
.buyer-req-box p:last-child { margin: 0; }

.buyer-action-box { flex: 1.5; min-width: 220px; display: flex; flex-direction: column; gap: 12px; }

.action-form { display: flex; gap: 8px; }
.action-form input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; margin: 0; }
.action-form button { padding: 10px 15px; background: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: auto; margin: 0; transition: 0.2s; }
.action-form button:hover { background: #1b5e20; }

.contact-links { display: flex; gap: 10px; flex-wrap: wrap; }
.contact-links a { 
    flex: 1; text-align: center; text-decoration: none; font-size: 13px; font-weight: 600; 
    padding: 8px 10px; border-radius: 6px; transition: 0.2s; white-space: nowrap;
}
.btn-call { background: #e8f5e9; color: #2e7d32; }
.btn-call:hover { background: #c8e6c9; }
.btn-wa { background: #e8f5e9; color: #2e7d32; }
.btn-wa:hover { background: #c8e6c9; }
.btn-chat { background: #e3f2fd; color: #1565c0; }
.btn-chat:hover { background: #bbdefb; }

.trusted { color: #2e7d32; font-weight: 700; background: #e8f5e9; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
.good { color: #f57f17; font-weight: 700; background: #fff8e1; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
.risky { color: #c62828; font-weight: 700; background: #ffebee; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
.new { color: #1565c0; font-weight: 700; background: #e3f2fd; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
</style>

</head>
<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<h2><i class='fas fa-wheat-awn'></i> Agricultural Marketplace</h2>

<form action="sell_product.php" method="POST" enctype="multipart/form-data" class="sell-form">

    <!-- PRODUCT TYPE (Category) -->
    <label style="font-weight:600; color:#333; margin-bottom:4px; display:block;">Product Type</label>
    <select name="type" id="product-type" style="width:100%; padding:12px; margin:8px 0; border:1px solid #ccc; border-radius:8px; font-size:15px; background:#fafafa;" required>
        <option value="">-- Select Product Type --</option>
        <optgroup label="🌾 Crops & Grains">
            <option value="Grains & Cereals">Grains & Cereals</option>
            <option value="Tubers & Roots">Tubers & Roots</option>
            <option value="Legumes & Nuts">Legumes & Nuts</option>
        </optgroup>
        <optgroup label="🥬 Vegetables">
            <option value="Vegetables">Vegetables</option>
        </optgroup>
        <optgroup label="🍎 Fruits">
            <option value="Fruits">Fruits</option>
        </optgroup>
        <optgroup label="🐄 Livestock & Animals">
            <option value="Cattle">Cattle</option>
            <option value="Poultry">Poultry</option>
            <option value="Goats & Sheep">Goats & Sheep</option>
            <option value="Pigs">Pigs</option>
            <option value="Other Animals">Other Animals</option>
        </optgroup>
        <optgroup label="🐟 Seafood & Fish">
            <option value="Fish">Fish</option>
            <option value="Crabs & Shellfish">Crabs & Shellfish</option>
            <option value="Other Seafood">Other Seafood</option>
        </optgroup>
        <optgroup label="🥛 Dairy & Processed">
            <option value="Dairy Products">Dairy Products</option>
            <option value="Processed Foods">Processed Foods</option>
            <option value="Oils & Fats">Oils & Fats</option>
        </optgroup>
        <optgroup label="🌿 Others">
            <option value="Spices & Herbs">Spices & Herbs</option>
            <option value="Animal Feed">Animal Feed</option>
            <option value="Seeds & Seedlings">Seeds & Seedlings</option>
            <option value="Other">Other</option>
        </optgroup>
    </select>

    <!-- PRODUCT NAME (Autocomplete) -->
    <label style="font-weight:600; color:#333; margin-bottom:4px; display:block; margin-top:8px;">Product Name</label>
    <div style="position:relative;">
        <input type="text" name="product_name" id="product-name" placeholder="Start typing product name..." autocomplete="off" required>
        <div id="name-suggestions" style="
            position:absolute; top:100%; left:0; right:0; z-index:100;
            background:white; border:1px solid #ddd; border-top:none; border-radius:0 0 10px 10px;
            max-height:220px; overflow-y:auto; display:none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        "></div>
    </div>

    <!-- QUANTITY & UNIT -->
    <label style="font-weight:600; color:#333; margin-bottom:4px; display:block; margin-top:8px;">Quantity & Unit</label>
    <div style="display:flex; gap:10px; margin:8px 0;">
        <input type="number" name="quantity" placeholder="e.g. 50" style="flex:1; margin:0;" required>
        <select name="unit" style="flex:1; padding:12px; margin:0; border:1px solid #ccc; border-radius:8px; font-size:15px; background:#fafafa;" required>
            <option value="">-- Select Unit --</option>
            <option value="kg">kg</option>
            <option value="bags">bags</option>
            <option value="tons">tons</option>
            <option value="crates">crates</option>
            <option value="boxes">boxes</option>
            <option value="bunches">bunches</option>
            <option value="liters">liters</option>
            <option value="pieces">pieces</option>
        </select>
    </div>

    <!-- WEIGHT PER UNIT (Optional) -->
    <label style="font-weight:600; color:#333; margin-bottom:4px; display:block; margin-top:8px;">Weight per Unit <span style="color:#777; font-weight:normal; font-size:13px;">(Optional, e.g. 50 kg per bag)</span></label>
    <div style="display:flex; gap:10px; margin:8px 0;">
        <input type="number" name="unit_weight" placeholder="e.g. 50" step="0.01" style="flex:1; margin:0;">
        <select name="weight_unit" style="flex:1; padding:12px; margin:0; border:1px solid #ccc; border-radius:8px; font-size:15px; background:#fafafa;">
            <option value="kg">kg</option>
            <option value="g">grams</option>
            <option value="lbs">lbs</option>
        </select>
    </div>

    <!-- <i class='fas fa-check-circle'></i> NEW PRICE FIELD -->
    <label style="font-weight:600; color:#333; margin-bottom:4px; display:block; margin-top:8px;">Price (GHS)</label>
    <input type="number" name="price" placeholder="Price (GHS)" step="0.01" required>

    <!-- IMAGE -->
    <label style="font-weight:600; color:#333; margin-bottom:4px; display:block; margin-top:8px;">Product Image</label>
    <input type="file" name="image" required>

    <button type="submit">Request to Sell Products</button>

</form>

<hr>

<?php
/* =========================
   SHOW MATCHING BUYER REQUESTS
========================= */
if ($show_buyers && $product_id != 0) {

    // Get the product details we just uploaded
    $prod_res = $conn->query("SELECT * FROM products WHERE id='$product_id'");
    $product = $prod_res ? $prod_res->fetch_assoc() : null;

    if ($product) {
        $p_type = $product['type'] ?? '';
        $p_name = $product['product_name'] ?? '';
        $p_qty = $product['quantity'] ?? 0;
        $p_price = $product['price'] ?? 0;

        echo "<h3 style='color:#1b5e20; text-align:center; margin-top:20px;'><i class='fas fa-handshake'></i> Matching Buyer Requests</h3>";
        echo "<p style='text-align:center; color:#666; font-size:14px; margin-bottom:20px;'>Buyers looking for <b>" . htmlspecialchars($p_name) . "</b> (" . htmlspecialchars($p_type) . ")</p>";

        // MATCHING requests: same product type or name
        $match_sql = "SELECT br.*, u.name AS buyer_name, u.location, u.phone 
                      FROM buy_requests br 
                      JOIN users u ON br.buyer_id = u.id 
                      WHERE br.status='open' 
                      AND (LOWER(br.product_type) = LOWER('$p_type') OR LOWER(br.product_name) = LOWER('$p_name'))
                      ORDER BY br.created_at DESC";
        $match_res = $conn->query($match_sql);

        $matched_ids = [];

        if ($match_res && $match_res->num_rows > 0) {
            echo "<div class='buyer-container'>";
            while ($req = $match_res->fetch_assoc()) {
                $matched_ids[] = $req['id'];
                $phone = preg_replace('/[^0-9]/', '', $req['phone']);

                // Check if farmer's quantity is in buyer's range
                $in_range = ($p_qty >= $req['min_quantity'] && $p_qty <= $req['max_quantity']);
                $price_ok = (($req['min_price'] <= 0 && $req['max_price'] <= 0) || 
                             ($p_price >= $req['min_price'] && ($req['max_price'] <= 0 || $p_price <= $req['max_price'])));
                $match_score = ($in_range && $price_ok) ? 'perfect' : (($in_range || $price_ok) ? 'partial' : 'low');

                if ($match_score == 'perfect') {
                    $match_label = "<span style='background:#e8f5e9; color:#2e7d32; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;'>✅ Perfect Match</span>";
                } elseif ($match_score == 'partial') {
                    $match_label = "<span style='background:#fff8e1; color:#f57f17; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;'>⚡ Partial Match</span>";
                } else {
                    $match_label = "<span style='background:#fce4ec; color:#c62828; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;'>🔍 Type Match</span>";
                }

                // Get reputation
                $rep = $conn->query("SELECT * FROM buyer_reputation WHERE buyer_id='{$req['buyer_id']}'");
                $r = ['on_time_payments' => 0, 'delayed_payments' => 0];
                if ($rep && $rep->num_rows > 0) { $r = $rep->fetch_assoc(); }
                $on = $r['on_time_payments'];
                $delayed = $r['delayed_payments'];
                $total_pay = $on + $delayed;

                if ($total_pay == 0) { $trust = "New 🆕"; $tclass = "new"; }
                elseif ($on >= 10 && $delayed == 0) { $trust = "Trusted ⭐"; $tclass = "trusted"; }
                elseif ($on >= 7 && $delayed <= 3) { $trust = "Good 👍"; $tclass = "good"; }
                elseif ($delayed > $on) { $trust = "Risky ⚠️"; $tclass = "risky"; }
                else { $trust = "Average"; $tclass = "good"; }
?>
                <div class="buyer-card" style="border-left: 4px solid #2e7d32;">
                    
                    <!-- Buyer Info -->
                    <div class="buyer-info">
                        <h3><?= htmlspecialchars($req['buyer_name']) ?></h3>
                        <p><i class='fas fa-map-marker-alt'></i> <?= htmlspecialchars($req['location']) ?></p>
                        <p style="margin-top:8px;">Trust Score: <span class="<?= $tclass ?>"><?= $trust ?></span></p>
                        <div style="margin-top:8px;"><?= $match_label ?></div>
                    </div>

                    <!-- Request Details -->
                    <div class="buyer-req-box">
                        <p><i class='fas fa-tag' style="color:#2e7d32; width:16px;"></i> <b>Wants:</b> <?= htmlspecialchars($req['product_name']) ?> (<?= htmlspecialchars($req['product_type']) ?>)</p>
                        <p><i class='fas fa-cubes' style="color:#1565c0; width:16px;"></i> <b>Quantity:</b> <?= $req['min_quantity'] ?> – <?= $req['max_quantity'] ?> <?= htmlspecialchars($req['unit']) ?></p>
                        <?php if ($req['min_price'] > 0 || $req['max_price'] > 0): ?>
                            <p><i class='fas fa-money-bill' style="color:#f57f17; width:16px;"></i> <b>Target Price:</b> GHS <?= number_format($req['min_price'], 2) ?> – <?= number_format($req['max_price'], 2) ?>/<?= htmlspecialchars($req['unit']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($req['notes'])): ?>
                            <p><i class='fas fa-sticky-note' style="color:#7b1fa2; width:16px;"></i> <b>Notes:</b> <?= htmlspecialchars($req['notes']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="buyer-action-box">
                        <form method="POST" action="process_sale.php" class="action-form">
                            <input type="hidden" name="buyer_id" value="<?= $req['buyer_id'] ?>">
                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                            <input type="number" name="quantity" placeholder="Qty" 
                                   min="<?= $req['min_quantity'] ?>" max="<?= $req['max_quantity'] ?>" required>
                            <button><i class='fas fa-check'></i> Sell</button>
                        </form>

                        <div class="contact-links">
                            <a href="tel:<?= $req['phone'] ?>" class="btn-call"><i class='fas fa-phone-alt'></i> Call</a>
                            <a href="https://wa.me/<?= $phone ?>" target="_blank" class="btn-wa"><i class='fas fa-comment-dots'></i> WhatsApp</a>
                            <a href="chat.php?user_id=<?= $req['buyer_id'] ?>" class="btn-chat"><i class='fas fa-comment'></i> Chat</a>
                        </div>
                    </div>

                </div>
<?php
            }
            echo "</div>";

        } else {
            echo "<p style='text-align:center; color:#999; padding:20px;'><i class='fas fa-info-circle'></i> No buyer requests match your product right now. Check back later or browse all open requests below.</p>";
        }
    }
}
?>

<!-- =========================
   ALL OPEN BUYER REQUESTS
   (Always visible so farmer can browse)
========================= -->
<div style="max-width:900px; margin:30px auto; padding:0 15px;">
    <h3 style="color:#1b5e20; text-align:center;"><i class='fas fa-bullhorn'></i> All Open Buyer Requests</h3>
    <p style="text-align:center; color:#666; font-size:14px; margin-bottom:20px;">See what buyers are looking for and match your products.</p>

    <?php
    $all_sql = "SELECT br.*, u.name AS buyer_name, u.location, u.phone 
                FROM buy_requests br 
                JOIN users u ON br.buyer_id = u.id 
                WHERE br.status='open' 
                ORDER BY br.created_at DESC";
    $all_res = $conn->query($all_sql);

    if ($all_res && $all_res->num_rows > 0) {
        echo "<div class='buyer-container' id='all-requests-container'>";
        while ($req = $all_res->fetch_assoc()) {
            $phone = preg_replace('/[^0-9]/', '', $req['phone']);
    ?>
            <div class="buyer-card all-request-card" data-type="<?= htmlspecialchars(strtolower($req['product_type'])) ?>" data-name="<?= htmlspecialchars(strtolower($req['product_name'])) ?>">
                
                <!-- Buyer Info -->
                <div class="buyer-info">
                    <h3><?= htmlspecialchars($req['buyer_name']) ?></h3>
                    <p><i class='fas fa-map-marker-alt'></i> <?= htmlspecialchars($req['location']) ?></p>
                    <p style="margin-top:8px; font-size:12px; color:#999;"><i class='fas fa-clock'></i> <?= date('M j, Y', strtotime($req['created_at'])) ?></p>
                </div>

                <!-- Request Details -->
                <div class="buyer-req-box">
                    <p><i class='fas fa-tag' style='color:#2e7d32; width:16px;'></i> <b>Product:</b> <?= htmlspecialchars($req['product_name']) ?> (<?= htmlspecialchars($req['product_type']) ?>)</p>
                    <p><i class='fas fa-cubes' style='color:#ef6c00; width:16px;'></i> <b>Qty:</b> <?= $req['min_quantity'] ?> – <?= $req['max_quantity'] ?> <?= htmlspecialchars($req['unit']) ?></p>
                    <?php if ($req['min_price'] > 0 || $req['max_price'] > 0): ?>
                        <p><i class='fas fa-money-bill' style='color:#2e7d32; width:16px;'></i> <b>Budget:</b> GHS <?= number_format($req['min_price'], 2) ?> – <?= number_format($req['max_price'], 2) ?>/<?= htmlspecialchars($req['unit']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($req['notes'])): ?>
                        <p><i class='fas fa-sticky-note' style='color:#7b1fa2; width:16px;'></i> <?= htmlspecialchars($req['notes']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="buyer-action-box" style="justify-content:center;">
                    <p style="font-size:13px; color:#666; margin:0 0 10px; text-align:center;">Contact Buyer</p>
                    <div class="contact-links" style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <a href="tel:<?= $req['phone'] ?>" class="btn-call" style="grid-column:1/3;"><i class='fas fa-phone-alt'></i> Call Directly</a>
                        <a href="https://wa.me/<?= $phone ?>" target="_blank" class="btn-wa"><i class='fas fa-comment-dots'></i> WhatsApp</a>
                        <a href="chat.php?user_id=<?= $req['buyer_id'] ?>" class="btn-chat"><i class='fas fa-comment'></i> Chat</a>
                    </div>
                </div>

            </div>
    <?php
        }
        echo "</div>";
    } else {
        echo "<p style='text-align:center; color:#999; padding:30px;'><i class='fas fa-inbox' style='font-size:30px; display:block; margin-bottom:10px;'></i> No open buyer requests yet.</p>";
    }
    ?>
</div>

<script>
/* =============================================
   PRODUCT NAME AUTOCOMPLETE BY TYPE
============================================= */
const productNames = {
    "Grains & Cereals": ["Maize","Rice","Sorghum","Millet","Wheat","Barley","Oats","Corn"],
    "Tubers & Roots": ["Yam","Cassava","Sweet Potato","Cocoyam","Irish Potato","Ginger","Turmeric"],
    "Legumes & Nuts": ["Beans","Groundnuts","Soya Beans","Cowpea","Bambara Beans","Lentils","Cashew Nuts","Tiger Nuts","Shea Nuts"],
    "Vegetables": ["Tomatoes","Onions","Peppers","Okra","Cabbage","Carrot","Lettuce","Cucumber","Garden Eggs","Green Beans","Spinach","Spring Onions","Kontomire","Ayoyo","Alefu"],
    "Fruits": ["Pineapple","Mango","Banana","Plantain","Orange","Pawpaw","Watermelon","Coconut","Avocado","Guava","Lime","Lemon","Apple","Passion Fruit","Soursop"],
    "Cattle": ["Cow","Bull","Calf","Ox"],
    "Poultry": ["Chicken","Turkey","Duck","Guinea Fowl","Quail","Eggs"],
    "Goats & Sheep": ["Goat","Sheep","Lamb","Ram"],
    "Pigs": ["Pig","Piglet","Pork"],
    "Other Animals": ["Rabbit","Grasscutter","Snail","Donkey","Horse"],
    "Fish": ["Tilapia","Catfish","Salmon","Mackerel","Tuna","Herring","Mudfish","Barracuda","Red Snapper"],
    "Crabs & Shellfish": ["Crab","Lobster","Shrimp","Prawn","Oyster","Clam"],
    "Other Seafood": ["Squid","Octopus","Dried Fish","Smoked Fish","Stockfish"],
    "Dairy Products": ["Fresh Milk","Yogurt","Cheese","Butter","Wagashi (Local Cheese)"],
    "Processed Foods": ["Gari","Kokonte","Kenkey","Shea Butter","Dawadawa","Flour","Tombrown"],
    "Oils & Fats": ["Palm Oil","Coconut Oil","Groundnut Oil","Shea Butter Oil","Soya Oil"],
    "Spices & Herbs": ["Pepper","Ginger","Garlic","Dawadawa","Anise","Cloves","Prekese","Negro Pepper"],
    "Animal Feed": ["Maize Bran","Wheat Bran","Soya Meal","Fish Meal","Hay","Silage"],
    "Seeds & Seedlings": ["Maize Seeds","Rice Seeds","Tomato Seedlings","Pepper Seedlings","Cocoa Seedlings","Oil Palm Seedlings"],
    "Other": []
};

<?php
// Load any existing product names from the database to supplement the predefined list
$existing_products = [];
$pn_sql = "SELECT DISTINCT product_name, type FROM products WHERE product_name IS NOT NULL AND product_name != ''";
$pn_res = $conn->query($pn_sql);
if($pn_res) {
    while($pn = $pn_res->fetch_assoc()) {
        $existing_products[] = ['name' => $pn['product_name'], 'type' => $pn['type']];
    }
}
?>

// Merge database product names into the JS data
const dbProducts = <?= json_encode($existing_products) ?>;
dbProducts.forEach(function(p) {
    if (p.type && productNames[p.type]) {
        if (!productNames[p.type].map(n => n.toLowerCase()).includes(p.name.toLowerCase())) {
            productNames[p.type].push(p.name);
        }
    }
});

const typeSelect = document.getElementById('product-type');
const nameInput = document.getElementById('product-name');
const suggestionsBox = document.getElementById('name-suggestions');

// Get all names (flat) for when no type is selected
function getAllNames() {
    let all = [];
    Object.values(productNames).forEach(arr => { all = all.concat(arr); });
    return [...new Set(all)];
}

function showSuggestions(filter) {
    const type = typeSelect.value;
    let names = type ? (productNames[type] || []) : getAllNames();

    if (filter) {
        const f = filter.toLowerCase();
        names = names.filter(n => n.toLowerCase().includes(f));
    }

    suggestionsBox.innerHTML = '';

    if (names.length === 0) {
        suggestionsBox.style.display = 'none';
        return;
    }

    names.sort().forEach(function(name) {
        const div = document.createElement('div');
        div.textContent = name;
        div.style.cssText = 'padding:10px 14px; cursor:pointer; border-bottom:1px solid #f0f0f0; font-size:14px; transition: background 0.15s;';
        div.addEventListener('mouseenter', function(){ this.style.background='#e8f5e9'; });
        div.addEventListener('mouseleave', function(){ this.style.background='white'; });
        div.addEventListener('click', function(){
            nameInput.value = name;
            suggestionsBox.style.display = 'none';
            filterBuyerCards();
        });
        suggestionsBox.appendChild(div);
    });

    suggestionsBox.style.display = 'block';
}

nameInput.addEventListener('focus', function(){
    showSuggestions(this.value);
});

nameInput.addEventListener('input', function(){
    showSuggestions(this.value);
    filterBuyerCards();
});

// When type changes, clear name and re-trigger suggestions
typeSelect.addEventListener('change', function(){
    nameInput.value = '';
    nameInput.placeholder = this.value 
        ? 'Start typing ' + this.value + ' name...' 
        : 'Start typing product name...';
    if (document.activeElement === nameInput) {
        showSuggestions('');
    }
    filterBuyerCards();
});

// Close suggestions when clicking outside
document.addEventListener('click', function(e){
    if (!nameInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
        suggestionsBox.style.display = 'none';
    }
});

/* =============================================
   REAL-TIME BUYER CARD FILTERING
============================================= */
function filterBuyerCards() {
    const selectedType = typeSelect.value.toLowerCase();
    const typedName = nameInput.value.toLowerCase();
    const allCards = document.querySelectorAll('.all-request-card');
    
    if (allCards.length === 0) return;

    let visibleCount = 0;

    allCards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        const cardName = card.getAttribute('data-name');
        
        let show = true;
        
        // Filter by Type
        if (selectedType && cardType !== selectedType) {
            show = false;
        }
        
        // Filter by Name
        if (typedName && !cardName.includes(typedName)) {
            show = false;
        }

        if (show) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Optional: Show a message if no buyers match the filter
    let noMatchMsg = document.getElementById('no-match-msg');
    if (visibleCount === 0) {
        if (!noMatchMsg) {
            noMatchMsg = document.createElement('p');
            noMatchMsg.id = 'no-match-msg';
            noMatchMsg.style.cssText = 'text-align:center; color:#999; padding:20px; width:100%;';
            noMatchMsg.innerHTML = "<i class='fas fa-search'></i> No buyers are currently looking for exactly this.";
            document.getElementById('all-requests-container').appendChild(noMatchMsg);
        }
        noMatchMsg.style.display = 'block';
    } else if (noMatchMsg) {
        noMatchMsg.style.display = 'none';
    }
}
</script>

</body>
</html>