<?php
include 'db.php';

$queries = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT UNIQUE,
        phone TEXT,
        location TEXT,
        password TEXT,
        role TEXT,
        is_verified INTEGER DEFAULT 0,
        otp TEXT
    )",
    "farmers" => "CREATE TABLE IF NOT EXISTS farmers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        phone TEXT,
        location TEXT,
        role TEXT,
        farm_size TEXT,
        email TEXT,
        password TEXT
    )",
    "products" => "CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        product_name TEXT,
        name TEXT,
        quantity INTEGER,
        type TEXT,
        unit TEXT,
        image TEXT,
        status TEXT,
        description TEXT,
        price REAL
    )",
    "orders" => "CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        buyer_id INTEGER,
        product_id INTEGER,
        quantity INTEGER,
        total REAL,
        status TEXT
    )",
    "buy_products" => "CREATE TABLE IF NOT EXISTS buy_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        price REAL,
        description TEXT,
        image TEXT
    )",
    "cart" => "CREATE TABLE IF NOT EXISTS cart (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        buyer_id INTEGER,
        product_id INTEGER,
        quantity INTEGER
    )",
    "markets" => "CREATE TABLE IF NOT EXISTS markets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        market_name TEXT,
        location TEXT
    )",
    "prices" => "CREATE TABLE IF NOT EXISTS prices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        crop_name TEXT,
        market_name TEXT,
        price REAL
    )",
    "insurance" => "CREATE TABLE IF NOT EXISTS insurance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        type TEXT,
        amount REAL,
        status TEXT
    )",
    "loans" => "CREATE TABLE IF NOT EXISTS loans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        company_id INTEGER,
        amount REAL,
        purpose TEXT,
        repayment_term TEXT,
        status TEXT
    )",
    "transactions" => "CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        buyer_id INTEGER,
        farmer_id INTEGER,
        product_id INTEGER,
        quantity INTEGER,
        status TEXT,
        date TEXT
    )",
    "buy_requests" => "CREATE TABLE IF NOT EXISTS buy_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        buyer_id INTEGER,
        product_id INTEGER,
        quantity INTEGER,
        status TEXT
    )",
    "bookings" => "CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        service_id INTEGER,
        date TEXT,
        status TEXT
    )",
    "sales" => "CREATE TABLE IF NOT EXISTS sales (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        farmer_id INTEGER,
        product_id INTEGER,
        quantity INTEGER,
        total_price REAL,
        date TEXT
    )",
    "buyer_reputation" => "CREATE TABLE IF NOT EXISTS buyer_reputation (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        buyer_id INTEGER,
        score REAL,
        reviews INTEGER
    )",
    "inputs" => "CREATE TABLE IF NOT EXISTS inputs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        description TEXT,
        price REAL,
        stock INTEGER,
        supplier_id INTEGER
    )",
    "purchases" => "CREATE TABLE IF NOT EXISTS purchases (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        farmer_id INTEGER,
        input_id INTEGER,
        quantity INTEGER,
        total_price REAL,
        date TEXT
    )",
    "supplier_performance" => "CREATE TABLE IF NOT EXISTS supplier_performance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        supplier_id INTEGER,
        score REAL,
        reviews INTEGER
    )",
    "notifications" => "CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        message TEXT,
        is_read INTEGER DEFAULT 0,
        date TEXT
    )",
    "loan_companies" => "CREATE TABLE IF NOT EXISTS loan_companies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        interest_rate REAL,
        max_amount REAL,
        terms TEXT
    )",
    "companies" => "CREATE TABLE IF NOT EXISTS companies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        location TEXT,
        phone TEXT
    )",
    "company_services" => "CREATE TABLE IF NOT EXISTS company_services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_id INTEGER,
        service_type TEXT,
        service_name TEXT,
        price REAL,
        description TEXT
    )",
    "crops" => "CREATE TABLE IF NOT EXISTS crops (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        crop_name TEXT
    )",
    "farmer_products" => "CREATE TABLE IF NOT EXISTS farmer_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        crop_id INTEGER,
        market_id INTEGER,
        quantity INTEGER,
        expected_price REAL,
        status TEXT
    )",
    "buy_transactions" => "CREATE TABLE IF NOT EXISTS buy_transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        product_id INTEGER,
        quantity INTEGER,
        total_price REAL,
        date TEXT
    )"
];

foreach ($queries as $name => $sql) {
    if ($conn->query($sql)) {
        echo "Successfully executed query for $name<br>\n";
    } else {
        echo "Error executing query for $name: " . $conn->error . "<br>\n";
    }
}
echo "Basic database initialization complete. You may need to add more tables depending on the exact MySQL schema.\n";
?>
