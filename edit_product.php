<?php
include 'db.php';

$id = $_GET['id'];

$product = $conn->query("SELECT * FROM buy_products WHERE id='$id'")->fetch_assoc();

if (isset($_POST['update'])) {

    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $conn->query("
        UPDATE buy_products 
        SET name='$name',
            category='$category',
            price='$price',
            quantity='$quantity'
        WHERE id='$id'
    ");

    header("Location: admin_products.php");
}
?>

<form method="POST">

<input name="name" value="<?= $product['name'] ?>">
<input name="category" value="<?= $product['category'] ?>">
<input name="price" value="<?= $product['price'] ?>">
<input name="quantity" value="<?= $product['quantity'] ?>">

<button name="update">Update</button>

</form>