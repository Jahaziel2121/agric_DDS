<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['add'])){

    $name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    $conn->query("INSERT INTO products (user_id, product_name, quantity)
    VALUES ('$user_id','$name','$quantity')");

    echo "<script>alert('Product Added Successfully');</script>";
}
?>

<form method="POST">
<input name="product_name" placeholder="Product Name" required>
<input name="quantity" placeholder="Quantity (bags)" required>
<button name="add">Add</button>
</form>