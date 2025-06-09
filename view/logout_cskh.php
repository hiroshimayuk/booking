<?php
session_start();

// Hủy session CSKH
unset($_SESSION['cskh']);

// Tùy chọn: hủy toàn bộ session
// session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: ../view/login_cskh.php");
exit();
?>