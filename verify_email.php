<?php
include 'db.php';
session_start();

// If no pending registration, redirect back
if (!isset($_SESSION['pending_reg'])) {
    header("Location: register.php");
    exit();
}

$pending = $_SESSION['pending_reg'];
$message = "";
$msg_type = "";

// Handle code verification
if (isset($_POST['verify_code'])) {
    $entered_code = trim($_POST['code']);

    // Check if code expired
    if (time() > $pending['expires']) {
        $message = "<i class='fas fa-clock'></i> Verification code has expired. Please register again.";
        $msg_type = "error";
        unset($_SESSION['pending_reg']);
    }
    // Check if code matches
    elseif ($entered_code !== $pending['code']) {
        $message = "<i class='fas fa-times-circle'></i> Invalid verification code. Please check your email and try again.";
        $msg_type = "error";
    }
    // Code is correct — complete registration
    else {
        $name = $conn->real_escape_string($pending['name']);
        $email = $conn->real_escape_string($pending['email']);
        $phone = $conn->real_escape_string($pending['phone']);
        $location = $conn->real_escape_string($pending['location']);
        $farm_size = $conn->real_escape_string($pending['farm_size']);
        $role = $conn->real_escape_string($pending['role']);
        $hashed_password = password_hash($pending['password'], PASSWORD_DEFAULT);

        // Insert into users
        $sql = "INSERT INTO users 
        (name, email, phone, location, password, role, is_verified)
        VALUES 
        ('$name', '$email', '$phone', '$location', '$hashed_password', '$role', 1)";

        $insert = $conn->query($sql);

        if (!$insert) {
            $message = "<i class='fas fa-times-circle'></i> Registration failed: " . $conn->error;
            $msg_type = "error";
        } else {
            $user_id = $conn->insert_id;

            // Insert into farmers table if farmer
            if ($role == "farmer") {
                $sql2 = "INSERT INTO farmers 
                (name, phone, location, role, farm_size, email, password)
                VALUES 
                ('$name', '$phone', '$location', '$role', '$farm_size', '$email', '$hashed_password')";
                $conn->query($sql2);
            }

            // AUTO LOGIN SESSION
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $pending['name'];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $pending['email'];

            // Clear pending registration
            unset($_SESSION['pending_reg']);

            // Redirect to dashboard
            if ($role === 'farmer') {
                header("Location: dashboard.php");
            } elseif ($role === 'buyer') {
                header("Location: buyer_dashboard.php");
            } else {
                header("Location: login.php");
            }
            exit();
        }
    }
}

// Handle resend code
if (isset($_POST['resend_code'])) {
    // Generate new code
    $new_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['pending_reg']['code'] = $new_code;
    $_SESSION['pending_reg']['expires'] = time() + 600;
    $pending = $_SESSION['pending_reg'];

    include 'mail_config.php';
    $sent = sendVerificationEmail($pending['email'], $pending['name'], $new_code);

    if ($sent) {
        $message = "<i class='fas fa-check-circle'></i> A new verification code has been sent to your email!";
        $msg_type = "success";
    } else {
        $message = "<i class='fas fa-exclamation-triangle'></i> Could not resend the code. Please try again.";
        $msg_type = "error";
    }
}

$masked_email = preg_replace('/(?<=.{2}).(?=.*@)/', '*', $pending['email']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify Email</title>
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

.verify-card {
    background: white;
    border-radius: 16px;
    padding: 35px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.verify-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #ff8f00, #ffa726);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    color: white;
    font-size: 28px;
}

.verify-card h2 {
    font-size: 22px;
    font-weight: 700;
    color: #333;
    margin: 0 0 8px;
}

.verify-card .subtitle {
    color: #888;
    font-size: 13px;
    margin-bottom: 25px;
}

.email-display {
    background: #f5f5f5;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #555;
    margin-bottom: 20px;
    display: inline-block;
}

.code-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-bottom: 20px;
}

.code-inputs input {
    width: 50px;
    height: 55px;
    text-align: center;
    font-size: 22px;
    font-weight: 700;
    border: 2px solid #ddd;
    border-radius: 10px;
    background: #fafafa;
    transition: 0.2s;
    color: #333;
}

