<?php
include 'db.php';

// Remove Bernard's Farm
$conn->query("DELETE FROM users WHERE name LIKE '%BERNARD%FARM%'");
$conn->query("DELETE FROM farmers WHERE name LIKE '%BERNARD%FARM%'");

// Find existing dummy farmers
$res = $conn->query("SELECT id FROM users WHERE email='kwame.farmer@example.com'");
$row = $res->fetch_assoc();
$farmer1 = $row ? $row['id'] : 1;

$res = $conn->query("SELECT id FROM users WHERE email='akosua.farmer@example.com'");
$row = $res->fetch_assoc();
$farmer2 = $row ? $row['id'] : 1;

// Define more products using the images we have
$more_products = [
    [
        'name' => 'Healthy Cattle',
        'type' => 'Cattle',
        'image_src' => 'images/cattle.jpg',
        'dest' => 'uploads/cattle_dummy.jpg',
        'qty' => 5,
        'unit' => 'heads',
        'price' => 5000,
        'user_id' => $farmer1
    ],
    [
        'name' => 'Farm Fresh Eggs',
        'type' => 'Eggs',
        'image_src' => 'images/eggs.jpg',
        'dest' => 'uploads/eggs_dummy.jpg',
        'qty' => 50,
        'unit' => 'crates',
        'price' => 45,
        'user_id' => $farmer2
    ],
    [
        'name' => 'Goat for Sale',
        'type' => 'Livestock',
        'image_src' => 'images/goat.jpg',
        'dest' => 'uploads/goat_dummy.jpg',
        'qty' => 15,
        'unit' => 'heads',
        'price' => 800,
        'user_id' => $farmer1
    ],
    [
        'name' => 'Healthy Pigs',
        'type' => 'Pig',
        'image_src' => 'images/pig.jpg',
        'dest' => 'uploads/pig_dummy.jpg',
        'qty' => 10,
        'unit' => 'heads',
        'price' => 1500,
        'user_id' => $farmer2
    ],
    [
        'name' => 'Farm Rabbits',
        'type' => 'Rabbit',
        'image_src' => 'images/rabbit.jpg',
        'dest' => 'uploads/rabbit_dummy.jpg',
        'qty' => 30,
        'unit' => 'heads',
        'price' => 120,
        'user_id' => $farmer1
    ],
    [
        'name' => 'Big Turkey',
        'type' => 'Turkey',
        'image_src' => 'images/turkey.jpg',
        'dest' => 'uploads/turkey_dummy.jpg',
        'qty' => 20,
        'unit' => 'birds',
        'price' => 400,
        'user_id' => $farmer2
    ],
    [
        'name' => 'Fresh Okro',
        'type' => 'Okro',
        'image_src' => 'images/okro.jpg',
        'dest' => 'uploads/okro_dummy.jpg',
        'qty' => 100,
        'unit' => 'baskets',
        'price' => 80,
        'user_id' => $farmer1
    ],
    [
        'name' => 'Organic Onions',
        'type' => 'Onion',
        'image_src' => 'images/onions.jpeg',
        'dest' => 'uploads/onions_dummy.jpg',
        'qty' => 200,
        'unit' => 'sacks',
        'price' => 350,
        'user_id' => $farmer2
    ],
    [
        'name' => 'Fresh Watermelon',
        'type' => 'Watermelon',
        'image_src' => 'images/watermelon.jpg',
        'dest' => 'uploads/watermelon_dummy.jpg',
        'qty' => 80,
        'unit' => 'fruits',
        'price' => 25,
        'user_id' => $farmer1
    ],
    [
        'name' => 'Mixed Vegetables',
        'type' => 'Vegetables',
        'image_src' => 'images/vegetables.jpg',
        'dest' => 'uploads/vegetables_dummy.jpg',
        'qty' => 60,
        'unit' => 'baskets',
        'price' => 150,
        'user_id' => $farmer2
    ]
];

foreach ($more_products as $p) {
    if (file_exists($p['image_src'])) {
        copy($p['image_src'], $p['dest']);
        $filename = basename($p['dest']);
        
        $sql = "INSERT INTO products (user_id, product_name, name, type, quantity, unit, image, status, description, price) 
                VALUES ({$p['user_id']}, '{$p['name']}', '{$p['name']}', '{$p['type']}', {$p['qty']}, '{$p['unit']}', '$filename', 'available', 'Fresh farm produce available for immediate purchase.', {$p['price']})";
        $conn->query($sql);
    }
}

echo "More dummy data generated successfully and Bernard's Farm removed.";
?>
