<?php
// dashboard_cskh.php
session_start();
if (!isset($_SESSION['cskh'])) {
    header("Location: login_cskh.php");
    exit();
}
$cskh = $_SESSION['cskh'];

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lấy danh sách khách hàng đã chat với CSKH này
require_once "model/ChatModel.php";
require_once "model/Database.php";
$chatModel = new ChatModel();

// Debug: Kiểm tra kết nối database
$db_debug = Database::getInstance()->getConnection()->error ? "Lỗi kết nối: " . Database::getInstance()->getConnection()->error : "Kết nối DB OK";

// Debug: Kiểm tra ID CSKH
$cskh_id_debug = isset($cskh['id']) ? $cskh['id'] : "Không có ID CSKH";

// Debug: Kiểm tra bảng chat_messages có tồn tại không
$table_exists = false;
$table_structure = [];
$conn = Database::getInstance()->getConnection();
$result = $conn->query("SHOW TABLES LIKE 'chat_messages'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
    $structure_result = $conn->query("DESCRIBE chat_messages");
    if ($structure_result) {
        while ($row = $structure_result->fetch_assoc()) {
            $table_structure[] = $row;
        }
    }
}

// Debug: Kiểm tra tin nhắn trong DB
$debug_messages = [];
if ($table_exists) {
    $debug_messages = $chatModel->debugCustomers($cskh['id']);
}

// Lấy danh sách khách hàng
$customers = [];
if ($table_exists) {
    $customers = $chatModel->getCustomersByCskhId($cskh['id']);
}

