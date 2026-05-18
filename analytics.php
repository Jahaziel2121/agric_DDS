<?php
include 'db.php';
?>

<h2><i class='fas fa-chart-line'></i> Smart Analytics</h2>

<h3>Top Selling Crops</h3>

<?php
$res = $conn->query("
SELECT product_name, SUM(quantity) as total
FROM sales
GROUP BY product_name
ORDER BY total DESC
");

while($r = $res->fetch_assoc()){
    echo "<p>{$r['product_name']} → {$r['total']} bags</p>";
}
?>

<hr>

<h3>Top Markets</h3>

<?php
$res = $conn->query("
SELECT market_name, COUNT(*) as total
FROM sales
GROUP BY market_name
ORDER BY total DESC
");

while($r = $res->fetch_assoc()){
    echo "<p>{$r['market_name']} → {$r['total']} sales</p>";
}
?>

<hr>

<h3>Most Purchased Inputs</h3>

<?php
$res = $conn->query("
SELECT inputs.name, SUM(purchases.quantity) as total
FROM purchases
JOIN inputs ON inputs.id = purchases.input_id
GROUP BY inputs.name
ORDER BY total DESC
");

while($r = $res->fetch_assoc()){
    echo "<p>{$r['name']} → {$r['total']} units</p>";
}
?>