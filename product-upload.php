<?php
include 'db.php';

$name = $_POST['name'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$description = $_POST['description'];

$sql = "INSERT INTO products (name, price, quantity, description)
VALUES ('$name', '$price', '$quantity', '$description')";

if ($conn->query($sql) === TRUE) {
    echo "Product uploaded successfully";
} else {
    echo "Error: " . $conn->error;
}
?>