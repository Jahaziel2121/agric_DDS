<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';
include 'back_button.php';

$user_id = $_SESSION['user_id'];

/* ================= BUY INPUT ================= */
if(isset($_POST['buy'])){

    $input_id = $_POST['input_id'];
    $qty = $_POST['qty'];

    $res = $conn->query("SELECT * FROM inputs WHERE id='$input_id'");
    $item = $res->fetch_assoc();

    if($item && $item['stock'] >= $qty){

        $total = $qty * $item['price'];

        $conn->query("INSERT INTO purchases (farmer_id,input_id,quantity,total_price)
        VALUES ('$user_id','$input_id','$qty','$total')");

        $purchase_id = $conn->insert_id;

        $new_stock = $item['stock'] - $qty;
        $conn->query("UPDATE inputs SET stock='$new_stock' WHERE id='$input_id'");

        echo "<script>alert('Purchase successful!');</script>";
    }
}

/* ================= RATE SUPPLIER ================= */
if(isset($_POST['rate'])){

    $purchase_id = $_POST['purchase_id'];
    $delivery = $_POST['delivery_status'];
    $quality = $_POST['quality_status'];
    $rating = $_POST['rating'];

    $conn->query("INSERT INTO supplier_performance
    (supplier_id, purchase_id, delivery_status, return_status, rating)
    VALUES (1,'$purchase_id','$delivery','$quality','$rating')");

    echo "<script>alert('Review submitted!');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Shop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container mt-4">

<h4>Available Inputs</h4>

<table class="table table-bordered">
<tr>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Buy</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM inputs");

while($row = $res->fetch_assoc()){
?>

<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['category'] ?></td>
<td><?= $row['price'] ?></td>
<td><?= $row['stock'] ?></td>

<td>
<form method="POST" class="d-flex gap-2">
<input type="hidden" name="input_id" value="<?= $row['id'] ?>">
<input type="number" name="qty" class="form-control" placeholder="Qty" required>
<button name="buy" class="btn btn-success">Buy</button>
</form>
</td>
</tr>

<?php } ?>

</table>

<hr>

<h4>Rate Supplier</h4>

<form method="POST">

<input type="number" name="purchase_id" class="form-control mb-2" placeholder="Purchase ID" required>

<select name="delivery_status" class="form-control mb-2">
<option value="on_time">On Time</option>
<option value="late">Late</option>
</select>

<select name="quality_status" class="form-control mb-2">
<option value="good">Good</option>
<option value="damaged">Damaged</option>
</select>

<input type="number" name="rating" class="form-control mb-2" placeholder="Rating (1-5)" required>

<button name="rate" class="btn btn-primary">Submit Review</button>

</form>

</div>

</body>
</html>