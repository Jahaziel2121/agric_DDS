<?php
include 'db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM buy_products WHERE id='$id'");

header("Location: admin_products.php");
exit();
?>