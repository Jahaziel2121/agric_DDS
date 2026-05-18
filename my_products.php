<?php
session_start();
include 'db.php';
include 'back_button.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != "farmer"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';

/* =========================
   ADD PRODUCT
========================= */
if(isset($_POST['add_product'])){
    $type = $conn->real_escape_string($_POST['type'] ?? '');
    $name = $conn->real_escape_string($_POST['product_name'] ?? '');
    $qty = (float)($_POST['quantity'] ?? 0);
    $unit = $conn->real_escape_string($_POST['unit'] ?? 'kg');
    $unit_weight = (float)($_POST['unit_weight'] ?? 0);
    $weight_unit = $conn->real_escape_string($_POST['weight_unit'] ?? 'kg');
    $price = (float)($_POST['price'] ?? 0);

    // Default image if not uploaded
    $image_name = 'default_product.jpg';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/';
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $image_name = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    if ($name && $qty && $price) {
        $sql = "INSERT INTO products (user_id, product_name, type, quantity, unit, unit_weight, weight_unit, price, image, description)
                VALUES ('$user_id', '$name', '$type', '$qty', '$unit', '$unit_weight', '$weight_unit', '$price', '$image_name', '')";
        
        if ($conn->query($sql)) {
            $msg = "<div class='alert alert-success'>✅ Product added to your inventory successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Error adding product: " . $conn->error . "</div>";
        }
    }
}

/* =========================
   SELL PRODUCT (REDUCE QTY)
========================= */
if(isset($_POST['sell'])){
    $product_id = $_POST['product_id'];
    $sell_qty = $_POST['sell_qty'];

    $res = $conn->query("SELECT * FROM products WHERE id='$product_id' AND user_id='$user_id'");
    $row = $res->fetch_assoc();

    if($row && $row['quantity'] >= $sell_qty){
        $new_qty = $row['quantity'] - $sell_qty;
        $conn->query("UPDATE products SET quantity='$new_qty' WHERE id='$product_id'");
        $msg = "<div class='alert alert-success'>✅ Sold successfully! Inventory updated.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Not enough stock!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Inventory | AGRIC DSS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f6; color: #333; padding-bottom: 50px; }

.page-title { font-weight: 800; color: #1b5e20; margin: 25px 0; display: flex; align-items: center; gap: 10px; }

/* Custom Form Card */
.form-card {
    background: white; border-radius: 16px; padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #eee; margin-bottom: 30px;
}
.form-card h4 { font-weight: 700; margin-bottom: 20px; color: #222; }
.form-label { font-weight: 600; font-size: 14px; color: #444; margin-bottom: 6px; }
.form-control, .form-select { border-radius: 8px; padding: 10px 14px; font-size: 14px; background: #fafafa; border-color: #ddd; }
.form-control:focus, .form-select:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); background: white; }

.btn-primary-custom {
    background: linear-gradient(135deg, #2e7d32, #4caf50); border: none;
    color: white; padding: 12px 20px; border-radius: 10px; font-weight: 700; width: 100%; transition: 0.3s;
}
.btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(46,125,50,0.3); color: white; }

/* Product Table */
.table-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #eee; }
.table th { background: #f8f9fa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
.table td { vertical-align: middle; font-size: 14.5px; }
.prod-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; }

/* Autocomplete */
.autocomplete-wrapper { position: relative; }
#name-suggestions {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
    background: white; border: 1px solid #ddd; border-top: none;
    border-radius: 0 0 10px 10px; max-height: 200px; overflow-y: auto;
    display: none; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
#name-suggestions div { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
#name-suggestions div:hover { background: #e8f5e9; }
</style>
</head>

<body>
<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container">
    <h2 class="page-title"><i class='fas fa-warehouse'></i> My Inventory Manager</h2>

    <?= $msg ?>

    <div class="row">
        <!-- ADD PRODUCT FORM -->
        <div class="col-lg-5">
            <div class="form-card">
                <h4><i class="fas fa-plus-circle"></i> Add New Product</h4>
                
                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="type" id="product-type" class="form-select" required>
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

                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <div class="autocomplete-wrapper">
                            <input type="text" name="product_name" id="product-name" class="form-control" placeholder="E.g. Onions, Maize" autocomplete="off" required>
                            <div id="name-suggestions"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" placeholder="E.g. 50" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Unit *</label>
                            <select name="unit" class="form-select" required>
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
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Weight/Unit <span class="text-muted fw-normal">(Opt)</span></label>
                            <input type="number" name="unit_weight" class="form-control" placeholder="E.g. 50" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">W. Unit</label>
                            <select name="weight_unit" class="form-select">
                                <option value="kg">kg</option>
                                <option value="g">grams</option>
                                <option value="lbs">lbs</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price (GHS) *</label>
                        <input type="number" name="price" class="form-control" placeholder="Price per unit" step="0.01" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Product Image <span class="text-muted fw-normal">(Opt)</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" name="add_product" class="btn-primary-custom"><i class="fas fa-save"></i> Add to Inventory</button>
                </form>
            </div>
        </div>

        <!-- CURRENT INVENTORY -->
        <div class="col-lg-7">
            <div class="table-card table-responsive">
                <h4 style="font-weight:700; margin-bottom:15px; color:#222;"><i class="fas fa-boxes"></i> Current Stock</h4>
                
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Details</th>
                            <th>Stock</th>
                            <th>Action (Sell Direct)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM products WHERE user_id='$user_id' ORDER BY id DESC");
                        if($result && $result->num_rows > 0):
                            while($row = $result->fetch_assoc()):
                                $img_path = strpos($row['image'], 'uploads/') === 0 ? $row['image'] : "uploads/" . $row['image'];
                                $img = (!empty($row['image']) && file_exists($img_path)) ? $img_path : "default_product.jpg";
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= htmlspecialchars($img) ?>" class="prod-img" onerror="this.src='default_product.jpg'">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($row['product_name']) ?></div>
                                        <div class="text-muted" style="font-size:12px;"><?= htmlspecialchars($row['type']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><b>GHS <?= number_format($row['price'] ?? 0, 2) ?></b> / <?= htmlspecialchars($row['unit'] ?? 'unit') ?></div>
                                <?php if(!empty($row['unit_weight']) && $row['unit_weight'] > 0): ?>
                                    <div class="text-muted" style="font-size:12px;"><?= $row['unit_weight'] . $row['weight_unit'] ?> per <?= htmlspecialchars($row['unit']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $row['quantity'] > 0 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                    <?= $row['quantity'] ?> <?= htmlspecialchars($row['unit'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="number" name="sell_qty" class="form-control form-control-sm" style="width:70px;" placeholder="Qty" min="1" max="<?= $row['quantity'] ?>" required>
                                    <button name="sell" class="btn btn-warning btn-sm fw-bold"><i class="fas fa-minus"></i> Sell</button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Your inventory is empty. Add a product to get started!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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