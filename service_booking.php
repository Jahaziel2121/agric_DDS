<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_GET['company_id'])) {
    die("No company selected");
}

$company_id = $_GET['company_id'];

/* =========================
   GET COMPANY + SERVICE DATA (JOIN FIX)
========================= */
$sql = "SELECT c.name, c.location, c.phone,
               s.service_type, s.service_name, s.price, s.description, s.image
        FROM companies c
        JOIN company_services s ON c.id = s.company_id
        WHERE c.id = '$company_id'";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Company not found or no services available");
}

/* Get first service (you can extend later for multiple services) */
$data = $result->fetch_assoc();

/* =========================
   CALCULATIONS
========================= */
$price = $data['price'];
$label = "";

if ($data['service_type'] == 'labor') {
    $label = "Number of Days / Workers";
} elseif ($data['service_type'] == 'inputs') {
    $label = "Quantity (Bags / Units)";
} elseif ($data['service_type'] == 'tools') {
    $label = "Number of Tools / Days";
} else {
    $label = "Quantity";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Service Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f6; color: #333; margin: 0; padding-bottom: 50px; }

.page-container { max-width: 600px; margin: 40px auto; padding: 0 20px; }

.booking-card {
    background: white; padding: 30px; border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06); border: 1px solid #eee;
}

.company-header {
    text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed #ddd;
}
.company-header h2 { font-weight: 800; color: #1b5e20; margin: 0 0 5px; font-size: 24px; }
.company-header p { color: #666; font-size: 14px; margin: 0; }

.service-info { margin-bottom: 25px; background: #f9fbfa; padding: 15px 20px; border-radius: 12px; }
.service-info p { margin: 8px 0; font-size: 15px; color: #444; }
.service-info i { width: 20px; color: #2e7d32; text-align: center; margin-right: 8px; }
.price-tag { font-size: 18px; font-weight: 800; color: #1565c0; margin-top: 10px !important; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14.5px; }
.form-group input {
    width: 100%; padding: 14px; border: 1.5px solid #ddd; border-radius: 10px;
    font-size: 15px; background: #fafafa; transition: 0.2s;
}
.form-group input:focus { border-color: #2e7d32; outline: none; background: #fff; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); }

.total-box {
    background: #e8f5e9; padding: 20px; border-radius: 12px; margin-bottom: 25px;
    border: 1px solid #c8e6c9; display: flex; align-items: center; justify-content: space-between;
}
.total-box label { font-weight: 700; color: #2e7d32; margin: 0; font-size: 16px; }
.total-box input {
    background: transparent; border: none; font-size: 22px; font-weight: 800;
    color: #1b5e20; text-align: right; width: 150px; padding: 0; margin: 0; outline: none;
}

.btn-confirm {
    width: 100%; padding: 15px; background: linear-gradient(135deg, #2e7d32, #4caf50);
    color: white; border: none; border-radius: 12px; font-size: 16px;
    font-weight: 700; cursor: pointer; transition: 0.3s;
}
.btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,125,50,0.3); }
</style>

<script>
function calculateTotal() {
    let qty = parseFloat(document.getElementById("qty").value) || 0;
    let price = parseFloat(document.getElementById("price").value) || 0;

    let total = qty * price;

    document.getElementById("total").value = total > 0 ? total.toFixed(2) : "";
}
</script>

</head>
<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="page-container">
    <?php if (!empty($data['image'])): ?>
        <img src="images/<?= htmlspecialchars($data['image']) ?>" alt="Service" style="width:100%; height:250px; object-fit:cover; border-radius:16px; margin-bottom:20px; box-shadow:0 8px 30px rgba(0,0,0,0.1);">
    <?php endif; ?>

    <div class="booking-card">
        <div class="company-header">
            <h2><i class="fas fa-building"></i> <?= htmlspecialchars($data['name']) ?></h2>
            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($data['location']) ?> &nbsp;|&nbsp; <i class="fas fa-phone"></i> <?= htmlspecialchars($data['phone']) ?></p>
        </div>

        <div class="service-info">
            <p><i class="fas fa-tools"></i> <b>Service:</b> <?= htmlspecialchars($data['service_name']) ?></p>
            <p><i class="fas fa-layer-group"></i> <b>Category:</b> <?= ucfirst(htmlspecialchars($data['service_type'])) ?></p>
            <p><i class="fas fa-info-circle"></i> <b>Description:</b> <?= htmlspecialchars($data['description']) ?></p>
            <p class="price-tag"><i class="fas fa-money-bill-wave"></i> Rate: GHS <?= number_format($price, 2) ?></p>
        </div>

        <form method="POST" action="process_booking.php">
            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company_id) ?>">
            <input type="hidden" id="price" value="<?= $price ?>">

            <div class="form-group">
                <label><?= htmlspecialchars($label) ?></label>
                <input type="number" id="qty" name="quantity" min="1" placeholder="Enter amount..." oninput="calculateTotal()" required>
            </div>

            <div class="total-box">
                <label>Estimated Total (GHS)</label>
                <input type="text" id="total" name="total_price" value="0.00" readonly>
            </div>

            <button type="submit" class="btn-confirm">
                <i class="fas fa-check-circle"></i> Confirm Booking Request
            </button>
        </form>
    </div>

</div>

</body>
</html>