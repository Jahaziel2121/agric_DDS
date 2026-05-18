<?php
session_start();
include 'db.php';

/* =========================
   HANDLE SELL ACTION
========================= */
if(isset($_POST['sell'])){

    $user = $_SESSION['user_id'];
    $crop = $_POST['crop_id'];
    $market = $_POST['market_id'];
    $qty = $_POST['quantity'];
    $price = $_POST['price'];

    // Get crop name
    $crop_data = $conn->query("SELECT crop_name FROM crops WHERE id=$crop");
    $c = $crop_data->fetch_assoc();

    // Get market name
    $market_data = $conn->query("SELECT market_name FROM markets WHERE id=$market");
    $m = $market_data->fetch_assoc();

    // Calculate total
    $total = $qty * $price;

    // Insert into database
    $conn->query("INSERT INTO farmer_products 
    (user_id, crop_id, market_id, quantity, expected_price, status)
    VALUES ('$user','$crop','$market','$qty','$price','Sold')");

    // Notification message
    $msg = "You sold ".$qty." bag(s) of ".$c['crop_name']." at ".$m['market_name'].".<br>
    Price per bag: GHS ".$price."<br>
    Total Amount: GHS ".$total;

    header("Location: add_product.php?msg=".urlencode($msg));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Sell Product</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<script>
// Auto-fill form
function selectPrice(crop_id, market_id, price){
    document.getElementById("crop").value = crop_id;
    document.getElementById("market").value = market_id;
    document.getElementById("price").value = price;
}
</script>

</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container mt-4">

<h3>Sell Your Product</h3>

<!-- NOTIFICATION -->
<?php
if(isset($_GET['msg'])){
    echo "<div class='alert alert-success'>".urldecode($_GET['msg'])."</div>";
}
?>

<!-- FORM -->
<form method="POST">

<select name="crop_id" id="crop" class="form-control mb-2" required>
<option value="">Select Crop</option>
<?php
$res=$conn->query("SELECT * FROM crops");
while($r=$res->fetch_assoc()){
echo "<option value='{$r['id']}'>{$r['crop_name']}</option>";
}
?>
</select>

<select name="market_id" id="market" class="form-control mb-2" required>
<option value="">Select Market</option>
<?php
$res=$conn->query("SELECT * FROM markets");
while($r=$res->fetch_assoc()){
echo "<option value='{$r['id']}'>{$r['market_name']}</option>";
}
?>
</select>

<input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity (bags)" required>

<input type="text" id="price" name="price" class="form-control mb-2" placeholder="Selected Price" readonly required>

<button class="btn btn-success w-100" name="sell">Sell Product</button>

</form>

<hr>

<!-- PRICE TABLE -->
<h4>Market Prices</h4>

<div class="table-responsive">

<table class="table table-bordered table-striped">

<tr>
<th>Crop</th>
<th>Market</th>
<th>Price (GHS)</th>
<th>Select</th>
</tr>

<?php
$res=$conn->query("
SELECT crops.id AS crop_id, markets.id AS market_id,
crops.crop_name, markets.market_name, prices.price
FROM prices
JOIN crops ON crops.id=prices.crop_id
JOIN markets ON markets.id=prices.market_id
");

while($r=$res->fetch_assoc()){

echo "<tr>
<td>{$r['crop_name']}</td>
<td>{$r['market_name']}</td>
<td>{$r['price']}</td>
<td>
<button class='btn btn-primary btn-sm'
onclick=\"selectPrice('{$r['crop_id']}', '{$r['market_id']}', '{$r['price']}')\">
Select
</button>
</td>
</tr>";
}
?>

</table>

</div>

</div>

</body>
</html>