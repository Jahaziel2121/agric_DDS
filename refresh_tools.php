<?php
include 'db.php';

// Clean up old tool services to avoid clutter
$conn->query("DELETE FROM company_services WHERE service_type='tools'");

$tools = [
    [
        'name' => 'Tractor Power XL',
        'type' => 'tools',
        'price' => 1200,
        'desc' => 'High-performance tractor for large scale land preparation and plowing.',
        'image' => 'IMG_3458.JPG'
    ],
    [
        'name' => 'Master Harvester 3000',
        'type' => 'tools',
        'price' => 2500,
        'desc' => 'Advanced combine harvester for quick and efficient grain harvesting.',
        'image' => 'IMG_3459.JPG'
    ],
    [
        'name' => 'Precision Tiller',
        'type' => 'tools',
        'price' => 450,
        'desc' => 'Motorized rotary tiller perfect for preparing soil for planting.',
        'image' => 'IMG_3460.JPG'
    ],
    [
        'name' => 'Pro-Spray Industrial',
        'type' => 'tools',
        'price' => 180,
        'desc' => 'Industrial grade boom sprayer for efficient fertilizer application.',
        'image' => 'IMG_3461.JPG'
    ],
    [
        'name' => 'Auto-Planter Pro',
        'type' => 'tools',
        'price' => 750,
        'desc' => 'Precision pneumatic planter for uniform seed distribution and depth.',
        'image' => 'IMG_3462.JPG'
    ]
];

// Ensure we have a company to host these
$conn->query("INSERT OR IGNORE INTO companies (id, name, location, phone) VALUES (99, 'Agri-Hire Solutions', 'Global', '0555000111')");

foreach ($tools as $t) {
    $stmt = $conn->prepare("INSERT INTO company_services (company_id, service_type, service_name, price, description, image) VALUES (99, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $t['type'], $t['name'], $t['price'], $t['desc'], $t['image']);
    $stmt->execute();
}

echo "Successfully refreshed 5 tools with individual images!";
?>
