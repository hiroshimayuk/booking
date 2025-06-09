<?php
// Include config từ thư mục gốc
require_once __DIR__ . '/../app/config/database.php';

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['admin_user'])) {
    header("Location: /booking/admin/login");
    exit();
}

// Kiểm tra vai trò của người dùng
if (!isset($_SESSION['admin_user']['VaiTro']) || $_SESSION['admin_user']['VaiTro'] !== 'Quản trị') {
    die("Bạn không có quyền truy cập vào trang này!");
}
?>