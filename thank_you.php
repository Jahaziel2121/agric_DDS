<?php
include 'db.php';

if (!isset($_GET['booking_id'])) {
    die("Invalid request");
}

$booking_id = $_GET['booking_id'];

$sql = "SELECT b.*, c.name AS company_name, c.location, c.phone
        FROM bookings b
        JOIN companies c ON b.company_id = c.id
        WHERE b.id = '$booking_id'";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Booking not found");
}

$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Booking Confirmation</title>

<style>
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

.thank-you-card {
    background: white;
    padding: 40px;
    width: 100%;
    max-width: 480px;
    border-radius: 24px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #e8f5e9;
    color: #2e7d32;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    margin: 0 auto 20px;
}

h1 {
    color: #1b5e20;
    font-weight: 800;
    margin: 0 0 10px;
    font-size: 26px;
}

p.subtitle {
    color: #666;
    font-size: 15px;
    margin-bottom: 30px;
}

.details-box {
    background: #f8fbf9;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid #eee;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #edf2ef;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #555;
    font-size: 14px;
}

.detail-value {
    font-weight: 700;
    color: #222;
    font-size: 14px;
}

.total-banner {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    padding: 15px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 20px;
    margin-bottom: 25px;
}

.btn-home {
    display: block;
    background: #1b5e20;
    color: white;
    text-decoration: none;
    padding: 15px;
    border-radius: 12px;
    font-weight: 700;
    transition: 0.3s;
}

.btn-home:hover {
    background: #000;
    transform: translateY(-2px);
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<div class="thank-you-card">
    <div class="success-icon">
        <i class="fas fa-check-circle"></i>
    </div>

    <h1>Booking Confirmed!</h1>
    <p class="subtitle">Your agricultural service has been successfully booked.</p>

    <div class="details-box">
        <div class="detail-row">
            <span class="detail-label">Service</span>
            <span class="detail-value"><?= htmlspecialchars($order['service_name']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Company</span>
            <span class="detail-value"><?= htmlspecialchars($order['company_name']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Location</span>
            <span class="detail-value"><?= htmlspecialchars($order['location']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Contact</span>
            <span class="detail-value"><?= htmlspecialchars($order['phone']) ?></span>
        </div>
    </div>

    <div class="total-banner">
        GHS <?= number_format($order['total_price'], 2) ?>
    </div>

    <p style="font-size: 13px; color: #777; margin-bottom: 25px;">
        <i class="fas fa-info-circle"></i> The company will contact you shortly to coordinate.
    </p>

    <a href="buy.php" class="btn-home">
        <i class="fas fa-arrow-left"></i> Back to Marketplace
    </a>
</div>

</body>
</html>