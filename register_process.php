<?php
require_once 'app/config/database.php';
session_start();

// Lấy dữ liệu từ form
$fullname = $_POST['fullname'];
$dob = $_POST['dob'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$symptoms = $_POST['symptoms'];
$doctor_id = $_POST['doctor'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];

// Lấy MaBenhNhan từ session (nếu đã đăng nhập)
if (isset($_SESSION['user']['MaNguoiDung'])) {
    // Lấy MaBenhNhan từ bảng BenhNhan
    $maNguoiDung = $_SESSION['user']['MaNguoiDung'];
    $sql = "SELECT MaBenhNhan FROM BenhNhan WHERE MaNguoiDung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $maNguoiDung);
    $stmt->execute();
    $result = $stmt->get_result();
    $benhnhan = $result->fetch_assoc();
    $maBenhNhan = $benhnhan['MaBenhNhan'];
} else {
    // Nếu chưa đăng nhập, có thể tạo mới tài khoản bệnh nhân ở đây (tùy yêu cầu)
    // Hoặc chuyển hướng về trang đăng nhập
    header("Location: view/login.php");
    exit;
}

// Ghép ngày và giờ thành DATETIME
$ngaygio = $appointment_date . ' ' . $appointment_time . ':00';

// Thêm vào bảng LichHen
$sql = "INSERT INTO LichHen (MaBenhNhan, MaBacSi, NgayGio, TrangThai, TrieuChung) VALUES (?, ?, ?, 'Chờ xác nhận', ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $maBenhNhan, $doctor_id, $ngaygio, $symptoms);

if ($stmt->execute()) {
    // Có thể lưu thêm triệu chứng vào bảng khác nếu muốn
    // Chuyển hướng hoặc thông báo thành công
    header("Location: index.php?success=1");
    exit;
} else {
    // Thông báo lỗi
    header("Location: index.php?error=1");
    exit;
}
?>