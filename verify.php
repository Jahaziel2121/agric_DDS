<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";

/* =========================
   VERIFY OTP
========================= */
if (isset($_POST['verify'])) {

    $otp_input = trim($_POST['otp']);

    $stmt = $conn->prepare("
        SELECT id FROM users 
        WHERE email = ? 
        AND otp = ? 
        AND is_verified = 0
    ");

    $stmt->bind_param("ss", $email, $otp_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $update = $conn->prepare("
            UPDATE users 
            SET is_verified = 1, otp = NULL 
            WHERE email = ?
        ");

        $update->bind_param("s", $email);
        $update->execute();

        unset($_SESSION['email']);

        echo "<script>
            alert('WhatsApp OTP verified successfully!');
            window.location='login.php';
        </script>";
        exit();

    } else {
        $message = "<i class='fas fa-times-circle'></i> Invalid OTP. Check your WhatsApp message.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>WhatsApp OTP Verification</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #e8f5e9;
    font-family: Arial;
}
.card {
    margin-top: 100px;
    padding: 25px;
    border-radius: 12px;
}
</style>
</head>

<body>

<div class="container">
<div class="row justify-content-center">
<div class="col-md-5">

<div class="card text-center">

<h3>📲 WhatsApp OTP Verification</h3>

<p>Enter the code sent to your WhatsApp number</p>

<?php if ($message != "") { ?>
<div class="alert alert-danger"><?= $message ?></div>
<?php } ?>

<form method="POST">

<input type="text" name="otp" class="form-control mb-3" placeholder="Enter OTP" required>

<button class="btn btn-success w-100" name="verify">
Verify Account
</button>

</form>

</div>

</div>
</div>
</div>

</body>
</html>