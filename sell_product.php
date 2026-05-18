<?php
include 'db.php';

$type = $_POST['type'] ?? '';
$product_name = $_POST['product_name'] ?? '';
$quantity = $_POST['quantity'] ?? 0;
$unit = $_POST['unit'] ?? '';
$unit_weight = !empty($_POST['unit_weight']) ? $_POST['unit_weight'] : 'NULL';
$weight_unit = $_POST['weight_unit'] ?? 'kg';

$image = $_FILES['image']['name'] ?? '';
$tmp = $_FILES['image']['tmp_name'] ?? '';

$folder = "uploads/";

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$target_file = $folder . basename($image);

/* upload image */
if (move_uploaded_file($tmp, $target_file)) {

    $sql = "INSERT INTO products (type, product_name, quantity, unit, unit_weight, weight_unit, image, status)
            VALUES ('$type', '$product_name', '$quantity', '$unit', $unit_weight, '$weight_unit', '$target_file', 'available')";

    if ($conn->query($sql)) {

        $product_id = $conn->insert_id;

        /* 🔥 IMPORTANT REDIRECT (THIS FIXES YOUR FLOW) */
        header("Location: sell.php?product_id=$product_id&show_buyers=1");
        exit();

    } else {
        die("Database Error: " . $conn->error);
    }

} else {
    die("Image upload failed");
}
?>