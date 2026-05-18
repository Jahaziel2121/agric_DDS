<?php
session_start();
include 'db.php';

if($_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

if(isset($_POST['add'])){
    $market = $_POST['market_name'];

    $conn->query("INSERT INTO markets (market_name) VALUES ('$market')");
    echo "<script>alert('Market added successfully');</script>";
}
?>

<h3>Add Market</h3>

<form method="POST">
<input type="text" name="market_name" placeholder="Market Name" required>
<button name="add">Add</button>
</form>
