<?php
session_start();
include 'db.php';
include 'back_button.php';

// Restrict to admin
if($_SESSION['role'] != 'admin'){
    header("Location: dashboard.php");
    exit();
}

// Update price
if(isset($_POST['update'])){
    $id = $_POST['price_id'];
    $price = $_POST['price'];

    $conn->query("UPDATE prices SET price='$price' WHERE id=$id");

    header("Location: manage_prices.php?msg=Price updated successfully");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Prices</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container mt-4">

<h3>Manage Market Prices</h3>

<?php
if(isset($_GET['msg'])){
    echo "<div class='alert alert-success'>".$_GET['msg']."</div>";
}
?>

<table class="table table-bordered table-striped">

<tr>
<th>Crop</th>
<th>Market</th>
<th>Price (GHS)</th>
<th>Update</th>
</tr>

<?php
$res=$conn->query("
SELECT prices.id, crops.crop_name, markets.market_name, prices.price
FROM prices
JOIN crops ON crops.id=prices.crop_id
JOIN markets ON markets.id=prices.market_id
");

while($r=$res->fetch_assoc()){
echo "<tr>
<form method='POST'>
<td>{$r['crop_name']}</td>
<td>{$r['market_name']}</td>

<td>
<input type='number' name='price' value='{$r['price']}' class='form-control' required>
</td>

<td>
<input type='hidden' name='price_id' value='{$r['id']}'>
<button class='btn btn-success btn-sm' name='update'>Save</button>
</td>

</form>
</tr>";
}
?>

</table>

</div>

</body>
</html>