<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>View Products</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow">

<h3><i class='fas fa-box'></i> Products List</h3>

<hr>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Farmer</th>
<th>Product</th>
<th>Quantity</th>
</tr>

<?php
$sql = "
SELECT products.id, users.name, products.product_name, products.quantity
FROM products
JOIN users ON users.id = products.user_id
";

$result = $conn->query($sql);

if($result->num_rows > 0){

    while($row = $result->fetch_assoc()){
        echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['name']}</td>
        <td>{$row['product_name']}</td>
        <td>{$row['quantity']} bags</td>
        </tr>";
    }

} else {
    echo "<tr><td colspan='4' class='text-center text-danger'>No products found</td></tr>";
}
?>

</table>

</div>

</div>

</body>
</html>