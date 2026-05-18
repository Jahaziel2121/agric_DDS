<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_GET['success'])) {
    $message = "<div class='alert alert-success fw-bold text-center' style='border-radius:12px;'>✔ Loan application submitted! The Admin will review your request.</div>";
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $loan_id = $_GET['delete'];
    $conn->query("DELETE FROM loans WHERE id='$loan_id' AND user_id='$user_id'");
    header("Location: loan.php");
    exit();
}

/* ================= APPLY ================= */
if (isset($_POST['apply_loan'])) {

    if(!isset($_POST['agree'])){
        $message = "<i class='fas fa-times-circle'></i> You must accept the Terms & Conditions!";
    } else {

        $amount = (float)$_POST['amount'];
        $purpose = $_POST['purpose'];
        $ghana_card_number = $conn->real_escape_string($_POST['ghana_card_number'] ?? '');
        $repayment_term = (int)$_POST['repayment_term'];
        
        // Ensure term is valid
        if ($repayment_term < 1) $repayment_term = 1;
        $term_str = $repayment_term . " Months";

        // Get actual rate based on the score logic (re-run logic to prevent tampering)
        $rep = $conn->query("SELECT * FROM buyer_reputation WHERE buyer_id='$user_id'");
        $r = ['on_time_payments' => 0, 'delayed_payments' => 0];
        if ($rep && $rep->num_rows > 0) { $r = $rep->fetch_assoc(); }
        $total = $r['on_time_payments'] + $r['delayed_payments'];
        $sys_score = ($total > 0) ? round(($r['on_time_payments'] / $total) * 100) : "N/A";
        
        if($sys_score === "N/A" || $sys_score < 40) $sys_rate = 5;
        elseif($sys_score < 80) $sys_rate = 3.5;
        else $sys_rate = 2;

        // If "Other" selected, use custom input
        if($purpose == "Other"){
            $purpose = $conn->real_escape_string($_POST['custom_purpose']);
        }
        
        $image_name = '';
        if (isset($_FILES['ghana_card_image']) && $_FILES['ghana_card_image']['error'] == 0) {
            $upload_dir = 'uploads/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $image_name = time() . '_' . basename($_FILES['ghana_card_image']['name']);
            move_uploaded_file($_FILES['ghana_card_image']['tmp_name'], $upload_dir . $image_name);
        }

        // Insert into loans table 
        $conn->query("
            INSERT INTO loans (user_id, company_id, amount, purpose, repayment_term, status, ghana_card_number, ghana_card_image, interest_rate)
            VALUES ('$user_id', 0, '$amount', '$purpose', '$term_str', 'Pending', '$ghana_card_number', '$image_name', '$sys_rate')
        ");
        
        $loan_id = $conn->insert_id;
        $amt_formatted = number_format($amount, 2);
        $admin_msg = "<div style='margin-bottom:8px;'><i class='fas fa-hand-holding-usd'></i> New Loan Request! User #{$user_id} requested GHS {$amt_formatted} for {$term_str}.</div><div><a href='approve_loan.php?id={$loan_id}' class='btn btn-sm btn-primary' style='border-radius:20px; font-weight:600; font-size:12px; padding:4px 12px;'>Review Request</a></div>";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (0, ?)");
        $stmt->bind_param("s", $admin_msg);
        $stmt->execute();

        header("Location: loan.php?success=1");
        exit();
    }
}

/* ================= FETCH USER LOANS ================= */
$sql = "SELECT l.*, 
               CASE WHEN l.company_id = 0 THEN 'AGRIC DSS Platform' ELSE c.name END AS provider_name,
               c.interest_rate AS comp_rate
        FROM loans l
        LEFT JOIN loan_companies c ON l.company_id = c.id
        WHERE l.user_id = $user_id
        ORDER BY l.created_at DESC";

$loans = $conn->query($sql);

if (!$loans) {
    die("SQL ERROR (loans): " . $conn->error);
}

/* Fetch Farmer Reputation for display */
$rep = $conn->query("SELECT * FROM buyer_reputation WHERE buyer_id='$user_id'");
$r = ['on_time_payments' => 0, 'delayed_payments' => 0];
if ($rep && $rep->num_rows > 0) { $r = $rep->fetch_assoc(); }
$total = $r['on_time_payments'] + $r['delayed_payments'];
if($total > 0) {
    $score = round(($r['on_time_payments'] / $total) * 100);
} else {
    $score = "N/A";
}

// Determine Dynamic Interest Rate based on Score
if($score === "N/A" || $score < 40) {
    $platform_rate = 5; // 5% monthly for unrated or high risk
} elseif($score < 80) {
    $platform_rate = 3.5;  // 3.5% monthly for medium risk
} else {
    $platform_rate = 2;  // 2% monthly for low risk
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Request Platform Funds | AGRIC DSS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f6; color: #333; padding-bottom: 50px; }

.page-title { font-weight: 800; color: #1b5e20; margin: 30px 0 20px; font-size: 28px; text-align: center; }

.card {
    background: white; border-radius: 16px; padding: 30px; margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #eee;
}
.card h4 { font-weight: 800; color: #222; margin-bottom: 20px; font-size: 18px; }

.trust-badge {
    display: inline-block; padding: 10px 20px; border-radius: 12px; font-weight: 700;
    background: #e8f5e9; color: #2e7d32; border: 1px dashed #c8e6c9; margin-bottom: 20px;
}

.terms-box {
    background: #f9fbfa; border: 1px dashed #c8e6c9; padding: 15px;
    border-radius: 10px; font-size: 13.5px; color: #555; margin-bottom: 20px; line-height: 1.6;
}

.form-control, .form-select { border-radius: 10px; padding: 12px 15px; font-size: 14.5px; background: #fafafa; border-color: #ddd; }
.form-control:focus, .form-select:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); background: white; }

.btn-custom {
    background: linear-gradient(135deg, #2e7d32, #4caf50); color: white; border: none;
    padding: 15px; border-radius: 12px; font-weight: 700; font-size: 16px; transition: 0.3s;
}
.btn-custom:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,125,50,0.3); color: white; }

.loan-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px; border: 1px solid #eee; border-radius: 12px; margin-bottom: 15px; background: #fafafa;
}
.loan-info p { margin: 0 0 4px; font-size: 14px; color: #555; }
.loan-info b { color: #222; font-size: 16px; }
.badge-status { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }

@media (max-width: 768px) {
    .loan-item { flex-direction: column; align-items: flex-start; gap: 15px; }
}
</style>
</head>

<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container" style="max-width: 900px;">

<h2 class="page-title"><i class='fas fa-hand-holding-usd'></i> Request Platform Funds</h2>

<?php if ($message) echo $message; ?>

<div class="row">

    <!-- ================= APPLY FOR LOAN ================= -->
    <div class="col-lg-6">
        <div class="card">
            <h4><i class="fas fa-paper-plane"></i> Submit Loan Request</h4>
            
            <div class="trust-badge w-100 text-center">
                <i class="fas fa-shield-check"></i> Your Platform Trust Score: <b><?= $score ?><?= $score !== 'N/A' ? '%' : '' ?></b>
                <div style="font-size:12px; color:#666; margin-top:4px;">(Your platform interest rate is <b style="color:#2e7d32;"><?= $platform_rate ?>%</b> based on this score)</div>
            </div>

            <div class="terms-box">
                <b><i class="fas fa-info-circle"></i> How it works:</b><br>
                1. You request the amount and select your desired repayment duration.<br>
                2. Our system applies your assigned interest rate (<b><?= $platform_rate ?>%</b>).<br>
                3. You review the calculated total repayment amount below.<br>
                4. The Admin reviews your final application and disburses funds.
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Requested Amount (GHS)</label>
                    <input type="number" name="amount" class="form-control" placeholder="E.g. 5000" min="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Desired Repayment Duration</label>
                    <select name="repayment_term" id="loanTerm" class="form-select" onchange="calculateLoan()" required>
                        <option value="">Select Duration...</option>
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months (1 Year)</option>
                        <option value="24">24 Months (2 Years)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Ghana Card Number</label>
                    <input type="text" name="ghana_card_number" class="form-control" placeholder="GHA-XXXXXXXXX-X" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Upload Ghana Card Picture</label>
                    <input type="file" name="ghana_card_image" class="form-control" accept="image/*" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Primary Purpose</label>
                    <select name="purpose" class="form-select" onchange="toggleOther(this)" required>
                        <option value="">Select Purpose...</option>
                        <option>Buy Seeds</option>
                        <option>Fertilizers & Chemicals</option>
                        <option>Farm Equipment</option>
                        <option>Land Preparation</option>
                        <option>Livestock Farming</option>
                        <option value="Other">Other...</option>
                    </select>
                </div>

                <input type="text" name="custom_purpose" id="otherPurpose" 
                       class="form-control mb-4" placeholder="Specify other purpose..." style="display:none;">

                <div id="loanCalculation" style="display:none; background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px; padding:15px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <span style="color:#2e7d32; font-weight:600; font-size:14px;">Principal Amount:</span>
                        <b id="calcPrincipal" style="color:#1b5e20;"></b>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <span style="color:#2e7d32; font-weight:600; font-size:14px;">Interest (<?= $platform_rate ?>% Monthly):</span>
                        <b id="calcInterest" style="color:#1b5e20;"></b>
                    </div>
                    <hr style="border-color:#a5d6a7; margin:10px 0;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#1b5e20; font-weight:800; font-size:16px;">Total Repayment:</span>
                        <b id="calcTotal" style="color:#1b5e20; font-size:18px;"></b>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="agreeTerm" name="agree" required>
                    <label class="form-check-label fw-bold" for="agreeTerm" style="font-size:14px; color:#555;">I accept the calculated total repayment amount and platform terms.</label>
                </div>

                <button class="btn btn-custom w-100" name="apply_loan"><i class="fas fa-check-circle"></i> Confirm & Send Request</button>
            </form>
        </div>
    </div>

    <!-- ================= LOAN HISTORY ================= -->
    <div class="col-lg-6">
        <div class="card" style="background:#f8f9fa;">
            <h4><i class="fas fa-folder-open"></i> Your Loan Requests</h4>

            <?php if ($loans->num_rows > 0) { ?>
                <div style="display:flex; flex-direction:column; gap:10px;">
                <?php while ($l = $loans->fetch_assoc()) { 
                    if ($l['status'] == "Approved") {
                        $status_html = "<span class='badge-status bg-success text-white'><i class='fas fa-check-circle'></i> Approved</span>";
                    } elseif ($l['status'] == "Rejected") {
                        $status_html = "<span class='badge-status bg-danger text-white'><i class='fas fa-times-circle'></i> Rejected</span>";
                    } else {
                        $status_html = "<span class='badge-status bg-warning text-dark'><i class='fas fa-hourglass-half'></i> Pending</span>";
                    }

                    // For older loans that had a company ID
                    $rate_display = ($l['company_id'] > 0 && isset($l['comp_rate'])) ? $l['comp_rate']."%" : ($l['interest_rate'] ? $l['interest_rate']."%" : "TBD");
                ?>
                    <div class="loan-item bg-white shadow-sm">
                        <div class="loan-info w-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span style="font-size:12px; color:#888; font-weight:700; margin-bottom:2px; display:block;">#LN-<?= str_pad($l['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                    <b style="font-size:18px; color:#1b5e20;">GHS <?= number_format($l['amount'], 2) ?></b>
                                </div>
                                <?= $status_html ?>
                            </div>
                            
                            <p><i class='fas fa-bullseye text-muted' style="width:16px;"></i> <?= htmlspecialchars($l['purpose']) ?></p>
                            
                            <?php if($l['status'] == 'Approved'): ?>
                                <p><i class='fas fa-chart-line text-muted' style="width:16px;"></i> <b>Rate:</b> <?= $rate_display ?></p>
                                <p><i class='fas fa-calendar-alt text-muted' style="width:16px;"></i> <b>Duration:</b> <?= htmlspecialchars($l['repayment_term']) ?></p>
                            <?php else: ?>
                                <p><i class='fas fa-clock text-muted' style="width:16px;"></i> <i>Awaiting admin review</i></p>
                            <?php endif; ?>

                            <?php if($l['status'] == 'Pending'): ?>
                                <div class="text-end mt-2">
                                    <a href="loan.php?delete=<?= $l['id'] ?>" class="text-danger" style="font-size:13px; text-decoration:none; font-weight:600;" onclick="return confirm('Withdraw application?');"><i class="fas fa-trash"></i> Cancel Request</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>
                </div>
            <?php } else { ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fs-1 mb-3 opacity-25"></i>
                    <p>You have no active loan requests.</p>
                </div>
            <?php } ?>
        </div>
    </div>

</div>
</div>

<script>
const platformRate = <?= $platform_rate ?>;

function calculateLoan() {
    const amountInput = document.querySelector('input[name="amount"]');
    const termSelect = document.getElementById('loanTerm');
    const calcBox = document.getElementById('loanCalculation');
    
    let amount = parseFloat(amountInput.value);
    let term = parseInt(termSelect.value);
    
    if(!amount || isNaN(amount) || !term || isNaN(term)) {
        calcBox.style.display = 'none';
        return;
    }
    
    // Monthly Simple Interest Formula
    let interest = amount * (platformRate / 100) * term;
    let total = amount + interest;
    
    document.getElementById('calcPrincipal').innerText = 'GHS ' + amount.toFixed(2);
    document.getElementById('calcInterest').innerText = 'GHS ' + interest.toFixed(2);
    document.getElementById('calcTotal').innerText = 'GHS ' + total.toFixed(2);
    
    calcBox.style.display = 'block';
}

document.querySelector('input[name="amount"]').addEventListener('input', calculateLoan);

function toggleOther(select){
    let field = document.getElementById("otherPurpose");
    field.style.display = (select.value === "Other") ? "block" : "none";
    if(select.value === "Other"){
        field.setAttribute("required", "true");
    } else {
        field.removeAttribute("required");
    }
}
</script>

</body>
</html>