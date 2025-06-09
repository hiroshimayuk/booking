<?php
// model/ChatModel.php
require_once __DIR__ . "/Database.php";

class ChatModel {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    // Lấy danh sách khách hàng đã chat với CSKH
    public function getCustomersByCskhId($cskh_id) {
        // Kiểm tra kết nối
        if (!$this->conn) {
            error_log("Không có kết nối database");
            return [];
        }
        
        // Truy vấn đơn giản hơn để kiểm tra
        $sql = "SELECT DISTINCT 
                    sender_id as id
                FROM chat_messages 
                WHERE receiver_id = ? 
                UNION 
                SELECT DISTINCT 
                    receiver_id as id 
                FROM chat_messages 
                WHERE sender_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("ii", $cskh_id, $cskh_id);
        if (!$stmt->execute()) {
            error_log("Lỗi execute statement: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            // Bỏ qua nếu ID trùng với CSKH
            if ($row['id'] == $cskh_id) continue;
            
            // Thêm khách hàng với thông tin cơ bản
            $customers[] = [
                'id' => $row['id'],
                'name' => 'Khách hàng #' . $row['id'],
                'is_online' => 0,
                'last_message' => '',
                'unread_count' => 0
            ];
        }
        
        // Nếu có khách hàng, lấy thêm thông tin chi tiết
        foreach ($customers as &$customer) {
            try {
                // Lấy tin nhắn cuối cùng
                $lastMsgSql = "SELECT message FROM chat_messages 
                              WHERE (sender_id = ? AND receiver_id = ?) 
                              OR (sender_id = ? AND receiver_id = ?) 
                              ORDER BY created_at DESC LIMIT 1";
                $lastMsgStmt = $this->conn->prepare($lastMsgSql);
                if ($lastMsgStmt) {
                    $lastMsgStmt->bind_param("iiii", $customer['id'], $cskh_id, $cskh_id, $customer['id']);
                    $lastMsgStmt->execute();
                    $lastMsgResult = $lastMsgStmt->get_result();
                    $lastMsg = $lastMsgResult->fetch_assoc();
                    $customer['last_message'] = $lastMsg ? $lastMsg['message'] : '';
                    $lastMsgStmt->close();
                }
                
                // Đếm tin nhắn chưa đọc
                $unreadSql = "SELECT COUNT(*) as count FROM chat_messages 
                             WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
                $unreadStmt = $this->conn->prepare($unreadSql);
                if ($unreadStmt) {
                    $unreadStmt->bind_param("ii", $customer['id'], $cskh_id);
                    $unreadStmt->execute();
                    $unreadResult = $unreadStmt->get_result();
                    $unread = $unreadResult->fetch_assoc();
                    $customer['unread_count'] = $unread ? $unread['count'] : 0;
                    $unreadStmt->close();
                }
            } catch (Exception $e) {
                error_log("Lỗi khi lấy thông tin chi tiết: " . $e->getMessage());
            }
        }
        
        return $customers;
    }
    
    // Lưu tin nhắn mới
    public function saveMessage($sender_id, $receiver_id, $message) {
        $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Lỗi execute statement: " . $stmt->error);
            return false;
        }
    }
    
    // Lấy tin nhắn giữa hai người dùng
    public function getMessages($user1, $user2) {
        $sql = "SELECT * FROM chat_messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = ?) 
                ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        return $messages;
    }
    
    // Đánh dấu tin nhắn đã đọc
    public function markAsRead($sender_id, $receiver_id) {
        $sql = "UPDATE chat_messages SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $sender_id, $receiver_id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Lỗi execute statement: " . $stmt->error);
            return false;
        }
    }
    
    // Thêm phương thức debug để kiểm tra
    public function debugCustomers($cskh_id) {
        $sql = "SELECT * FROM chat_messages WHERE receiver_id = ? OR sender_id = ? LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("ii", $cskh_id, $cskh_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        return $messages;
    }
}
?>
