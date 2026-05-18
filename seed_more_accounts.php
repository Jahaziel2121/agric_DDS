<?php
include 'db.php';

$more_companies = [
    [
        'name' => 'Green Hands Labor Group',
        'location' => 'Kumasi',
        'phone' => '0244111222',
        'services' => [
            ['labor', 'Professional Harvesters', 50, 'Experienced workers for seasonal crop harvesting.']
        ]
    ],
    [
        'name' => 'Fertilizer Direct',
        'location' => 'Accra',
        'phone' => '0505333444',
        'services' => [
            ['inputs', 'Organic Compost (50kg)', 120, 'Premium organic fertilizer for healthy crop growth.']
        ]
    ],
    [
        'name' => 'Tamale Plowing Services',
        'location' => 'Tamale',
        'phone' => '0200555666',
        'services' => [
            ['labor', 'Manual Land Clearing', 30, 'Deep clearing and weeding services per acre.']
        ]
    ]
];

foreach ($more_companies as $comp) {
    $stmt = $conn->prepare("INSERT INTO companies (name, location, phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $comp['name'], $comp['location'], $comp['phone']);
    $stmt->execute();
    $comp_id = $conn->insert_id;
    
    foreach ($comp['services'] as $srv) {
        $stmt2 = $conn->prepare("INSERT INTO company_services (company_id, service_type, service_name, price, description) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("issds", $comp_id, $srv[0], $srv[1], $srv[2], $srv[3]);
        $stmt2->execute();
    }
}

echo "Successfully added more service provider accounts!";
?>
