<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../model/UserModel.php";
require_once __DIR__ . "/../model/Database.php";
// Nếu dùng PHPMailer qua Composer
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController {
    private $userModel;
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->userModel = new UserModel($this->conn);
    }
    
    // 1. Đăng ký: Tạo user và profile bệnh nhân
    public function register($data) {
        $username   = trim($data['username']);
        $password   = $data['password'];
        $fullname   = $data['fullname'];
        $dob        = $data['dob'];
        $gender     = $data['gender'];
        $phone      = $data['phone'];
        $email      = $data['email'];
        $address    = $data['address'];
        $soBaoHiem  = isset($data['soBaoHiem']) ? $data['soBaoHiem'] : "";
        $role       = "Bệnh nhân";
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $this->conn->begin_transaction();
        try {
            $userId = $this->userModel->createUser($username, $hashedPassword, $role);
            if (!$userId) {
                throw new Exception("Error creating user.");
            }
            if (!$this->userModel->createBenhNhan($userId, $fullname, $dob, $gender, $phone, $email, $address, $soBaoHiem)) {
                throw new Exception("Error creating patient profile.");
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Error: " . $e->getMessage();
        }
    }
    
    // 2. Đăng nhập: Xác thực thông tin người dùng
    public function login($data) {
        $username = trim($data['username']);
        $password = $data['password'];
        $user     = $this->userModel->getUserByUsername($username);
        if ($user && password_verify($password, $user['MatKhau'])) {
            return $user;
        }
        return false;
    }
    
    // 3. Đổi mật khẩu (ở chế độ đã đăng nhập): Kiểm tra OTP, mật khẩu cũ và cập nhật mật khẩu mới
    public function changePassword($data) {
        if (!isset($_SESSION['user'])) {
            $_SESSION['cp_message'] = "You are not logged in.";
            header("Location: ../view/change_password.php");
            exit();
        }
        
        $userId          = $_SESSION['user']['MaNguoiDung'];
        $oldPassword     = $data['old_password'];
        $newPassword     = $data['new_password'];
        $confirmPassword = $data['confirm_password'];
        $providedCode    = $data['verification_code'];
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['cp_message'] = "New passwords do not match.";
            header("Location: ../view/change_password.php");
            exit();
        }
        if (!isset($_SESSION['verification_code']) || $providedCode != $_SESSION['verification_code']) {
            $_SESSION['cp_message'] = "Verification code is incorrect.";
            header("Location: ../view/change_password.php");
            exit();
        }
        
        $user = $this->userModel->getUserById($userId);
        if (!$user || !password_verify($oldPassword, $user['MatKhau'])) {
            $_SESSION['cp_message'] = "Old password is incorrect.";
            header("Location: ../view/change_password.php");
            exit();
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->updatePassword($userId, $hashedPassword)) {
            $_SESSION['cp_message'] = "Password changed successfully.";
            unset($_SESSION['verification_code']);
        } else {
            $_SESSION['cp_message'] = "Error updating password.";
        }
        header("Location: ../view/change_password.php");
        exit();
    }
    
    // Helper: cập nhật mật khẩu trong bảng nguoidung
    public function updatePassword($userId, $hashedPassword) {
        $sql = "UPDATE nguoidung SET MatKhau = ? WHERE MaNguoiDung = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { die("Prepare failed: " . $this->conn->error); }
        $stmt->bind_param("si", $hashedPassword, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // 4. Cập nhật thông tin cá nhân (profile)
    public function updateProfile($data, $files) {
        if (!isset($_SESSION['user'])) {
            echo "You are not logged in.";
            exit();
        }
        $userId    = $_SESSION['user']['MaNguoiDung'];
        $fullname  = $data['fullname'];
        $dob       = $data['dob'];
        $gender    = $data['gender'];
        $phone     = $data['phone'];
        $address   = $data['address'];
        $soBaoHiem = isset($data['soBaoHiem']) ? $data['soBaoHiem'] : "";
        
        $imagePath = null;
        if (isset($files['HinhAnhBenhNhan']) && $files['HinhAnhBenhNhan']['error'] === 0) {
            $uploadDir = __DIR__ . "/../public/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename   = basename($files['HinhAnhBenhNhan']['name']);
            $targetFile = $uploadDir . time() . "_" . $filename;
            if (move_uploaded_file($files['HinhAnhBenhNhan']['tmp_name'], $targetFile)) {
                $imagePath = basename($targetFile);
            }
        }
        
        $result = $this->userModel->updateBenhNhanProfile($userId, $fullname, $dob, $gender, $phone, $address, $soBaoHiem, $imagePath);
        if ($result) {
            $updatedUser = $this->userModel->getUserById($userId);
            $_SESSION['user'] = $updatedUser;
            $_SESSION['notification'] = "Profile updated successfully.";
            header("Location: ../view/update_profile.php");
            exit();
        } else {
            $_SESSION['notification'] = "Error updating profile.";
            header("Location: ../view/update_profile.php");
            exit();
        }
    }
    
    // Kiểm tra mật khẩu cũ
    public function verifyOldPassword() {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }
        
        $userId = $_SESSION['user']['MaNguoiDung'];
        $oldPassword = isset($_POST['old_password']) ? $_POST['old_password'] : "";
        
        $user = $this->userModel->getUserById($userId);
        if ($user && password_verify($oldPassword, $user['MatKhau'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu cũ không đúng.']);
        }
        exit();
    }

    // 5. Gửi OTP cho đổi mật khẩu (người dùng đã đăng nhập)
    public function sendVerificationCode() {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }
        $otp = rand(100000, 999999);
        $_SESSION['verification_code'] = $otp;
        
        $user = $_SESSION['user'];
        $to = $user['Email'];
        
        // Tạo nội dung email bằng cách sử dụng output buffering
        ob_start();
        // Đảm bảo các biến được truyền vào template
        $userName = isset($user['HoTen']) ? $user['HoTen'] : $user['TenDangNhap'];
        $currentYear = date('Y');
        $emailTemplatePath = __DIR__ . '/../view/change_password_email.php';
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
        
        $mail = new PHPMailer(true);
        try {
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
            $mail->addAddress($to, $user['TenDangNhap']);
            $mail->isHTML(true);
            $mail->Subject = "Mã xác nhận đổi mật khẩu - BỆNH VIỆN FOUR_ROCK";
            $mail->Body    = $messageHtml;
            $mail->AltBody = $messageAlt;
            
            $mail->send();
            
            // Trả về thông tin OTP đầy đủ để JavaScript có thể hiển thị ký tự đầu tiên
            echo json_encode([
                'success' => true, 
                'message' => 'OTP has been sent to your email.',
                'otp' => $otp // Gửi OTP đầy đủ để JavaScript có thể xử lý
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Could not send OTP: ' . $mail->ErrorInfo]);
        }
        exit();
    }
    
    // 6. Gửi OTP cho quên mật khẩu
    public function sendForgotPasswordOtp() {
        if (!isset($_POST['email'])) {
            $_SESSION["login_message"] = "Vui lòng nhập địa chỉ email.";
            header("Location: ../view/login.php");
            exit();
        }

        $email = trim($_POST['email']);
        
        // Kiểm tra email có tồn tại trong hệ thống không
        $conn = Database::getInstance()->getConnection();
        $userModel = new UserModel($conn);
        $user = $userModel->getUserByEmail($email);
        
        if (!$user) {
            $_SESSION["login_message"] = "Email không tồn tại trong hệ thống.";
            header("Location: ../view/login.php");
            exit();
        }
        
        // Sinh OTP (6 chữ số)
        $otp = rand(100000, 999999);
        
        // Lưu OTP và user_id vào session
        $_SESSION['forgot_password_otp'] = $otp;
        $_SESSION['forgot_password_user_id'] = $user['MaNguoiDung'];
        
        // Tạo nội dung email
        $userName = isset($user['HoTen']) ? $user['HoTen'] : $user['TenDangNhap'];
        $currentYear = date('Y');
        
        // Tạo nội dung email bằng cách sử dụng output buffering
        ob_start();
        // Đảm bảo các biến được truyền vào template
        $emailTemplatePath = __DIR__ . '/../view/email_template.php';
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
                    . "Bạn đã yêu cầu khôi phục mật khẩu tài khoản tại BỆNH VIỆN FOUR_ROCK.\n\n"
                    . "Mã xác nhận của bạn là: {$otp}\n\n"
                    . "Mã xác nhận này sẽ hết hạn sau 5 phút.\n\n"
                    . "Nếu bạn không yêu cầu hành động này, vui lòng bỏ qua email này.\n\n"
                    . "Trân trọng,\nĐội ngũ BỆNH VIỆN FOUR_ROCK";

        // Gửi OTP qua email sử dụng PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hienquangtranht1@gmail.com'; // Thay bằng email của bạn
            $mail->Password   = 'qewg mrze brpz lncf';          // Thay bằng App Password của bạn
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Cài đặt charset và encoding cho email bằng tiếng Việt
            $mail->CharSet   = 'UTF-8';
            $mail->Encoding  = 'base64';
            
            $mail->setFrom('hienquangtranht1@gmail.com', 'BỆNH VIỆN FOUR_ROCK');
            $mail->addAddress($email, $userName);
            $mail->isHTML(true);
            $mail->Subject = "Khôi phục mật khẩu - BỆNH VIỆN FOUR_ROCK";
            $mail->Body    = $messageHtml;
            $mail->AltBody = $messageAlt;
            $mail->send();
            
            // Sau khi gửi OTP thành công, chuyển hướng sang fg_password.php
            header("Location: ../view/fg_password.php");
            exit();
        } catch (Exception $e) {
            $_SESSION["login_message"] = "Không thể gửi OTP. Lỗi: " . $mail->ErrorInfo;
            header("Location: ../view/login.php");
            exit();
        }
    }
    
    // 7. Reset mật khẩu cho quên mật khẩu (action "resetPassword")
    // Lưu ý: Gọi hàm này khi form trong verifyfg_password.php đẩy dữ liệu OTP và mật khẩu mới.
    public function resetPassword($data) {
        // Kiểm tra session có OTP và user id cho quên mật khẩu
        if (!isset($_SESSION['forgot_password_otp']) || !isset($_SESSION['forgot_password_user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Forgot password data has expired. Please request OTP again.'
            ]);
            exit();
        }
        
        $otpInput = isset($data['otp']) ? trim($data['otp']) : "";
        $newPassword = isset($data['new_password']) ? trim($data['new_password']) : "";
        $confirmPassword = isset($data['confirm_password']) ? trim($data['confirm_password']) : "";
        
        if ($otpInput != $_SESSION['forgot_password_otp']) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ]);
            exit();
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode([
                'success' => false,
                'message' => 'New password and confirmation password do not match.'
            ]);
            exit();
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userId = $_SESSION['forgot_password_user_id'];
        if ($this->userModel->updatePassword($userId, $hashedPassword)) {
            unset($_SESSION['forgot_password_otp']);
            unset($_SESSION['forgot_password_user_id']);
            echo json_encode([
                'success' => true,
                'message' => 'Password has been successfully updated.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating the password.'
            ]);
        }
        exit();
    }
}

// Xử lý request POST dựa trên trường action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : "";
    $controller = new UserController();
    
    switch($action) {
        case "login":
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            $user = $controller->login([
                'username' => $username,
                'password' => $password
            ]);
            
            if ($user) {
                $_SESSION['user'] = $user;
                $_SESSION['login_message'] = "Đăng nhập thành công!";
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['login_message'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
                header("Location: ../view/login.php");
                exit();
            }
            break;
        case "register":
            $result = $controller->register($_POST);
            if ($result === true) {
                header("Location: ../view/login.php");
            } else {
                echo $result;
            }
            break;
        case "changePassword":
            $controller->changePassword($_POST);
            break;
        case "updateProfile":
            $controller->updateProfile($_POST, $_FILES);
            break;
        case "sendVerificationCode":
            $controller->sendVerificationCode();
            break;
        case "verifyOldPassword":
            $controller->verifyOldPassword();
            break;
        case "sendForgotPasswordOtp":
            $controller->sendForgotPasswordOtp();
            break;
        case "resetPassword":
            $controller->resetPassword($_POST);
            break;
    }
}
?>
