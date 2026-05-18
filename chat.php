<?php
session_start();
include 'db.php';
include 'back_button.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$active_contact_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$active_contact_name = "Select a conversation";

// Fetch all conversations (unique contacts)
$contacts_sql = "
    SELECT u.id, u.name, u.role, 
           (SELECT message FROM messages 
            WHERE (sender_id = u.id AND receiver_id = '$user_id') 
               OR (sender_id = '$user_id' AND receiver_id = u.id) 
            ORDER BY id DESC LIMIT 1) as last_msg,
           (SELECT created_at FROM messages 
            WHERE (sender_id = u.id AND receiver_id = '$user_id') 
               OR (sender_id = '$user_id' AND receiver_id = u.id) 
            ORDER BY id DESC LIMIT 1) as last_time,
           (SELECT COUNT(*) FROM messages 
            WHERE sender_id = u.id AND receiver_id = '$user_id' AND is_read = 0) as unread_count
    FROM users u
    WHERE u.id IN (
        SELECT sender_id FROM messages WHERE receiver_id = '$user_id'
        UNION
        SELECT receiver_id FROM messages WHERE sender_id = '$user_id'
    )
";

// If a new chat is initiated that isn't in history yet
if ($active_contact_id > 0) {
    $check_contact = $conn->query("SELECT id, name, role FROM users WHERE id = '$active_contact_id'");
    if ($check_contact && $cc = $check_contact->fetch_assoc()) {
        $active_contact_name = $cc['name'];
        // Ensure this user is in the contacts list query
        $contacts_sql .= " UNION SELECT {$cc['id']}, '{$cc['name']}', '{$cc['role']}', NULL, NULL, 0 WHERE {$cc['id']} NOT IN (SELECT sender_id FROM messages WHERE receiver_id = '$user_id' UNION SELECT receiver_id FROM messages WHERE sender_id = '$user_id')";
    }
}

$contacts_sql .= " ORDER BY last_time DESC NULLS LAST";
$contacts_result = $conn->query($contacts_sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; box-sizing: border-box; }
body { background: #f4f7f2; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

.chat-container {
    display: flex;
    flex: 1;
    max-width: 1200px;
    margin: 20px auto;
    width: 95%;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    height: calc(100vh - 120px);
}

/* SIDEBAR */
.chat-sidebar {
    width: 320px;
    border-right: 1px solid #eee;
    background: #fff;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    background: #1b5e20;
    color: white;
    font-weight: 600;
}

.contact-list {
    flex: 1;
    overflow-y: auto;
}

.contact-item {
    display: flex;
    padding: 15px 20px;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: 0.2s;
}

.contact-item:hover, .contact-item.active {
    background: #e8f5e9;
}

.contact-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #a5d6a7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 15px;
    flex-shrink: 0;
}

