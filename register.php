<?php
include 'db.php';
session_start();

$message = "";
$msg_type = "";

if (isset($_POST['register'])) {

    // FORM DATA
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $farm_size = $_POST['farm_size'] ?? '';
    $password = $_POST['password'];
    $role = $_POST['role'];

    // CHECK IF USER EXISTS
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");

    if ($check && $check->num_rows > 0) {
        $message = "<i class='fas fa-times-circle'></i> Email already exists!";
        $msg_type = "error";
    } else {
        // Generate 6-digit verification code
        $verify_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store registration data in session for after verification
        $_SESSION['pending_reg'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'location' => $location,
            'farm_size' => $farm_size,
            'password' => $password,
            'role' => $role,
            'code' => $verify_code,
            'expires' => time() + 600 // 10 minutes
        ];

        // Send the email
        include 'mail_config.php';
        $sent = sendVerificationEmail($email, $name, $verify_code);

        if ($sent) {
            header("Location: verify_email.php");
            exit();
        } else {
            $message = "<i class='fas fa-exclamation-triangle'></i> Could not send verification email. Please try again.";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; }
body {
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.reg-card {
    background: white;
    border-radius: 16px;
    padding: 35px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.reg-header {
    text-align: center;
    margin-bottom: 25px;
}

.reg-header .icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: white;
    font-size: 24px;
}

.reg-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #1b5e20;
    margin: 0;
}

.reg-header p {
    color: #888;
    font-size: 13px;
    margin: 5px 0 0;
}

.form-control {
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 14px;
    background: #fafafa;
    transition: 0.2s;
}

.form-control:focus {
    border-color: #43a047;
    box-shadow: 0 0 0 3px rgba(67,160,71,0.1);
    background: white;
}

.form-label {
    font-weight: 600;
    font-size: 13px;
    color: #555;
    margin-bottom: 6px;
}

.btn-register {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 13px;
    width: 100%;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    margin-top: 10px;
    transition: 0.2s;
    cursor: pointer;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46,125,50,0.3);
}

.alert-msg {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
}

.alert-msg.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

.login-link {
    text-align: center;
    margin-top: 18px;
    font-size: 13px;
    color: #888;
}

.login-link a { color: #2e7d32; font-weight: 600; text-decoration: none; }
.login-link a:hover { text-decoration: underline; }

#farmField { transition: 0.3s; }
</style>
</head>

<body>

<div class="reg-card">

    <div class="reg-header">
        <div class="icon"><i class="fas fa-user-plus"></i></div>
        <h2>Create Account</h2>
        <p>Join the Agricultural Market System</p>
    </div>

    <?php if($message): ?>
        <div class="alert-msg <?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            <small style="color: #888; font-size: 11px;"><i class="fas fa-shield-alt"></i> A verification code will be sent to this email</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" placeholder="Enter your location" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" id="role" class="form-control" required onchange="toggleFarmFields()">
                <option value="">Select your role</option>
                <option value="farmer">Farmer</option>
                <option value="buyer">Buyer</option>
            </select>
        </div>

        <div id="farmField" class="mb-3">
            <label class="form-label">Farm Size</label>
            <input type="text" name="farm_size" class="form-control" placeholder="e.g. 5 acres">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Create a password" required>
        </div>

        <button class="btn-register" name="register">
            <i class="fas fa-envelope"></i> Register & Verify Email
        </button>

    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Sign In</a>
    </div>

</div>

<script>
function toggleFarmFields() {
    var role = document.getElementById("role").value;
    var farmField = document.getElementById("farmField");
    farmField.style.display = (role === "buyer") ? "none" : "block";
}
</script>

</body>
</html>