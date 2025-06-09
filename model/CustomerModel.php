<?php
// model/CustomerModel.php
require_once __DIR__ . "/Database.php";

class CustomerModel {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    // Lấy thông tin khách hàng theo ID
    public function getCustomerById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Cập nhật trạng thái online của khách hàng
    public function updateOnlineStatus($id, $status) {
        $sql = "UPDATE users SET is_online = ?, last_activity = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Lỗi prepare statement: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Lỗi execute statement: " . $stmt->error);
            return false;
        }
    }
}
?>