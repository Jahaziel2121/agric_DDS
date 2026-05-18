<?php
include 'db.php';

$companies = [
    [
        'name' => 'AgriTech Rentals & Services',
        'location' => 'Kumasi',
        'phone' => '0551234567',
        'services' => [
            ['tools', 'Heavy Duty Tractor', 1500, 'Rent a heavy duty tractor for land preparation.', 'IMG_3458.JPG'],
            ['tools', 'Combine Harvester', 2500, 'Efficient combine harvester for maize and rice.', 'IMG_3459.JPG']
        ]
    ],
    [
        'name' => 'GreenField Tooling',
        'location' => 'Accra',
        'phone' => '0249876543',
        'services' => [
            ['tools', 'Rotary Tiller', 400, 'Motorized rotary tiller for soil aeration.', 'IMG_3460.JPG'],
            ['tools', 'Industrial Sprayer', 200, 'High-capacity sprayer for fertilizer and pesticide.', 'IMG_3461.JPG']
        ]
    ],
    [
        'name' => 'Farmers Equip Hub',
        'location' => 'Tamale',
        'phone' => '0501122334',
        'services' => [
            ['tools', 'Precision Planter', 600, 'Automated precision planter for row crops.', 'IMG_3462.JPG']
        ]
    ]
];

foreach ($companies as $comp) {
    $stmt = $conn->prepare("INSERT INTO companies (name, location, phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $comp['name'], $comp['location'], $comp['phone']);
    $stmt->execute();
    $comp_id = $conn->insert_id;
    
    foreach ($comp['services'] as $srv) {
        $stmt2 = $conn->prepare("INSERT INTO company_services (company_id, service_type, service_name, price, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("issdss", $comp_id, $srv[0], $srv[1], $srv[2], $srv[3], $srv[4]);
        $stmt2->execute();
    }
}

echo "Successfully seeded tool services with new images!";
?>
