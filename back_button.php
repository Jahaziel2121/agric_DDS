<?php
// Back button helper - call this on sub-pages
function back_button($url = null, $label = "Back") {
    if (!$url) {
        // Smart default based on role
        $role = $_SESSION['role'] ?? '';
        $is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
        
        if ($is_admin) {
            $url = 'admin_dashboard.php';
        } elseif ($role === 'farmer') {
            $url = 'dashboard.php';
        } elseif ($role === 'buyer') {
            $url = 'buyer_dashboard.php';
        } else {
            $url = 'index.php';
        }
    }
    
    echo "<div style='padding: 10px 20px;'>
        <a href='$url' style='
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #2e7d32;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            background: #e8f5e9;
            transition: all 0.2s ease;
        ' onmouseover=\"this.style.background='#c8e6c9'\" onmouseout=\"this.style.background='#e8f5e9'\">
            ← $label
        </a>
    </div>";
}
?>
