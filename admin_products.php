<?php
include 'db.php';

$result = $conn->query("SELECT * FROM buy_products");
?>

<h2>All Products</h2>

<table border="1" width="100%" cellpadding="10">
<tr>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>Stock</th>
    <th>Image</th>
    <th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['category'] ?></td>
    <td>GHS <?= $row['price'] ?></td>
    <td><?= $row['quantity'] ?></td>
    <?php 
    $img_path = strpos($row['image'], 'uploads/') === 0 ? $row['image'] : "uploads/" . $row['image']; 
    $img = (!empty($row['image']) && file_exists($img_path)) ? $img_path : "default_product.jpg";
    ?>
    <td><img src="<?= $img ?>" width="60"></td>

    <td>
        <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
        <a href="delete_product.php?id=<?= $row['id'] ?>" style="background:red;">Delete</a>
    </td>
</tr>

<?php } ?>

</table>