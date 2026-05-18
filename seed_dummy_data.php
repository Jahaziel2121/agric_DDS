<?php
include 'db.php';

// Check if already seeded
$res = $conn->query("SELECT COUNT(*) as c FROM users WHERE email='kwame.farmer@example.com'");
$row = $res->fetch_assoc();
if ($row['c'] > 0) {
    die("Dummy data is already seeded!");
}

// 1. Create Dummy Users
$password = password_hash('password123', PASSWORD_DEFAULT);

$users = [
    "INSERT INTO users (name, email, phone, location, password, role, is_verified) VALUES ('Kwame Mensah', 'kwame.farmer@example.com', '0541234567', 'Kumasi', '$password', 'farmer', 1)",
    "INSERT INTO users (name, email, phone, location, password, role, is_verified) VALUES ('Akosua Serwaa', 'akosua.farmer@example.com', '0249876543', 'Tamale', '$password', 'farmer', 1)",
    "INSERT INTO users (name, email, phone, location, password, role, is_verified) VALUES ('Yaw Osei', 'yaw.buyer@example.com', '0201122334', 'Accra', '$password', 'buyer', 1)"
];

foreach ($users as $sql) {
    $conn->query($sql);
}

// Get the user IDs
$res = $conn->query("SELECT id FROM users WHERE email='kwame.farmer@example.com'");
$farmer1 = $res->fetch_assoc()['id'];

$res = $conn->query("SELECT id FROM users WHERE email='akosua.farmer@example.com'");
$farmer2 = $res->fetch_assoc()['id'];

$res = $conn->query("SELECT id FROM users WHERE email='yaw.buyer@example.com'");
$buyer1 = $res->fetch_assoc()['id'];

// 2. Insert into Farmers table
$conn->query("INSERT INTO farmers (user_id, name, phone, location, role, farm_size, email, password) VALUES ($farmer1, 'Kwame Mensah', '0541234567', 'Kumasi', 'farmer', '50 Acres', 'kwame.farmer@example.com', '$password')");
$conn->query("INSERT INTO farmers (user_id, name, phone, location, role, farm_size, email, password) VALUES ($farmer2, 'Akosua Serwaa', '0249876543', 'Tamale', 'farmer', '120 Acres', 'akosua.farmer@example.com', '$password')");

// 3. Insert Dummy Products
// First, we copy some images to the uploads folder to make sure they show up perfectly
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
copy('images/maize.JPG', 'uploads/maize_dummy.jpg');
copy('images/tomato.JPG', 'uploads/tomato_dummy.jpg');
copy('images/chicken.JPG', 'uploads/chicken_dummy.jpg');

$products = [
    "INSERT INTO products (user_id, product_name, name, type, quantity, unit, image, status, description, price) VALUES ($farmer1, 'Premium White Maize', 'Premium White Maize', 'Maize', 100, 'bags', 'maize_dummy.jpg', 'available', 'High-quality freshly harvested white maize. Excellent for consumption and processing.', 450)",
    "INSERT INTO products (user_id, product_name, name, type, quantity, unit, image, status, description, price) VALUES ($farmer1, 'Fresh Tomatoes', 'Fresh Tomatoes', 'Tomato', 50, 'crates', 'tomato_dummy.jpg', 'available', 'Ripe, red, and juicy tomatoes straight from the farm.', 120)",
    "INSERT INTO products (user_id, product_name, name, type, quantity, unit, image, status, description, price) VALUES ($farmer2, 'Live Broiler Chickens', 'Live Broiler Chickens', 'Poultry', 200, 'birds', 'chicken_dummy.jpg', 'available', 'Healthy, fully-grown broiler chickens ready for the market. Average weight 2.5kg.', 65)"
];

foreach ($products as $sql) {
    $conn->query($sql);
}

// 4. Insert Company and Services
$conn->query("INSERT INTO companies (name, location, phone) VALUES ('AgroTech Solutions', 'Sunyani', '0554443322')");
$company_id = $conn->insert_id;

$services = [
    "INSERT INTO company_services (company_id, service_type, service_name, price, description) VALUES ($company_id, 'tools', 'Tractor Rental', 500, 'Heavy-duty tractor for plowing and tilling. Price per day.')",
    "INSERT INTO company_services (company_id, service_type, service_name, price, description) VALUES ($company_id, 'inputs', 'NPK Fertilizer', 250, 'High-grade NPK 15-15-15 fertilizer. Price per 50kg bag.')"
];

foreach ($services as $sql) {
    $conn->query($sql);
}

// 5. Insert some dummy orders
$res = $conn->query("SELECT id FROM products WHERE product_name='Premium White Maize'");
$maize_id = $res->fetch_assoc()['id'];

$conn->query("INSERT INTO orders (buyer_id, product_id, quantity, total, status) VALUES ($buyer1, $maize_id, 5, 2250, 'Pending')");
$conn->query("INSERT INTO orders (buyer_id, product_id, quantity, total, status) VALUES ($buyer1, $maize_id, 2, 900, 'Delivered')");

echo "Dummy data seeded successfully!";
?>
