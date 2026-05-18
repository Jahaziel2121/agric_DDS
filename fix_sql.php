<?php
include 'db.php';

$alters = [
    "ALTER TABLE orders ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE notifications ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE bookings ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE transactions ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE buy_transactions ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE transactions ADD COLUMN unit_price REAL",
    "ALTER TABLE bookings ADD COLUMN company_id INTEGER",
    "ALTER TABLE bookings ADD COLUMN service_type TEXT",
    "ALTER TABLE bookings ADD COLUMN service_name TEXT",
    "ALTER TABLE bookings ADD COLUMN quantity INTEGER",
    "ALTER TABLE bookings ADD COLUMN total_price REAL",
    "ALTER TABLE buy_products ADD COLUMN seller_id INTEGER",
    "ALTER TABLE farmer_products ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE products ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE sales ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP"
];

foreach ($alters as $sql) {
    if ($conn->query($sql)) {
        echo "Successfully executed: $sql<br>\n";
    } else {
        echo "Error or already exists: " . $conn->error . "<br>\n";
    }
}
echo "SQL errors fixed.\n";
?>