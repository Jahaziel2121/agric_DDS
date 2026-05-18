<?php
session_start();
include 'db.php';

if(!isset($_SESSION['temp_user_id'])){
    header("Location: login.php");
    exit();
}

$message = "";

if(isset($_POST['verify_otp'])){

    $otp = $_POST['otp'];
    $user_id = $_SESSION['temp_user_id'];

    $result = $conn->query("
        SELECT * FROM users 
        WHERE id='$user_id' AND otp='$otp'
    ");

    if($result && $result->num_rows == 1){

        $user = $result->fetch_assoc();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // clear OTP
        $conn->query("UPDATE users SET otp=NULL WHERE id='$user_id'");

        unset($_SESSION['temp_user_id']);

        /* ROLE REDIRECT */
        if($user['role'] == 'farmer'){
            header("Location: dashboard.php");
        } elseif($user['role'] == 'buyer'){
            header("Location: buyer_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }

        exit();

    } else {
        $message = "<i class='fas fa-times-circle'></i> Invalid OTP";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>OTP Verification</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-4">

<h3 class="text-center">Enter OTP</h3>

<?php if($message) echo "<div class='alert alert-danger'>$message</div>"; ?>

<form method="POST">

<input type="text" name="otp" class="form-control mb-3" placeholder="Enter OTP" required>

<button class="btn btn-success w-100" name="verify_otp">
Verify
</button>

</form>

</div>
</div>
</div>

</body>
</html>