.code-inputs input:focus {
    border-color: #43a047;
    box-shadow: 0 0 0 3px rgba(67,160,71,0.15);
    background: white;
    outline: none;
}

.btn-verify {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 13px;
    width: 100%;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: 0.2s;
}

.btn-verify:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46,125,50,0.3);
}

.resend-section {
    margin-top: 20px;
    font-size: 13px;
    color: #888;
}

.btn-resend {
    background: none;
    border: none;
    color: #1565c0;
    font-weight: 600;
    cursor: pointer;
    font-size: 13px;
    text-decoration: underline;
}

.btn-resend:hover { color: #0d47a1; }

.alert-msg {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: 500;
}

.alert-msg.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
.alert-msg.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

.timer {
    font-size: 12px;
    color: #e65100;
    font-weight: 600;
    margin-top: 10px;
}

.back-link {
    display: block;
    margin-top: 15px;
    font-size: 13px;
    color: #999;
    text-decoration: none;
}

.back-link:hover { color: #555; }
</style>
</head>

<body>

<div class="verify-card">

    <div class="verify-icon"><i class="fas fa-envelope-open-text"></i></div>

    <h2>Check Your Email</h2>
    <p class="subtitle">We sent a 6-digit verification code to</p>

    <div class="email-display"><i class="fas fa-envelope"></i> <?= $masked_email ?></div>

    <?php if($message): ?>
        <div class="alert-msg <?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="code" id="full_code">

        <div class="code-inputs">
            <input type="text" maxlength="1" class="code-digit" data-index="0" inputmode="numeric" autofocus>
            <input type="text" maxlength="1" class="code-digit" data-index="1" inputmode="numeric">
            <input type="text" maxlength="1" class="code-digit" data-index="2" inputmode="numeric">
            <input type="text" maxlength="1" class="code-digit" data-index="3" inputmode="numeric">
            <input type="text" maxlength="1" class="code-digit" data-index="4" inputmode="numeric">
            <input type="text" maxlength="1" class="code-digit" data-index="5" inputmode="numeric">
        </div>

        <button type="submit" name="verify_code" class="btn-verify">
            <i class="fas fa-check-circle"></i> Verify & Complete Registration
        </button>
    </form>

    <div class="timer" id="countdown"></div>

    <div class="resend-section">
        Didn't receive the code?
        <form method="POST" style="display: inline;">
            <button type="submit" name="resend_code" class="btn-resend">Resend Code</button>
        </form>
    </div>

    <a href="register.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Registration</a>

</div>

<script>
// Auto-focus and auto-advance code inputs
const digits = document.querySelectorAll('.code-digit');
const fullCode = document.getElementById('full_code');

digits.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        // Only allow digits
        input.value = input.value.replace(/[^0-9]/g, '');
        
        if (input.value && index < 5) {
            digits[index + 1].focus();
        }
        updateFullCode();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !input.value && index > 0) {
            digits[index - 1].focus();
        }
    });

    // Handle paste
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
        paste.split('').forEach((char, i) => {
            if (digits[i]) {
                digits[i].value = char;
            }
        });
        if (paste.length > 0) {
            digits[Math.min(paste.length, 5)].focus();
        }
        updateFullCode();
    });
});

function updateFullCode() {
    let code = '';
    digits.forEach(d => code += d.value);
    fullCode.value = code;
}

// Countdown timer
const expires = <?= $pending['expires'] ?>;
function updateTimer() {
    const now = Math.floor(Date.now() / 1000);
    const remaining = expires - now;
    const el = document.getElementById('countdown');
    
    if (remaining <= 0) {
        el.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Code expired. Please resend.';
        el.style.color = '#c62828';
        return;
    }
    
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    el.innerHTML = '<i class="fas fa-clock"></i> Code expires in ' + mins + ':' + String(secs).padStart(2, '0');
    setTimeout(updateTimer, 1000);
}
updateTimer();
</script>

</body>
</html>
