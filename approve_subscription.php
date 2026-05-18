<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Fetch subscription details
    $sub_q = $conn->query("SELECT user_id FROM subscriptions WHERE id='$id'");
    if ($sub_q && $sub = $sub_q->fetch_assoc()) {
        $farmer_id = $sub['user_id'];
        
        // Update subscription status
        $conn->query("UPDATE subscriptions SET status='approved' WHERE id='$id'");
        
        // Activate the farmer
        $conn->query("UPDATE users SET is_subscribed=1, subscribed_at=datetime('now') WHERE id='$farmer_id'");
        
        // Notify the farmer
        $msg = "<i class=\"fas fa-check-circle\"></i> Your subscription payment has been approved! Your account is now fully activated.";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$farmer_id', '$msg')");
        
        // Update the admin notification: replace buttons with "Approved" label
        $done_label = "<div><span style=\"background:#e8f5e9; color:#2e7d32; padding:4px 14px; border-radius:20px; font-weight:600; font-size:12px;\"><i class=\"fas fa-check-circle\"></i> Approved</span></div>";
        $stmt = $conn->prepare("UPDATE notifications SET message = SUBSTR(message, 1, INSTR(message, '</a></div>') -1) || ? WHERE user_id=0 AND message LIKE ?");
        // Simpler approach: find the notification by matching the subscription ID in the href and replace its content
        // Find notifications containing the approve link for this subscription
        $search = "%approve_subscription.php?id=$id%";
        $notifs = $conn->query("SELECT id, message FROM notifications WHERE user_id=0 AND message LIKE '$search'");
        if ($notifs && $notifs->num_rows > 0) {
            while ($n = $notifs->fetch_assoc()) {
                // Keep the text part (first div), replace the buttons div
                $old_msg = $n['message'];
                // Remove everything from the second <div> containing buttons
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