.contact-info { flex: 1; overflow: hidden; }
.contact-name { font-weight: 600; font-size: 15px; margin-bottom: 2px; color: #333; }
.contact-msg { font-size: 13px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.unread-badge { background: #ef4444; color: white; font-size: 11px; padding: 2px 6px; border-radius: 10px; font-weight: 700; float: right; margin-top: 5px; }

/* MAIN CHAT AREA */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f9fbf9;
}

.chat-header {
    padding: 15px 25px;
    background: white;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
}

.chat-messages {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.msg-bubble {
    max-width: 70%;
    padding: 12px 18px;
    border-radius: 20px;
    font-size: 14.5px;
    line-height: 1.5;
    position: relative;
    word-wrap: break-word;
}

.msg-bubble.incoming {
    background: white;
    color: #333;
    align-self: flex-start;
    border-bottom-left-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.03);
}

.msg-bubble.outgoing {
    background: #2e7d32;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.msg-time {
    font-size: 11px;
    margin-top: 5px;
    text-align: right;
    opacity: 0.7;
}

.chat-input-area {
    padding: 20px;
    background: white;
    border-top: 1px solid #eee;
}

.chat-form {
    display: flex;
    gap: 15px;
}

.chat-form input {
    flex: 1;
    padding: 15px 20px;
    border: 1px solid #ddd;
    border-radius: 30px;
    outline: none;
    font-size: 15px;
    background: #f5f5f5;
}

.chat-form input:focus { background: white; border-color: #81c784; }

.chat-form button {
    background: #ff8f00;
    color: white;
    border: none;
    padding: 0 25px;
    border-radius: 30px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

.chat-form button:hover { background: #e65100; }

.empty-chat {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: #aaa;
}

@media (max-width: 768px) {
    .chat-container { height: calc(100vh - 60px); margin: 0; width: 100%; border-radius: 0; }
    .chat-sidebar { width: <?php echo $active_contact_id ? '0' : '100%'; ?>; display: <?php echo $active_contact_id ? 'none' : 'flex'; ?>; }
    .chat-main { display: <?php echo $active_contact_id ? 'flex' : 'none'; ?>; }
}
</style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="chat-container">
    
    <!-- SIDEBAR -->
    <div class="chat-sidebar">
        <div class="sidebar-header"><i class='fas fa-comment-dots'></i> Messages</div>
        <div class="contact-list">
            <?php 
            if ($contacts_result && $contacts_result->num_rows > 0) {
                while ($c = $contacts_result->fetch_assoc()) {
                    $active_class = ($c['id'] == $active_contact_id) ? 'active' : '';
                    $unread = $c['unread_count'] > 0 ? "<span class='unread-badge'>{$c['unread_count']}</span>" : '';
                    $last_msg = $c['last_msg'] ?: 'No messages yet';
                    
                    echo "
                    <a href='chat.php?user_id={$c['id']}' class='contact-item {$active_class}'>
                        <div class='contact-avatar'><i class='fas fa-user'></i></div>
                        <div class='contact-info'>
                            <div class='contact-name'>{$c['name']}</div>
                            <div class='contact-msg'>{$last_msg}</div>
                        </div>
                        {$unread}
                    </a>";
                }
            } else {
                echo "<div style='padding:20px;text-align:center;color:#999;font-size:14px;'>No conversations yet.</div>";
            }
            ?>
        </div>
    </div>

    <!-- MAIN CHAT -->
    <div class="chat-main">
        <?php if ($active_contact_id > 0): ?>
            
            <div class="chat-header">
                <?php if(strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false): ?>
                    <a href="chat.php" style="margin-right:15px;text-decoration:none;font-size:20px;"><i class='fas fa-arrow-left'></i></a>
                <?php endif; ?>
                <div class="contact-avatar" style="width:40px;height:40px;font-size:18px;margin-right:10px;"><i class='fas fa-user'></i></div>
                <div>
                    <h5 style="margin:0;font-size:16px;font-weight:700;color:#1b5e20;"><?= htmlspecialchars($active_contact_name) ?></h5>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages">
                <!-- Messages will be loaded here via AJAX -->
                <div style="text-align:center;color:#ccc;margin-top:20px;">Loading messages...</div>
            </div>

            <div class="chat-input-area">
                <form class="chat-form" id="chat-form">
                    <input type="text" id="message-input" placeholder="Type a message..." required autocomplete="off">
                    <button type="submit">Send</button>
                </form>
            </div>

        <?php else: ?>
            <div class="empty-chat">
                <div style="font-size:60px;margin-bottom:10px;"><i class='fas fa-comment-dots'></i></div>
                <h3>Your Messages</h3>
                <p>Select a conversation from the left to start chatting.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if ($active_contact_id > 0): ?>
<script>
    const activeContactId = <?= $active_contact_id ?>;
    const chatContainer = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    
    let lastScrollHeight = 0;
    let isUserScrolling = false;

    // Check if user has scrolled up
    chatContainer.addEventListener('scroll', () => {
        isUserScrolling = chatContainer.scrollTop + chatContainer.clientHeight < chatContainer.scrollHeight - 20;
    });

    function fetchMessages() {
        fetch(`fetch_messages.php?contact_id=${activeContactId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    chatContainer.innerHTML = '';
                    if (data.messages.length === 0) {
                        chatContainer.innerHTML = '<div style="text-align:center;color:#ccc;margin-top:20px;">Say hello! <i class="fas fa-hand-sparkles"></i></div>';
                        return;
                    }
                    
                    data.messages.forEach(msg => {
                        const type = msg.is_mine ? 'outgoing' : 'incoming';
                        const html = `
                            <div class="msg-bubble ${type}">
                                ${msg.message}
                                <div class="msg-time">${msg.time}</div>
                            </div>
                        `;
                        chatContainer.insertAdjacentHTML('beforeend', html);
                    });

                    // Auto scroll to bottom if not manually scrolling
                    if (!isUserScrolling) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                }
            });
    }

    // Handle send message
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;

        // Optimistic UI update
        const timeNow = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        chatContainer.insertAdjacentHTML('beforeend', `
            <div class="msg-bubble outgoing" style="opacity:0.7;">
                ${text}
                <div class="msg-time">${timeNow}</div>
            </div>
        `);
        messageInput.value = '';
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // Send to server
        const formData = new URLSearchParams();
        formData.append('receiver_id', activeContactId);
        formData.append('message', text);

        fetch('send_message.php', {
            method: 'POST',
            body: formData,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(() => {
            fetchMessages(); // Refresh actual state
        });
    });

    // Initial fetch & Polling
    fetchMessages();
    setInterval(fetchMessages, 3000);

</script>
<?php endif; ?>

</body>
</html>
