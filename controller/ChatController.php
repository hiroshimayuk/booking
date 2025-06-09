<?php
// controller/ChatController.php
session_start();
require_once "../model/ChatModel.php";

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$chatModel = new ChatModel();
$action = isset($_POST['action']) ? $_POST['action'] : "";

// Đảm bảo header JSON
header("Content-Type: application/json");

switch ($action) {
    case "sendMessage":
        $sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : 0;
        $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : "";
        
        // Kiểm tra dữ liệu đầu vào
        if (!$sender_id || !$receiver_id || $message === "") {
            echo json_encode(["success" => false, "message" => "Thiếu thông tin cần thiết"]);
            exit();
        }
        
        // Lưu tin nhắn vào cơ sở dữ liệu
        $result = $chatModel->saveMessage($sender_id, $receiver_id, $message);
        if ($result) {
            echo json_encode(["success" => true, "message" => "Tin nhắn đã được gửi"]);
        } else {
            echo json_encode(["success" => false, "message" => "Gửi tin nhắn thất bại"]);
        }
        break;
        
    case "getMessages":
        $user1 = isset($_POST['user1']) ? intval($_POST['user1']) : 0;
        $user2 = isset($_POST['user2']) ? intval($_POST['user2']) : 0;

        // Ghi log để debug
        error_log("getMessages - user1: $user1, user2: $user2");

        // Kiểm tra dữ liệu đầu vào
        if (!$user1 || !$user2) {
            echo json_encode(["success" => false, "messages" => []]);
            exit();
        }

        // Lấy tin nhắn từ cơ sở dữ liệu
        $messages = $chatModel->getMessages($user1, $user2);
        echo json_encode(["success" => true, "messages" => $messages]);
        break;
        
    case "getCustomers":
        $cskh_id = isset($_POST['cskh_id']) ? intval($_POST['cskh_id']) : 0;
        
        if (!$cskh_id) {
            echo json_encode(["success" => false, "customers" => []]);
            exit();
        }
        
        $customers = $chatModel->getCustomersByCskhId($cskh_id);
        echo json_encode(["success" => true, "customers" => $customers]);
        break;
        
    case "markAsRead":
        $sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : 0;
        $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
        
        if (!$sender_id || !$receiver_id) {
            echo json_encode(["success" => false, "message" => "Thiếu thông tin cần thiết"]);
            exit();
        }
        
        $result = $chatModel->markAsRead($sender_id, $receiver_id);
        echo json_encode(["success" => $result]);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Hành động không hợp lệ"]);
        break;
}
?>
