<?php
include 'db.php';

/* =========================
   VALIDATE ID SAFELY
========================= */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<i class='fas fa-times-circle'></i> Invalid receipt ID");
}

$id = intval($_GET['id']);

/* =========================
   SAFE QUERY (NO CRASH)
========================= */
$sql = "SELECT 
            t.*,
            p.type AS product_name,
            p.price AS unit_price,
            u.name AS buyer_name
        FROM transactions t
        LEFT JOIN products p ON t.product_id = p.id
        LEFT JOIN users u ON t.buyer_id = u.id
        WHERE t.id = $id";

$result = $conn->query($sql);

if (!$result) {
    die("SQL ERROR: " . $conn->error);
}

if ($result->num_rows == 0) {
    die("<i class='fas fa-times-circle'></i> Receipt not found (No transaction with this ID)");
}

$row = $result->fetch_assoc();

/* =========================
   SAFE DATA HANDLING
========================= */
$product_name = $row['product_name'] ?? 'Unknown Product';
$buyer_name   = $row['buyer_name'] ?? 'Unknown Buyer';
$status       = $row['status'] ?? 'Pending';
$created_at   = $row['created_at'] ?? 'N/A';
$buyer_response = $row['buyer_response'] ?? 'No response';

$quantity = (float)($row['quantity'] ?? 0);
$unit_price = (float)($row['unit_price'] ?? 0);
$total_price = (float)($row['total_price'] ?? ($quantity * $unit_price));

$remaining_stock = 0;

/* =========================
   SAFE STOCK CHECK
========================= */
if (!empty($row['product_id'])) {
    $p = $conn->query("SELECT quantity FROM products WHERE id=" . intval($row['product_id']));
    if ($p && $p->num_rows > 0) {
        $remaining_stock = $p->fetch_assoc()['quantity'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Receipt</title>

<style>
body {
    font-family: Arial;
    background: #eef2f7;
    padding: 20px;
}

.receipt {
    width: 420px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.header {
    text-align: center;
    border-bottom: 2px dashed #ccc;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.header h2 {
    color: #2e7d32;
}

.sub {
    font-size: 13px;
    color: #777;
}

.row {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
}

.label {
    color: #555;
}

.value {
    font-weight: bold;
}

.total-box {
    margin-top: 15px;
    padding: 12px;
    background: #e8f5e9;
    border-radius: 10px;
    text-align: center;
}

.status {
    text-align: center;
    margin-top: 10px;
    font-weight: bold;
    color: #1565c0;
}

.footer {
    text-align: center;
    margin-top: 15px;
    font-size: 13px;
    color: #777;
}

button {
    width: 100%;
    margin-top: 15px;
    padding: 12px;
    background: #2e7d32;
    color: white;
    border: none;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="receipt">

    <div class="header">
        <h2>🌿 AGRIC SYSTEM</h2>
        <div class="sub">Official Payment Receipt</div>
    </div>

    <div class="row">
        <span class="label">Product:</span>
        <span class="value"><?= htmlspecialchars($product_name) ?></span>
    </div>

    <div class="row">
        <span class="label">Buyer:</span>
        <span class="value"><?= htmlspecialchars($buyer_name) ?></span>
    </div>

    <div class="row">
        <span class="label">Quantity:</span>
        <span class="value"><?= $quantity ?></span>
    </div>

    <div class="row">
        <span class="label">Unit Price:</span>
        <span class="value">GHS <?= number_format($unit_price, 2) ?></span>
    </div>

    <div class="total-box">
        <h3>Total: GHS <?= number_format($total_price, 2) ?></h3>
    </div>

    <div class="status">
        Status: <?= htmlspecialchars($status) ?>
    </div>

    <div class="row">
        <span class="label">Buyer Response:</span>
        <span class="value"><?= htmlspecialchars($buyer_response) ?></span>
    </div>

    <div class="row">
        <span class="label">Date:</span>
        <span class="value"><?= $created_at ?></span>
    </div>

    <div class="row">
        <span class="label">Remaining Stock:</span>
        <span class="value"><?= $remaining_stock ?> units</span>
    </div>

    <div class="footer">
        Thank you for using Agric Market System
    </div>

    <button onclick="window.print()">🖨 Print Receipt</button>

</div>

</body>
</html>