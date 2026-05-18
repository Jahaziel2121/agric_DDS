<?php
include 'db.php';

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmers Market</title>

<style>
body {
    font-family: Arial;
    background: #eef2f7;
}

.container {
    width: 90%;
    margin: auto;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
}

button {
    width: 100%;
    padding: 10px;
    background: #2e7d32;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    background: #1b5e20;
}
</style>

</head>

<body>

<div class="container">

<h2><i class='fas fa-wheat-awn'></i> Farmers Selling Products</h2>

<div class="grid">

<?php while($row = $result->fetch_assoc()) { ?>

<div class="card">

    <?php $img_path = strpos($row['image'], 'uploads/') === 0 ? $row['image'] : "uploads/" . $row['image']; ?>
    <img src="<?= $img_path ?>">

    <h3><?= $row['product_name'] ?></h3>

    <p><b>Farmer:</b> <?= $row['farmer_name'] ?></p>
    <p><b>Location:</b> <?= $row['location'] ?></p>

    <p><b>Type:</b> <?= $row['type'] ?></p>

    <p><b>Price:</b> GHS <?= $row['price'] ?></p>

    <p><b>Quantity:</b> <?= $row['quantity'] ?></p>

    <p><b>Contact:</b> <?= $row['contact'] ?></p>

    <!-- BUY REQUEST -->
    <form action="request_buy.php" method="POST">

        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">

        <input type="number" name="quantity" placeholder="Quantity" required>

        <button type="submit">Request to Buy</button>

    </form>

</div>

<?php } ?>

</div>

</div>

</body>
</html>