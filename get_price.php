<?php
include 'db.php';

$crop = $_GET['crop_id'];
$market = $_GET['market_id'];

$res = $conn->query("SELECT price FROM prices WHERE crop_id=$crop AND market_id=$market");

if($row = $res->fetch_assoc()){
    echo $row['price'];
} else {
    echo "0";
}
?>