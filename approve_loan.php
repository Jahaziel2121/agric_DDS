<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php?tab=loans");
    exit();
}

$loan_id = $_GET['id'];
$message = "";

// Fetch Loan Details
$loan_res = $conn->query("
    SELECT l.*, u.name, u.phone, u.location, u.fraud_reports, u.avg_completion_days, u.id AS farmer_id
    FROM loans l
    JOIN users u ON l.user_id = u.id
    WHERE l.id = '$loan_id'
");

if (!$loan_res || $loan_res->num_rows == 0) {
    die("Loan request not found.");
}
$loan = $loan_res->fetch_assoc();
$farmer_id = $loan['farmer_id'];

// Process Approval
if (isset($_POST['approve_loan'])) {
    $rate = (float)$_POST['interest_rate'];
    $term = $conn->real_escape_string($_POST['repayment_term']);

    $sql = "UPDATE loans SET status='Approved', interest_rate='$rate', repayment_term='$term' WHERE id='$loan_id'";
    if ($conn->query($sql)) {
        // Send Notification to Farmer
        $amount = number_format($loan['amount'], 2);
        $msg = "Your loan request for GHS {$amount} has been Approved!";
        $date = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO notifications (user_id, message, date) VALUES ('$farmer_id', '$msg', '$date')");
        
        header("Location: admin_dashboard.php?tab=loans&msg=loan_approved");
        exit();
    } else {
        $message = "Error approving loan: " . $conn->error;
    }
}

// Fetch Farmer Trust Score
$farmer_id = $loan['farmer_id'];
$rep = $conn->query("SELECT * FROM buyer_reputation WHERE buyer_id='$farmer_id'");
$r = ['on_time_payments' => 0, 'delayed_payments' => 0];
if ($rep && $rep->num_rows > 0) { $r = $rep->fetch_assoc(); }
$total = $r['on_time_payments'] + $r['delayed_payments'];
if($total > 0) {
    $score = round(($r['on_time_payments'] / $total) * 100);
} else {
    $score = "N/A";
}

// Fetch Total Sales
$sales_res = $conn->query("SELECT COUNT(*) AS total_sales FROM transactions WHERE farmer_id='$farmer_id' AND status='Completed'");
$sales = $sales_res ? $sales_res->fetch_assoc()['total_sales'] : 0;

?>

<!DOCTYPE html>
<html>
<head>
<title>Approve Loan | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f6; color: #333; padding-bottom: 50px; }

.page-title { font-weight: 800; color: #1b5e20; margin: 30px 0 20px; text-align: center; }

.card {
    background: white; border-radius: 16px; padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #eee; margin: 0 auto; max-width: 800px;
}
.card h4 { font-weight: 800; color: #222; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }

.info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
.info-row span { color: #666; font-weight: 500; }
.info-row b { color: #222; font-weight: 700; }

.stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 25px 0; }
.stat-box {
    background: #f8f9fa; border: 1px solid #eee; padding: 15px; border-radius: 12px; text-align: center;
}
.stat-box span { display: block; font-size: 13px; color: #666; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
.stat-box b { font-size: 22px; color: #222; }

.stat-box.score { background: #e8f5e9; border-color: #c8e6c9; }
.stat-box.score b { color: #2e7d32; }

.stat-box.fraud { background: #ffebee; border-color: #ffcdd2; }
.stat-box.fraud b { color: #c62828; }

.form-control, .form-select { border-radius: 10px; padding: 12px 15px; font-size: 14.5px; background: #fafafa; border-color: #ddd; }
.form-control:focus, .form-select:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); background: white; }

.btn-custom {
    background: linear-gradient(135deg, #2e7d32, #4caf50); color: white; border: none;
    padding: 14px; border-radius: 10px; font-weight: 700; font-size: 15px; transition: 0.3s; width: 100%;
}
.btn-custom:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(46,125,50,0.3); color: white; }

.ghana-card-img {
    width: 100%; max-height: 250px; object-fit: contain; border-radius: 10px; border: 1px dashed #ccc; padding: 5px; background: #fafafa;
}
</style>
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container">
    <h2 class="page-title"><i class='fas fa-user-shield'></i> Comprehensive Loan Review</h2>

    <?php if ($message) echo "<div class='alert alert-danger fw-bold text-center' style='max-width:800px; margin:0 auto 20px;'>$message</div>"; ?>

    <div class="card">
        <div class="row">
            <div class="col-md-6 border-end">
                <h4><i class="fas fa-file-invoice-dollar"></i> Request Details</h4>
                <div class="info-row"><span>Farmer Name:</span> <b><?= htmlspecialchars($loan['name']) ?></b></div>
                <div class="info-row"><span>Phone:</span> <b><?= htmlspecialchars($loan['phone']) ?></b></div>
                <div class="info-row"><span>Location:</span> <b><?= htmlspecialchars($loan['location']) ?></b></div>
                <div class="info-row"><span>Requested Amount:</span> <b style="color:#1b5e20;">GHS <?= number_format($loan['amount'], 2) ?></b></div>
                <div class="info-row"><span>Purpose:</span> <b><?= htmlspecialchars($loan['purpose']) ?></b></div>
                <div class="info-row"><span>Date Applied:</span> <b><?= date('M d, Y', strtotime($loan['created_at'])) ?></b></div>
                
                <h5 style="margin-top:20px; font-weight:700; color:#555; font-size:15px;">Identity Verification</h5>
                <div class="info-row"><span>Ghana Card:</span> <b><?= htmlspecialchars($loan['ghana_card_number'] ?: 'Not Provided') ?></b></div>
                
                <?php if(!empty($loan['ghana_card_image'])): ?>
                    <div class="mt-3">
                        <img src="uploads/<?= htmlspecialchars($loan['ghana_card_image']) ?>" class="ghana-card-img" alt="Ghana Card">
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning py-2 mt-2" style="font-size:13px;">⚠ No Ghana Card Image Uploaded</div>
                <?php endif; ?>
            </div>

            <div class="col-md-6 ps-4">
                <h4><i class="fas fa-chart-bar"></i> Applicant Profile</h4>
                
                <div class="stats-grid">
                    <div class="stat-box score">
                        <span><i class="fas fa-star"></i> Trust Score</span>
                        <b><?= $score ?><?= $score !== 'N/A' ? '%' : '' ?></b>
                    </div>
                    <div class="stat-box">
                        <span><i class="fas fa-shopping-cart"></i> Completed Sales</span>
                        <b><?= $sales ?></b>
                    </div>
                    <div class="stat-box fraud">
                        <span><i class="fas fa-exclamation-triangle"></i> Fraud Reports</span>
                        <b><?= $loan['fraud_reports'] ?? 0 ?></b>
                    </div>
                    <div class="stat-box">
                        <span><i class="fas fa-stopwatch"></i> Avg. Deal Time</span>
                        <b><?= $loan['avg_completion_days'] ?? 'N/A' ?> Days</b>
                    </div>
                </div>

                <?php if($loan['status'] == 'Pending'): ?>
                    <form method="POST" style="margin-top: 30px; background:#f9fbfa; padding:20px; border-radius:12px; border:1px solid #eee;">
                        <h5 style="font-weight:700; margin-bottom:15px; color:#1565c0;"><i class="fas fa-sliders-h"></i> Admin Decision</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Assigned Interest Rate (% Monthly)</label>
                            <input type="number" name="interest_rate" class="form-control" step="0.1" value="<?= htmlspecialchars($loan['interest_rate']) ?>" readonly style="background:#e9ecef; cursor:not-allowed;">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small">Requested Repayment Duration</label>
                            <input type="text" name="repayment_term" class="form-control" value="<?= htmlspecialchars($loan['repayment_term']) ?>" readonly style="background:#e9ecef; cursor:not-allowed;">
                        </div>
                        
                        <div style="font-size:12px; color:#666; margin-bottom:15px; font-weight:600;"><i class="fas fa-lock" style="color:#888;"></i> Fields locked to preserve the applicant's accepted terms.</div>

                        <button class="btn btn-custom" name="approve_loan"><i class="fas fa-check-circle"></i> Approve & Dispatch Funds</button>
                    </form>
                <?php else: ?>
                    <div style="margin-top: 30px; background:#f0fdf4; padding:20px; border-radius:12px; border:1px solid #bbf7d0; text-align:center;">
                        <h5 style="font-weight:800; color:#166534; margin-bottom:15px;"><i class="fas fa-check-double"></i> Loan Processed</h5>
                        <p style="color:#15803d; margin:0 0 5px;"><strong>Status:</strong> <?= htmlspecialchars($loan['status']) ?></p>
                        <p style="color:#15803d; margin:0 0 5px;"><strong>Assigned Rate:</strong> <?= htmlspecialchars($loan['interest_rate']) ?>%</p>
                        <p style="color:#15803d; margin:0;"><strong>Duration:</strong> <?= htmlspecialchars($loan['repayment_term']) ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

</body>
</html>