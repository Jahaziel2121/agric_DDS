<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $location = $conn->real_escape_string(trim($_POST['location']));
    
    $update_sql = "UPDATE users SET name='$name', phone='$phone', location='$location'";
    
    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = 'uploads/profiles/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $update_sql .= ", profile_picture='$new_filename'";
                $_SESSION['profile_picture'] = $new_filename; // Update session
            } else {
                $message = "❌ Error uploading profile picture.";
                $msg_type = "error";
            }
        } else {
            $message = "❌ Invalid file type. Only JPG, PNG, and GIF are allowed.";
            $msg_type = "error";
        }
    }
    
    $update_sql .= " WHERE id='$user_id'";
    
    if (!$message) {
        if ($conn->query($update_sql)) {
            $_SESSION['name'] = $name; // Update session name
            $message = "✅ Profile updated successfully!";
            $msg_type = "success";
        } else {
            $message = "❌ Error updating profile: " . $conn->error;
            $msg_type = "error";
        }
    }
}

// Fetch current user details
$user_q = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = $user_q->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { font-family: 'Inter', sans-serif; }
body { background: #f4f7f2; color: #333; }

.page-header {
    background: linear-gradient(135deg, #1b5e20, #2e7d32);
    color: white;
    padding: 30px 20px;
    text-align: center;
    border-radius: 0 0 20px 20px;
    margin-bottom: 30px;
}

.profile-container {
    max-width: 600px;
    margin: 0 auto 40px auto;
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.profile-pic-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 20px;
    display: block;
    background: #e8f5e9;
    border: 3px solid #43a047;
    position: relative;
}

.pic-wrapper {
    text-align: center;
    position: relative;
    width: 120px;
    margin: 0 auto 20px;
}

.pic-upload-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #ff8f00;
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: 0.2s;
}

.pic-upload-btn:hover { background: #e65100; transform: scale(1.05); }

.form-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
}

.form-control {
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background: #f9f9f9;
}

.form-control:focus {
    border-color: #43a047;
    box-shadow: 0 0 0 3px rgba(67, 160, 71, 0.1);
    background: white;
}

.btn-save {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    margin-top: 15px;
    transition: 0.2s;
}

.btn-save:hover { background: #1b5e20; transform: translateY(-2px); }

.alert-msg {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
    text-align: center;
}

.alert-msg.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.alert-msg.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

.btn-logout {
    display: block;
    background: transparent;
    color: #c62828;
    border: 2px solid #ffcdd2;
    padding: 12px;
    width: 100%;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    margin-top: 15px;
    text-align: center;
    text-decoration: none;
    transition: 0.2s;
}

.btn-logout:hover {
    background: #ffebee;
    color: #b71c1c;
    border-color: #ef9a9a;
}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="page-header">
    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
    <p style="margin:0; opacity:0.8;">Update your personal details and profile picture</p>
</div>

<div class="profile-container">

    <?php if ($message): ?>
        <div class="alert-msg <?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="profile.php" enctype="multipart/form-data">
        
        <div class="pic-wrapper">
            <?php 
                $pic_src = !empty($user['profile_picture']) ? "uploads/profiles/" . htmlspecialchars($user['profile_picture']) : "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=a5d6a7&color=1b5e20";
            ?>
            <img src="<?= $pic_src ?>" id="preview_img" class="profile-pic-preview">
            <label for="profile_upload" class="pic-upload-btn" title="Change Picture">
                <i class="fas fa-camera"></i>
            </label>
            <input type="file" id="profile_upload" name="profile_picture" accept="image/*" style="display: none;" onchange="previewImage(event)">
        </div>

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address (Read-only)</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background: #e9ecef; cursor: not-allowed;">
            <small style="color: #888;">Email cannot be changed.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($user['location'] ?? '') ?>">
        </div>

        <button type="submit" name="update_profile" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
    </form>
    
    <hr style="margin: 30px 0; border-color: #eee;">
    
    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Log Out Securely</a>

</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('preview_img');
        output.src = reader.result;
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

</body>
</html>
