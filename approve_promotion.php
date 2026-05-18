<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $promo_q = $conn->query("SELECT user_id, product_id FROM promotions WHERE id='$id'");
    if ($promo_q && $promo = $promo_q->fetch_assoc()) {
        $farmer_id = $promo['user_id'];
        $product_id = $promo['product_id'];
        
        // Approve promotion
        $conn->query("UPDATE promotions SET status='approved' WHERE id='$id'");
        
        // Mark product as promoted
        $conn->query("UPDATE products SET is_promoted=1, promoted_at=datetime('now') WHERE id='$product_id' AND user_id='$farmer_id'");
        
        // Notify the farmer
        $msg = "<i class=\"fas fa-star\"></i> Your promotion payment has been approved! Your product is now featured at the top of the marketplace.";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$farmer_id', '$msg')");
        
        // Update the admin notification: replace buttons with "Approved" label
        $done_label = "<div><span style=\"background:#e8f5e9; color:#2e7d32; padding:4px 14px; border-radius:20px; font-weight:600; font-size:12px;\"><i class=\"fas fa-check-circle\"></i> Approved</span></div>";
        $search = "%approve_promotion.php?id=$id%";
        $notifs = $conn->query("SELECT id, message FROM notifications WHERE user_id=0 AND message LIKE '$search'");
        if ($notifs && $notifs->num_rows > 0) {
            while ($n = $notifs->fetch_assoc()) {
                $old_msg = $n['message'];
                $pos = strpos($old_msg, '</div><div>');
                if ($pos !== false) {
                    $new_msg = substr($old_msg, 0, $pos + 6) . $done_label;
                } else {
                    $new_msg = $old_msg . $done_label;
                }
                $upd = $conn->prepare("UPDATE notifications SET message=? WHERE id=?");
                $upd->bind_param("si", $new_msg, $n['id']);
                $upd->execute();
            }
        }
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'notifications.php') !== false) {
    header("Location: notifications.php");
} else {
    header("Location: admin_dashboard.php?tab=promos");
}
exit();
?>
