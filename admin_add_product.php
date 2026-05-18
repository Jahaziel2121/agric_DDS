<?php
include 'db.php';

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    /* ================= IMAGE UPLOAD ================= */
    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    $folder = "uploads/" . basename($image);

    if (move_uploaded_file($tmp, $folder)) {

        $sql = "INSERT INTO buy_products 
        (name, category, price, quantity, image)
        VALUES 
        ('$name', '$category', '$price', '$quantity', '$image')";

        if ($conn->query($sql)) {
            $success = "Product added successfully!";
        } else {
            $error = $conn->error;
        }

    } else {
        $error = "Image upload failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Add Product</title>

<style>
body {
    font-family: Arial;
    background: #eef2f7;
}

.container {
    width: 50%;
    margin: auto;
    margin-top: 50px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: #2e7d32;
}

input, select {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
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

.success {
    color: green;
    text-align: center;
}

.error {
    color: red;
    text-align: center;
}
</style>

</head>

<body>

<div class="container">

<h2>🌿 Admin Product Upload</h2>

<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="name" placeholder="Product Name (e.g. Fertilizer)" required>

    <select name="category" required>
        <option value="">Select Category</option>
        <option value="Fertilizer">Fertilizer</option>
        <option value="Seeds">Seeds</option>
        <option value="Tools">Tools</option>
        <option value="Pesticides">Pesticides</option>
    </select>

    <input type="number" name="price" placeholder="Price (GHS)" required>

    <input type="number" name="quantity" placeholder="Stock Quantity" required>

    <input type="file" name="image" required>

    <button type="submit" name="submit">Add Product</button>

</form>

</div>

</body>
</html>s