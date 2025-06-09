<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Tắt hiển thị lỗi trực tiếp để tránh trả về HTML khi có lỗi
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/doimatkhau.php';
// Thêm PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../../vendor/autoload.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/BOOKING/');
}

class ChangePasswordController {
    private $conn;
    private $userModel; // Dùng model DoiMatKhau

    public function __construct($conn) {
        $this->conn = $conn;
        $this->userModel = new DoiMatKhau($conn);
    }

    // Hiển thị form đổi mật khẩu
    public function index() {
        if (!isset($_SESSION["MaNguoiDung"])) {
            header("Location: " . BASE_URL . "Auth?action=login");
            exit();
        }
        require_once __DIR__ . '/../views/change_password.php';
    }

    // Xử lý đổi mật khẩu với xác thực OTP
    public function update() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!isset($_SESSION["MaNguoiDung"])) {
                header("Location: " . BASE_URL . "Auth?action=login");
                exit();
            }
            $maNguoiDung = $_SESSION["MaNguoiDung"];
            $oldPassword = isset($_POST["oldPassword"]) ? trim($_POST["oldPassword"]) : "";
            $newPassword = isset($_POST["newPassword"]) ? trim($_POST["newPassword"]) : "";
            $confirmPassword = isset($_POST["confirmPassword"]) ? trim($_POST["confirmPassword"]) : "";
            $verificationCode = isset($_POST["verification_code"]) ? trim($_POST["verification_code"]) : "";

            // Kiểm tra mật khẩu mới có khớp với xác nhận không
            if ($newPassword !== $confirmPassword) {
                header("Location: " . BASE_URL . "ChangePassword?action=index&error=confirm");
                exit();
            }
            
            // Kiểm tra mã OTP
            if (!isset($_SESSION['verification_code']) || $verificationCode != $_SESSION['verification_code']) {
                header("Location: " . BASE_URL . "ChangePassword?action=index&error=otp");
                exit();
            }
            
            // Lấy thông tin người dùng
            $user = $this->userModel->getUserById($maNguoiDung);
            if (!$user) {
                header("Location: " . BASE_URL . "ChangePassword?action=index&error=user");
                exit();
            }
            
            // So sánh mật khẩu cũ nhập vào với giá trị trong CSDL
            if ($oldPassword !== $user["MatKhau"]) {
                header("Location: " . BASE_URL . "ChangePassword?action=index&error=old");
                exit();
            }
            
            // Cập nhật mật khẩu mới
            if ($this->userModel->updatePassword($maNguoiDung, $newPassword)) {
                // Xóa mã OTP sau khi đổi mật khẩu thành công
                unset($_SESSION['verification_code']);
                header("Location: " . BASE_URL . "ChangePassword?action=index&success=1");
                exit();
            } else {
                header("Location: " . BASE_URL . "ChangePassword?action=index&error=update");
                exit();
            }
        }
    }
    
    // Kiểm tra mật khẩu cũ
    public function verifyOldPassword() {
        try {
            // Đảm bảo header được thiết lập đúng
            header('Content-Type: application/json');
            
            if (!isset($_SESSION["MaNguoiDung"])) {
                echo json_encode(['success' => false, 'message' => 'Người dùng chưa đăng nhập']);
                exit();
            }
            
            $maNguoiDung = $_SESSION["MaNguoiDung"];
            $oldPassword = isset($_POST["oldPassword"]) ? trim($_POST["oldPassword"]) : "";
            
            $user = $this->userModel->getUserById($maNguoiDung);
            if ($user && $oldPassword === $user["MatKhau"]) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu cũ không đúng']);
            }
        } catch (Exception $e) {
            // Ghi log lỗi
            error_log("Error in verifyOldPassword: " . $e->getMessage());
            // Trả về lỗi dạng JSON
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // Gửi mã OTP qua email
    public function sendVerificationCode() {
        try {
            // Đảm bảo header được thiết lập đúng
            header('Content-Type: application/json');
            
            if (!isset($_SESSION["MaNguoiDung"])) {
                echo json_encode(['success' => false, 'message' => 'Người dùng chưa đăng nhập']);
                exit();
            }
            
            $maNguoiDung = $_SESSION["MaNguoiDung"];
            
            // Lấy email từ POST request
            $email = isset($_POST['email']) ? trim($_POST['email']) : null;
            
            // Debug: Ghi log thông tin
            error_log("MaNguoiDung: " . $maNguoiDung);
            error_log("Email from POST: " . ($email ? $email : "Not provided"));
            
            // Lấy thông tin người dùng
            $user = $this->userModel->getUserById($maNguoiDung);
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng']);
                exit();
            }
            
            // Kiểm tra email người dùng
            if (empty($email)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Vui lòng nhập email để nhận mã OTP'
                ]);
                exit();
            }
            
            // Tạo mã OTP ngẫu nhiên 6 chữ số
            $otp = rand(100000, 999999);
            $_SESSION['verification_code'] = $otp;
            
            // Lấy thông tin tên người dùng
            $userName = isset($user["HoTen"]) ? $user["HoTen"] : (isset($user["TenDangNhap"]) ? $user["TenDangNhap"] : "Người dùng");
            
            // Tạo nội dung email bằng cách sử dụng output buffering
            ob_start();
            // Đảm bảo các biến được truyền vào template
            $currentYear = date('Y');
            define('DIRECT_ACCESS', true);
            
            // Sử dụng template email mới
            $emailTemplatePath = __DIR__ . '/../views/email_template_change_password.php';
            if (file_exists($emailTemplatePath)) {
                include $emailTemplatePath;
            } else {
                // Fallback nếu template không tồn tại
                echo "<html><body>";
                echo "<h2>BỆNH VIỆN FOUR_ROCK</h2>";
                echo "<p>Xin chào {$userName},</p>";
                echo "<p>Mã OTP của bạn là: <strong>{$otp}</strong></p>";
                echo "<p>Mã này có hiệu lực trong 5 phút.</p>";
                echo "<p>Trân trọng,<br>Đội ngũ BỆNH VIỆN FOUR_ROCK</p>";
                echo "</body></html>";
            }
            $messageHtml = ob_get_clean();
            
            // Nội dung email dạng plain text
            $messageAlt = "Xin chào {$userName},\n\n"
                        . "Bạn đã yêu cầu đổi mật khẩu tài khoản tại BỆNH VIỆN FOUR_ROCK.\n\n"
                        . "Mã xác nhận của bạn là: {$otp}\n\n"
                        . "Mã xác nhận này sẽ hết hạn sau 5 phút.\n\n"
                        . "Nếu bạn không yêu cầu hành động này, vui lòng bỏ qua email này.\n\n"
                        . "Trân trọng,\nĐội ngũ BỆNH VIỆN FOUR_ROCK";
            
            // Gửi email với PHPMailer
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hienquangtranht1@gmail.com';
            $mail->Password   = 'qewg mrze brpz lncf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Cài đặt charset và encoding cho email bằng tiếng Việt
            $mail->CharSet   = 'UTF-8';
            $mail->Encoding  = 'base64';
            
            $mail->setFrom('hienquangtranht1@gmail.com', 'BỆNH VIỆN FOUR_ROCK');
            $mail->addAddress($email, $userName);
            $mail->isHTML(true);
            $mail->Subject = "Mã xác nhận đổi mật khẩu - BỆNH VIỆN FOUR_ROCK";
            $mail->Body    = $messageHtml;
            $mail->AltBody = $messageAlt;
            
            $mail->send();
            
            // Trả về thông tin OTP đầy đủ để JavaScript có thể hiển thị ký tự đầu tiên
            echo json_encode([
                'success' => true, 
                'message' => 'Mã OTP đã được gửi đến email ' . substr($email, 0, 3) . '***' . substr($email, strpos($email, '@')),
                'otp' => $otp // Gửi OTP đầy đủ để JavaScript có thể xử lý
            ]);
        } catch (Exception $e) {
            // Ghi log lỗi
            error_log("Error in sendVerificationCode: " . $e->getMessage());
            // Trả về lỗi dạng JSON
            echo json_encode(['success' => false, 'message' => 'Không thể gửi mã OTP: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Bắt tất cả lỗi và trả về JSON
try {
    // Xử lý request
    if (isset($_POST['action'])) {
        $controller = new ChangePasswordController($conn);
        $action = $_POST['action'];
        
        if ($action === 'verifyOldPassword') {
            $controller->verifyOldPassword();
        } elseif ($action === 'sendVerificationCode') {
            $controller->sendVerificationCode();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
            exit();
        }
    } else {
        $controller = new ChangePasswordController($conn);
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';
        
        if ($action === 'update') {
            $controller->update();
        } else {
            $controller->index();
        }
    }
} catch (Exception $e) {
    // Nếu có lỗi, trả về JSON thay vì hiển thị lỗi HTML
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        exit();
    } else {
        // Nếu là request thông thường, chuyển hướng về trang lỗi
        header("Location: " . BASE_URL . "ChangePassword?action=index&error=system");
        exit();
    }
}
?>
