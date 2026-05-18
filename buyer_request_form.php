<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';

/* =========================
   HANDLE FORM SUBMISSION
========================= */
if (isset($_POST['submit_request'])) {
    $type = $_POST['product_type'] ?? '';
    $name = $_POST['product_name'] ?? '';
    $min_qty = $_POST['min_quantity'] ?? 1;
    $max_qty = $_POST['max_quantity'] ?? 100;
    $unit = $_POST['unit'] ?? 'kg';
    $min_price = $_POST['min_price'] ?? 0;
    $max_price = $_POST['max_price'] ?? 0;
    $notes = $_POST['notes'] ?? '';

    if ($type && $name && $min_qty && $max_qty) {
        $sql = "INSERT INTO buy_requests (buyer_id, product_type, product_name, min_quantity, max_quantity, unit, min_price, max_price, notes, status)
                VALUES ('$user_id', '$type', '$name', '$min_qty', '$max_qty', '$unit', '$min_price', '$max_price', '$notes', 'open')";

        if ($conn->query($sql)) {
            $msg = "✅ Your buy request has been posted! Farmers can now see what you need.";
        } else {
            $msg = "❌ Error posting request: " . $conn->error;
        }
    } else {
        $msg = "⚠️ Please fill in all required fields.";
    }
}

/* =========================
   HANDLE DELETE REQUEST
========================= */
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM buy_requests WHERE id='$del_id' AND buyer_id='$user_id'");
    header("Location: buyer_request_form.php?msg=" . urlencode("Request removed successfully."));
    exit();
}

if (isset($_GET['msg'])) {
    $msg = urldecode($_GET['msg']);
}