// Lấy ID khách hàng đang chat (nếu có)
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : (count($customers) > 0 ? $customers[0]['id'] : null);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard CSKH - Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #007bff;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
        .navbar-text {
            color: rgba(255,255,255,0.8);
        }
        .logout-btn {
            color: white;
            background-color: rgba(255,255,255,0.2);
            border: none;
            border-radius: 4px;
            padding: 5px 15px;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 12px 15px;
        }
        .list-group-item:first-child {
            border-top: none;
        }
        .list-group-item.active {
            background-color: #e9f2ff;
            color: #007bff;
            border-color: #dee2e6;
            border-left: 3px solid #007bff;
        }
        .chat-box {
            height: 400px;
            overflow-y: auto;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
        }
        .message-bubble {
            max-width: 75%;
            padding: 10px 15px;
            border-radius: 18px;
            margin-bottom: 10px;
            position: relative;
        }
        .message-customer {
            background-color: #f1f1f1;
            border-bottom-left-radius: 5px;
            float: left;
            clear: both;
        }
        .message-cskh {
            background-color: #007bff;
            color: white;
            border-bottom-right-radius: 5px;
            float: right;
            clear: both;
        }
        .message-time {
            font-size: 0.75rem;
            margin-top: 5px;
            opacity: 0.7;
        }
        .debug-toggle {
            cursor: pointer;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .debug-info {
            display: none;
        }
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-headset mr-2"></i> Hệ thống CSKH
        </a>
        <div class="ml-auto d-flex align-items-center">
            <span class="navbar-text mr-3">
                Xin chào, <?php echo htmlspecialchars($cskh['username'] ?? 'CSKH'); ?>
            </span>
            <a href="view/logout_cskh.php" class="logout-btn">
                <i class="fas fa-sign-out-alt mr-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</nav>

<!-- Debug toggle -->
<div class="container">
    <p class="debug-toggle" onclick="toggleDebug()">
        <i class="fas fa-bug mr-1"></i> Thông tin debug <i class="fas fa-chevron-down ml-1"></i>
    </p>
    <div class="alert alert-info debug-info" id="debugInfo">
        <h5>Debug Info:</h5>
        <p>CSKH ID: <?php echo $cskh_id_debug; ?></p>
        <p>DB Status: <?php echo $db_debug; ?></p>
        <p>Bảng chat_messages tồn tại: <?php echo $table_exists ? 'Có' : 'Không'; ?></p>
        <?php if ($table_exists): ?>
            <p>Cấu trúc bảng chat_messages:</p>
            <pre><?php print_r($table_structure); ?></pre>
        <?php endif; ?>
        <p>Số lượng khách hàng: <?php echo count($customers); ?></p>
        <p>Debug Messages:</p>
        <pre><?php print_r($debug_messages); ?></pre>
    </div>
</div>

<!-- Main content -->
<div class="container">
    <div class="row">
        <!-- Customer list -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Danh sách khách hàng</span>
                    <span class="badge badge-primary"><?php echo count($customers); ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="input-group p-3">
                        <input type="text" class="form-control" placeholder="Tìm kiếm khách hàng...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if (empty($customers)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>Chưa có khách hàng nào chat với bạn.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($customers as $customer): ?>
                                <a href="?customer_id=<?php echo $customer['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $customer['id'] == $customer_id ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-<?php echo $customer['is_online'] ? 'success' : 'secondary'; ?> mr-2">&bull;</span>
                                            <?php echo htmlspecialchars($customer['name'] ?? 'Khách hàng #' . $customer['id']); ?>
                                        </div>
                                        <?php if (isset($customer['unread_count']) && $customer['unread_count'] > 0): ?>
                                            <span class="badge badge-danger"><?php echo $customer['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars($customer['last_message'] ?? 'Chưa có tin nhắn'); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Chat box -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <?php if ($customer_id): ?>
                        Chat với Khách hàng #<?php echo $customer_id; ?>
                    <?php else: ?>
                        Chọn một khách hàng để bắt đầu chat
                    <?php endif; ?>
                </div>
                <div class="card-body chat-box" id="chatBox">
                    <?php if (!$customer_id): ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h5>Chọn một khách hàng để bắt đầu chat</h5>
                            <p class="text-muted">Danh sách khách hàng hiển thị ở bên trái</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Đang tải...</span>
                            </div>
                            <p class="mt-2">Đang tải tin nhắn...</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($customer_id): ?>
                    <div class="card-footer">
                        <div class="input-group">
                            <input type="text" id="chatInput" class="form-control" placeholder="Nhập tin nhắn...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" onclick="sendMessage()">
                                    <i class="fas fa-paper-plane mr-1"></i> Gửi
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle debug info
function toggleDebug() {
    const debugInfo = document.getElementById('debugInfo');
    if (debugInfo.style.display === 'block') {
        debugInfo.style.display = 'none';
    } else {
        debugInfo.style.display = 'block';
    }
}

const cs_kh_id = <?php echo $cskh['id']; ?>;
let currentCustomerId = <?php echo $customer_id ? $customer_id : 'null'; ?>;

// Tải tin nhắn chat
function loadChat() {
    if (!currentCustomerId) return;
    
    fetch("controller/ChatController.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=getMessages&user1=" + cs_kh_id + "&user2=" + currentCustomerId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const chatBox = document.getElementById("chatBox");
            let html = "";
            
            if (data.messages.length === 0) {
                html = `<div class="empty-state">
                    <i class="far fa-comment-dots"></i>
                    <p class="text-muted">Chưa có tin nhắn nào với khách hàng này.</p>
                </div>`;
            } else {
                data.messages.forEach(msg => {
                    const messageTime = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    if (parseInt(msg.sender_id) === currentCustomerId) {
                        html += `<div class="message-bubble message-customer">
                            <div>${msg.message}</div>
                            <small class="message-time text-muted">${messageTime}</small>
                        </div>`;
                    } else {
                        html += `<div class="message-bubble message-cskh">
                            <div>${msg.message}</div>
                            <small class="message-time text-white-50">${messageTime}</small>
                        </div>`;
                    }
                });
                // Thêm div để clear float
                html += '<div style="clear: both;"></div>';
            }
            
            chatBox.innerHTML = html;
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    })
    .catch(error => {
        console.error("Lỗi:", error);
        document.getElementById("chatBox").innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-circle fa-3x mb-3 text-danger"></i>
                <p class="text-danger">Có lỗi xảy ra khi tải tin nhắn.</p>
                <button class="btn btn-outline-primary mt-2" onclick="loadChat()">Thử lại</button>
            </div>
        `;
    });
}

// Gửi tin nhắn
function sendMessage() {
    if (!currentCustomerId) return;
    
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    if (message === "") return;
    
    // Xóa nội dung input
    chatInput.value = "";
    
    fetch("controller/ChatController.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=sendMessage&sender_id=" + cs_kh_id + "&receiver_id=" + currentCustomerId + "&message=" + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadChat(); // Tải lại tin nhắn sau khi gửi thành công
        } else {
            alert("Gửi tin nhắn thất bại: " + (data.message || "Lỗi không xác định"));
        }
    })
    .catch(error => {
        console.error("Lỗi:", error);
        alert("Lỗi gửi tin nhắn: " + error.message);
    });
}

// Bắt sự kiện nhấn Enter để gửi tin nhắn
document.getElementById('chatInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

// Tải tin nhắn khi trang được tải
if (currentCustomerId) {
    loadChat();
}

// Tải tin nhắn mỗi 5 giây
setInterval(() => {
    if (currentCustomerId) {
        loadChat();
    }
}, 5000);
</script>
</body>
</html>
