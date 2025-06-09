<?php
session_start();

if (!isset($_SESSION['forgot_password_otp']) || !isset($_SESSION['forgot_password_user_id'])) {
    echo "OTP đã hết hạn. Vui lòng yêu cầu gửi lại OTP từ trang <a href='login.php'>Đăng nhập</a>.";
    exit();
}

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/UserModel.php";

$error = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['step']) && $_POST['step'] === "verify_otp") {
        $otpInput = trim($_POST['otp']);
        if ($otpInput != $_SESSION['forgot_password_otp']) {
            $error = "OTP không đúng. Vui lòng thử lại.";
        } else {
            // Nếu OTP đúng, đánh dấu xác thực thành công
            $_SESSION['otp_verified'] = true;
            $message = "OTP hợp lệ. Vui lòng nhập mật khẩu mới.";
        }
    } elseif (isset($_POST['step']) && $_POST['step'] === "reset_password") {
        // Kiểm tra xem OTP đã được xác thực hay chưa
        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
            $error = "OTP chưa được xác thực.";
        } else {
            $newPassword     = trim($_POST['new_password']);
            $confirmPassword = trim($_POST['confirm_password']);
            
            if ($newPassword !== $confirmPassword) {
                $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
            } else {
                // Cập nhật mật khẩu mới vào cơ sở dữ liệu
                $conn = Database::getInstance()->getConnection();
                $userModel = new UserModel($conn);
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $userId = $_SESSION['forgot_password_user_id'];
                
                if ($userModel->updatePassword($userId, $hashedPassword)) {
                    // Xóa dữ liệu OTP khỏi session sau khi cập nhật thành công
                    unset($_SESSION['forgot_password_otp']);
                    unset($_SESSION['forgot_password_user_id']);
                    unset($_SESSION['otp_verified']);
                    
                    // Chuyển hướng về trang đăng nhập
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Có lỗi xảy ra khi cập nhật mật khẩu.";
                }
            }
        }
    }
}

// Hiển thị mẫu email nếu được yêu cầu
if (isset($_GET['show_email']) && $_GET['show_email'] == 1) {
    $userName = "Người dùng";
    $otp = $_SESSION['forgot_password_otp'];
    $currentYear = date('Y');
    define('DIRECT_ACCESS', true);
    include_once 'email_template.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu - Four Rock</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Roboto', sans-serif; 
            background-color: #f2f2f2; 
            color: #333;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .position-relative {
            position: relative;
        }
        .position-relative input.form-control {
            padding-right: 40px;
        }
        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            z-index: 2;
        }
        .otp-container {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .otp-box {
            font-size: 24px;
            color: #2E86C1;
            font-weight: bold;
            margin: 15px 0;
            padding: 10px 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            display: inline-block;
            border: 1px dashed #ccc;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h2 {
            color: #2E86C1;
            font-weight: 700;
        }
        .btn-primary {
            background-color: #2E86C1;
            border-color: #2E86C1;
        }
        .btn-primary:hover {
            background-color: #1a5276;
            border-color: #1a5276;
        }
        .btn-success {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        .btn-success:hover {
            background-color: #1e8449;
            border-color: #1e8449;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .email-preview-btn {
            margin-top: 10px;
            font-size: 14px;
        }
        .modal-dialog {
            max-width: 650px;
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-body {
            padding: 0;
        }
        .email-frame {
            width: 100%;
            height: 600px;
            border: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Đặt lại mật khẩu</h2>
        <p>Bệnh viện Four Rock</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="otp-container">
        <p>Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra và nhập mã xác nhận:</p>
        <div class="otp-box"><?php echo substr($_SESSION['forgot_password_otp'], 0, 1) . '*****'; ?></div>
        <p class="text-muted">Mã OTP có hiệu lực trong 5 phút</p>
    </div>
    
    <?php 
    // Nếu OTP chưa được xác thực, hiển thị form nhập OTP.
    // Nếu đã được xác thực, hiển thị form nhập mật khẩu mới.
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true): ?>
        <form method="post" action="">
            <input type="hidden" name="step" value="verify_otp">
            <div class="form-group">
                <label for="otp"><i class="fas fa-key mr-2"></i>Nhập mã OTP:</label>
                <input type="text" name="otp" id="otp" class="form-control" placeholder="Nhập mã 6 số" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-check-circle mr-2"></i>Xác nhận OTP</button>
        </form>
    <?php else: ?>
        <form method="post" action="">
            <input type="hidden" name="step" value="reset_password">
            <div class="form-group position-relative">
                <label for="new_password"><i class="fas fa-lock mr-2"></i>Mật khẩu mới:</label>
                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Nhập mật khẩu mới" required>
                <i class="fas fa-eye toggle-password" data-target="#new_password"></i>
            </div>
            <div class="form-group position-relative">
                <label for="confirm_password"><i class="fas fa-lock mr-2"></i>Xác nhận mật khẩu mới:</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
                <i class="fas fa-eye toggle-password" data-target="#confirm_password"></i>
            </div>
            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save mr-2"></i>Đặt lại mật khẩu</button>
        </form>
    <?php endif; ?>
    
    <div class="footer">
        <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
        <a href="login.php" class="btn btn-link"><i class="fas fa-arrow-left mr-2"></i>Quay lại trang đăng nhập</a>
    </div>
</div>

<!-- Modal hiển thị mẫu email -->
<div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailPreviewModalLabel">Mẫu email khôi phục mật khẩu</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe class="email-frame" src="email_template.php?show_email=1"></iframe>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function(){
        // Toggle hiển thị mật khẩu qua icon
        $(".toggle-password").click(function(){
            var targetInput = $($(this).data("target"));
            var type = targetInput.attr("type") === "password" ? "text" : "password";
            targetInput.attr("type", type);
            $(this).toggleClass("fa-eye fa-eye-slash");
        });
    });
</script>
</body>
</html>
