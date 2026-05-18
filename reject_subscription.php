<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sub_q = $conn->query("SELECT user_id FROM subscriptions WHERE id='$id'");
    if ($sub_q && $sub = $sub_q->fetch_assoc()) {
        $farmer_id = $sub['user_id'];
        
        // Update subscription status
        $conn->query("UPDATE subscriptions SET status='rejected' WHERE id='$id'");
        
        // Notify the farmer
        $msg = "<i class=\"fas fa-times-circle\"></i> Your subscription payment was rejected. The transaction ID could not be verified. Please try again or contact support.";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$farmer_id', '$msg')");
        
        // Update the admin notification: replace buttons with "Rejected" label
        $done_label = "<div><span style=\"background:#ffebee; color:#c62828; padding:4px 14px; border-radius:20px; font-weight:600; font-size:12px;\"><i class=\"fas fa-times-circle\"></i> Rejected</span></div>";
        $search = "%approve_subscription.php?id=$id%";
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
    header("Location: admin_dashboard.php?tab=subs");
}
exit();
?>
