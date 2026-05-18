<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    /* =========================
       <i class='fas fa-lock'></i> ADMIN LOGIN
    ========================== */
    if ($email == "esuakohbernard4@gmail.com" && $password == "admin123") {

        $_SESSION['admin'] = true;
        $_SESSION['name'] = "Admin";
        $_SESSION['role'] = "admin";

        header("Location: admin_dashboard.php");
        exit();
    }

    /* =========================
       <i class='fas fa-user'></i> USER LOGIN (SECURE)
    ========================== */

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $user = $result->fetch_assoc();

        /* =========================
           PASSWORD CHECK
        ========================== */
        if (password_verify($password, $user['password'])) {

            /* =========================
               AUTO VERIFY
            ========================== */
            $conn->query("UPDATE users SET is_verified = 1 WHERE id = '{$user['id']}'");

            /* =========================
               SESSION SETUP
            ========================== */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            /* =========================
               ROLE-BASED REDIRECT (FIXED)
            ========================== */

            if ($user['role'] === 'farmer') {
                header("Location: dashboard.php");
                exit();
            }

            elseif ($user['role'] === 'buyer') {
                header("Location: buyer_dashboard.php");
                exit();
            }

            elseif ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            }

            else {
                header("Location: login.php?error=invalid_role");
                exit();
            }

        } else {
            $message = "<i class='fas fa-times-circle'></i> Wrong password";
        }

    } else {
        $message = "<i class='fas fa-times-circle'></i> User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login - Agricultural System</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(to right, #e8f5e9, #c8e6c9);
    font-family: Arial;
}

.login-box {
    margin-top: 80px;
}

.card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

h2 {
    color: #1b5e20;
    font-weight: bold;
}

.btn-custom {
    background: #2e7d32;
    color: white;
}

.btn-custom:hover {
    background: #1b5e20;
    color: white;
}

.footer {
    text-align: center;
    margin-top: 30px;
    color: #2e7d32;
}
</style>

</head>

<body>

<div class="container login-box">
<div class="row justify-content-center">

<div class="col-md-5">

<div class="card p-4">

<h2 class="text-center">🌿 Login</h2>

<p class="text-center text-muted">
Access your Agricultural Market System
</p>

<?php if ($message != "") { ?>
<div class="alert alert-danger">
    <?= $message ?>
</div>
<?php } ?>

<form method="POST">

<input type="email" name="email" class="form-control mb-3" placeholder="Email Address" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

<button type="submit" name="login" class="btn btn-custom w-100">
Login
</button>

</form>

<hr>

<p class="text-center">
Don't have an account?
<a href="register.php" style="color:#2e7d32;">Register here</a>
</p>

</div>

</div>

</div>

</div>

<div class="footer">
    <small>© 2026 Agricultural Market & Decision Support System</small>
</div>z

</body>
</html>