<?php
// view/customer_chat.php
session_start();
// Ở đây chúng ta giả định khách hàng có ID, trong thực tế bạn sẽ lấy từ session sau khi đăng nhập
$customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 13; // Mặc định là 13 nếu chưa đăng nhập
$cskh_id = 1;  // CSKH mặc định có id = 1
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chat với CSKH</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .chat-box { 
            height: 300px; 
            overflow-y: auto; 
            border: 1px solid #ccc; 
            padding: 10px; 
            margin-bottom: 10px;
        }
        .message {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 5px;
            max-width: 80%;
        }
        .customer-message {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .cskh-message {
            background-color: #f1f0f0;
            margin-right: auto;
            text-align: left;
        }
        .message-time {
            font-size: 0.7rem;
            color: #aaa;
            margin-top: 3px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Chat với CSKH</h2>
    <div class="chat-box" id="chatBox">
        <!-- Lịch sử tin nhắn sẽ hiển thị ở đây -->
        <div class="text-center text-muted">
            <p>Đang tải tin nhắn...</p>
        </div>
    </div>
    <div class="input-group mt-2">
        <input type="text" id="chatInput" class="form-control" placeholder="Nhập tin nhắn...">
        <div class="input-group-append">
            <button class="btn btn-primary" onclick="sendMessage()">Gửi</button>
        </div>
    </div>
    <div class="mt-2 text-muted small">
        <p>ID của bạn: <?php echo $customer_id; ?> (Đang chat với CSKH #<?php echo $cskh_id; ?>)</p>
    </div>
</div>
<script>
const customer_id = <?php echo $customer_id; ?>;
const cskh_id = <?php echo $cskh_id; ?>;

function loadChat() {
    fetch("../controller/ChatController.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=getMessages&user1=" + customer_id + "&user2=" + cskh_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const chatBox = document.getElementById("chatBox");
            chatBox.innerHTML = "";
            
            if (data.messages.length === 0) {
                chatBox.innerHTML = "<div class='text-center text-muted'><p>Chưa có tin nhắn nào.</p></div>";
                return;
            }
            
            data.messages.forEach(function(msg) {
                const messageTime = new Date(msg.created_at || Date.now()).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const messageDiv = document.createElement("div");
                messageDiv.className = "message";
                
                if (parseInt(msg.sender_id) === customer_id) {
                    messageDiv.className += " customer-message";
                    messageDiv.innerHTML = `${msg.message}<div class="message-time">${messageTime}</div>`;
                } else {
                    messageDiv.className += " cskh-message";
                    messageDiv.innerHTML = `${msg.message}<div class="message-time">${messageTime}</div>`;
                }
                
                chatBox.appendChild(messageDiv);
            });
            
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    })
    .catch(error => {
        console.error("Lỗi:", error);
    });
}

function sendMessage() {
    const chatInput = document.getElementById("chatInput");
    const message = chatInput.value.trim();
    
    if (message === "") {
        alert("Vui lòng nhập tin nhắn!");
        return;
    }
    
    // Hiển thị tin nhắn ngay lập tức (optimistic UI)
    const chatBox = document.getElementById("chatBox");
    const messageTime = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    const messageDiv = document.createElement("div");
    messageDiv.className = "message customer-message";
    messageDiv.innerHTML = `${message}<div class="message-time">${messageTime}</div>`;
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
    
    // Xóa nội dung input
    chatInput.value = "";
    
    fetch("../controller/ChatController.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=sendMessage&sender_id=" + customer_id + "&receiver_id=" + cskh_id + "&message=" + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert("Gửi tin thất bại: " + (data.message || "Lỗi không xác định"));
            loadChat(); // Tải lại tin nhắn nếu gửi thất bại
        }
    })
    .catch(error => {
        console.error("Lỗi gửi tin nhắn:", error);
        alert("Lỗi gửi tin nhắn: " + error.message);
        loadChat(); // Tải lại tin nhắn nếu có lỗi
    });
}

// Bắt sự kiện nhấn Enter để gửi tin nhắn
document.getElementById("chatInput").addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        sendMessage();
    }
});

// Tải tin nhắn mỗi 5 giây
setInterval(loadChat, 5000);

// Tải tin nhắn khi trang được tải
document.addEventListener("DOMContentLoaded", function() {
    loadChat();
});
</script>
</body>
</html>