/* =========================
   FETCH MY ACTIVE REQUESTS
========================= */
$my_requests = $conn->query("SELECT * FROM buy_requests WHERE buyer_id='$user_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Post Buy Request | AGRIC DSS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f6; color: #333; margin: 0; padding-bottom: 50px; }

.page-container { max-width: 800px; margin: 30px auto; padding: 0 20px; }

.page-header { margin-bottom: 25px; }
.page-header h2 { font-weight: 800; color: #1565c0; margin: 0; }
.page-header p { color: #666; font-size: 14px; margin: 5px 0 0; }

.alert-msg {
    padding: 14px 20px; border-radius: 10px; margin-bottom: 20px;
    font-weight: 600; font-size: 14px;
    background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9;
}

/* FORM GRID */
.request-form {
    background: white; padding: 30px; border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #eee;
    margin-bottom: 30px;
}

.form-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
}
.full-width { grid-column: span 2; }

.form-group label {
    display: block; font-weight: 600; color: #333;
    margin-bottom: 8px; font-size: 14px;
}
.form-group label span { color: #888; font-weight: 400; font-size: 12px; }

.form-group input, .form-group select, .form-group textarea {
    width: 100%; padding: 12px 14px; border: 1.5px solid #e0e0e0;
    border-radius: 10px; font-size: 14px; background: #fafafa;
    transition: all 0.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: #1565c0; outline: none; background: #fff;
    box-shadow: 0 0 0 3px rgba(21,101,192,0.1);
}
.form-group textarea { resize: vertical; min-height: 80px; }

.row-2 { display: flex; gap: 12px; }
.row-2 > div { flex: 1; }

.btn-submit {
    grid-column: span 2;
    padding: 14px; background: linear-gradient(135deg, #1565c0, #1976d2);
    color: white; border: none; border-radius: 12px; font-size: 16px;
    font-weight: 700; cursor: pointer; transition: 0.3s;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(21,101,192,0.3); }

/* AUTOCOMPLETE DROPDOWN */
.autocomplete-wrapper { position: relative; }
#name-suggestions {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
    background: white; border: 1px solid #ddd; border-top: none;
    border-radius: 0 0 10px 10px; max-height: 200px; overflow-y: auto;
    display: none; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
#name-suggestions div {
    padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f0f0f0;
    font-size: 14px; transition: background 0.15s;
}
#name-suggestions div:hover { background: #e3f2fd; }

/* COMPACT HORIZONTAL LIST */
.section-title {
    font-weight: 800; color: #1565c0; margin: 30px 0 15px;
    font-size: 20px; display: flex; align-items: center; gap: 10px;
}

.request-item {
    display: flex; align-items: center; justify-content: space-between;
    background: white; padding: 18px 20px; border-radius: 12px;
    border: 1px solid #eee; margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: 0.2s;
}
.request-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.06); border-color: #ddd; }

.req-main { display: flex; align-items: center; gap: 15px; flex: 1; }
.req-icon { 
    width: 45px; height: 45px; border-radius: 10px; background: #e8f5e9; 
    color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 20px; 
}
.req-title { font-weight: 700; color: #222; font-size: 16px; margin: 0 0 4px; }
.req-subtitle { color: #777; font-size: 13px; margin: 0; }

.req-details { display: flex; gap: 30px; align-items: center; flex: 2; font-size: 13.5px; color: #555; }
.req-details .detail-block { display: flex; flex-direction: column; gap: 3px; }
.req-details b { color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }

.req-actions { display: flex; align-items: center; gap: 15px; }

.badge-open { background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }

.btn-delete-icon {
    color: #ef5350; background: #ffebee; width: 35px; height: 35px;
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: 0.2s;
}
.btn-delete-icon:hover { background: #d32f2f; color: white; }

.empty-state { text-align: center; padding: 40px; color: #999; font-size: 15px; background: white; border-radius: 16px; border: 1px dashed #ccc; }
.empty-state i { font-size: 40px; margin-bottom: 10px; display: block; color: #ddd; }

@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; gap: 15px; }
    .btn-submit { grid-column: 1; }
    .request-item { flex-direction: column; align-items: flex-start; gap: 15px; }
    .req-details { flex-direction: column; gap: 10px; align-items: flex-start; }
    .req-actions { width: 100%; justify-content: space-between; border-top: 1px solid #eee; padding-top: 15px; }
}
</style>

</head>
<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="page-container">

    <div class="page-header">
        <h2><i class="fas fa-bullhorn"></i> Post a Buy Request</h2>
        <p>Tell farmers exactly what you're looking for. They'll match their products to your needs.</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert-msg"><?= $msg ?></div>
    <?php endif; ?>

    <!-- BUY REQUEST FORM -->
    <div class="request-form">
        <form method="POST" class="form-grid">

            <!-- Product Type -->
            <div class="form-group">
                <label>Product Category <span>*</span></label>
                <select name="product_type" id="product-type" required>
                    <option value="">-- Select Category --</option>
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
            </div>

            <!-- Product Name -->
            <div class="form-group">
                <label>Specific Product Name <span>*</span></label>
                <div class="autocomplete-wrapper">
                    <input type="text" name="product_name" id="product-name" placeholder="E.g. Onions, Maize..." autocomplete="off" required>
                    <div id="name-suggestions"></div>
                </div>
            </div>

            <!-- Quantity Range -->
            <div class="form-group">
                <label>Target Quantity <span>(Min – Max)</span></label>
                <div class="row-2">
                    <input type="number" name="min_quantity" placeholder="Min" min="1" required>
                    <input type="number" name="max_quantity" placeholder="Max" min="1" required>
                </div>
            </div>

            <!-- Unit & Price -->
            <div class="form-group">
                <label>Unit & Price Range <span>(Optional budget)</span></label>
                <div class="row-2" style="gap:5px;">
                    <select name="unit" required style="flex: 0.6;">
                        <option value="kg">kg</option>
                        <option value="bags">bags</option>
                        <option value="tons">tons</option>
                        <option value="crates">crates</option>
                        <option value="boxes">boxes</option>
                        <option value="pieces">pieces</option>
                    </select>
                    <input type="number" name="min_price" placeholder="Min GHS" step="0.01" min="0">
                    <input type="number" name="max_price" placeholder="Max GHS" step="0.01" min="0">
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group full-width">
                <label>Special Requirements <span>(Optional notes for the farmer)</span></label>
                <textarea name="notes" placeholder="E.g. Organic only, needs to be fresh harvest, pick-up from farm..."></textarea>
            </div>

            <button type="submit" name="submit_request" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Publish Request to Farmers
            </button>

        </form>
    </div>

    <!-- MY ACTIVE REQUESTS -->
    <h3 class="section-title"><i class="fas fa-list-check"></i> My Buy Requests</h3>

    <?php if ($my_requests && $my_requests->num_rows > 0): ?>
        <div style="display:flex; flex-direction:column;">
            <?php while ($req = $my_requests->fetch_assoc()): ?>
                <div class="request-item">
                    <!-- Main Info -->
                    <div class="req-main">
                        <div class="req-icon"><i class="fas fa-basket-shopping"></i></div>
                        <div>
                            <p class="req-title"><?= htmlspecialchars($req['product_name']) ?></p>
                            <p class="req-subtitle"><?= htmlspecialchars($req['product_type']) ?></p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="req-details">
                        <div class="detail-block">
                            <b>Quantity</b>
                            <span><?= $req['min_quantity'] ?> – <?= $req['max_quantity'] ?> <?= htmlspecialchars($req['unit']) ?></span>
                        </div>
                        <div class="detail-block">
                            <b>Price Range (GHS)</b>
                            <span>
                                <?php if ($req['min_price'] > 0 || $req['max_price'] > 0): ?>
                                    <?= number_format($req['min_price'], 2) ?> – <?= number_format($req['max_price'], 2) ?>
                                <?php else: ?>
                                    Negotiable
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Actions / Status -->
                    <div class="req-actions">
                        <span class="badge-open"><?= ucfirst($req['status']) ?></span>
                        <a href="buyer_request_form.php?delete=<?= $req['id'] ?>" class="btn-delete-icon" title="Remove Request" onclick="return confirm('Remove this request?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            You haven't posted any buy requests yet.
        </div>
    <?php endif; ?>

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

const typeSelect = document.getElementById('product-type');
const nameInput = document.getElementById('product-name');
const suggestionsBox = document.getElementById('name-suggestions');

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
    if (names.length === 0) { suggestionsBox.style.display = 'none'; return; }

    names.sort().forEach(function(name) {
        const div = document.createElement('div');
        div.textContent = name;
        div.addEventListener('click', function(){
            nameInput.value = name;
            suggestionsBox.style.display = 'none';
        });
        suggestionsBox.appendChild(div);
    });

    suggestionsBox.style.display = 'block';
}

nameInput.addEventListener('focus', function(){ showSuggestions(this.value); });
nameInput.addEventListener('input', function(){ showSuggestions(this.value); });

typeSelect.addEventListener('change', function(){
    nameInput.value = '';
    nameInput.placeholder = this.value
        ? 'Start typing ' + this.value + ' name...'
        : 'Start typing product name...';
    if (document.activeElement === nameInput) { showSuggestions(''); }
});

document.addEventListener('click', function(e){
    if (!nameInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
        suggestionsBox.style.display = 'none';
    }
});
</script>

</body>
</html>
