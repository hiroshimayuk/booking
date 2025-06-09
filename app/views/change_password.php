<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["MaNguoiDung"])) {
    header("Location: " . BASE_URL . "Auth?action=login");
    exit();
}

$message = "";
if (isset($_GET["success"])) {
    $message = "Đổi mật khẩu thành công!";
    $messageType = "success";
} elseif (isset($_GET["error"])) {
    $error = $_GET["error"];
    if ($error == "confirm") {
        $message = "Mật khẩu mới và xác nhận mật khẩu không khớp!";
    } elseif ($error == "old") {
        $message = "Mật khẩu cũ không đúng!";
    } elseif ($error == "update") {
        $message = "Lỗi khi cập nhật mật khẩu!";
    } elseif ($error == "user") {
        $message = "Không tìm thấy thông tin người dùng!";
    } elseif ($error == "otp") {
        $message = "Mã OTP không đúng hoặc đã hết hạn!";
    }
    $messageType = "error";
}

// Lấy thông tin người dùng từ session
$userName = "";
if (isset($_SESSION["HoTen"]) && !empty($_SESSION["HoTen"])) {
    $userName = $_SESSION["HoTen"];
} elseif (isset($_SESSION["TenDangNhap"]) && !empty($_SESSION["TenDangNhap"])) {
    $userName = $_SESSION["TenDangNhap"];
} else {
    $userName = "Người dùng";
}

// Hiển thị mẫu email nếu được yêu cầu
if (isset($_GET['show_email']) && $_GET['show_email'] == 1) {
    $otp = isset($_SESSION['verification_code']) ? $_SESSION['verification_code'] : "123456";
    $currentYear = date('Y');
    define('DIRECT_ACCESS', true);
    include_once __DIR__ . '/../../view/change_password_email.php';
    exit;
}
?>

<?php include __DIR__ . '/../../header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
        }
        
        .container {
            background-color: #fff;
            padding: 30px;
            max-width: 600px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn-success {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        /* Position relative cho input có icon */
        .position-relative {
            position: relative;
        }
        .position-relative input {
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        /* OTP container */
        .otp-container {
            text-align: center;
            margin: 20px 0;
            display: none;
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
        /* Modal email preview */
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
        .email-preview-btn {
            margin-top: 10px;
            font-size: 14px;
        }
        #otpMessage {
            font-size: 0.9rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Đổi mật khẩu</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Hiển thị OTP khi được gửi -->
        <div class="otp-container" id="otpContainer" style="display: none;">
            <p>Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra và nhập mã xác nhận:</p>
            <div class="otp-box" id="otpPreview">******</div>
            <p class="text-muted">Mã OTP có hiệu lực trong 5 phút</p>
        </div>
        
        <form id="changePasswordForm" method="POST" action="<?= BASE_URL ?>app/controllers/ChangePasswordController.php?action=update">
            <div class="form-group position-relative">
                <label for="oldPassword"><i class="fas fa-lock mr-2"></i>Mật khẩu cũ:</label>
                <input type="password" id="oldPassword" name="oldPassword" class="form-control" required>
                <i class="fas fa-eye toggle-password" data-target="#oldPassword"></i>
            </div>

            <div class="form-group position-relative">
                <label for="newPassword"><i class="fas fa-key mr-2"></i>Mật khẩu mới:</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control" required>
                <i class="fas fa-eye toggle-password" data-target="#newPassword"></i>
            </div>

            <div class="form-group position-relative">
                <label for="confirmPassword"><i class="fas fa-check-circle mr-2"></i>Xác nhận mật khẩu mới:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required>
                <i class="fas fa-eye toggle-password" data-target="#confirmPassword"></i>
            </div>
            
            <!-- Hiển thị trường email ngay từ đầu -->
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope mr-2"></i>Email của bạn:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Nhập email để nhận mã OTP" required>
                <small class="form-text text-muted">Mã OTP sẽ được gửi đến email này.</small>
            </div>
            
            <div class="form-group">
                <label for="verification_code"><i class="fas fa-shield-alt mr-2"></i>Mã xác nhận (OTP):</label>
                <input type="text" name="verification_code" id="verification_code" class="form-control" required disabled>
            </div>
            
            <div id="otpMessage" class="mb-3"></div>
            
            <div class="text-center">
                <button type="button" id="sendOtpBtn" class="btn btn-primary"><i class="fas fa-paper-plane mr-2"></i>Gửi mã xác nhận</button>
                <button type="submit" id="submitPasswordBtn" class="btn btn-success" style="display: none;"><i class="fas fa-check-circle mr-2"></i>Xác nhận đổi mật khẩu</button>
            </div>
        </form>
    </div>
    
    <!-- Modal xem trước email -->
    <div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailPreviewModalLabel">Xem trước email OTP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="<?= BASE_URL ?>app/views/change_password.php?show_email=1" class="email-frame"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle hiển thị mật khẩu
            $(".toggle-password").click(function() {
                var input = $($(this).attr("data-target"));
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                    $(this).removeClass("fa-eye").addClass("fa-eye-slash");
                } else {
                    input.attr("type", "password");
                    $(this).removeClass("fa-eye-slash").addClass("fa-eye");
                }
            });
            
            // Xử lý gửi OTP
            $("#sendOtpBtn").click(function() {
                // Kiểm tra mật khẩu cũ đã được nhập chưa
                var oldPassword = $("#oldPassword").val().trim();
                var newPassword = $("#newPassword").val().trim();
                var confirmPassword = $("#confirmPassword").val().trim();
                var email = $("#email").val().trim(); // Lấy email từ input nếu có
                
                if (oldPassword === "") {
                    $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Vui lòng nhập mật khẩu cũ");
                    $("#oldPassword").focus();
                    return;
                }
                
                if (newPassword === "") {
                    $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Vui lòng nhập mật khẩu mới");
                    $("#newPassword").focus();
                    return;
                }
                
                // Kiểm tra điều kiện mật khẩu mới
                var passwordValidation = validatePassword(newPassword);
                if (passwordValidation !== true) {
                    $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text(passwordValidation);
                    $("#newPassword").focus();
                    return;
                }
                
                if (confirmPassword === "") {
                    $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Vui lòng xác nhận mật khẩu mới");
                    $("#confirmPassword").focus();
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Mật khẩu mới và xác nhận mật khẩu không khớp");
                    $("#confirmPassword").focus();
                    return;
                }
                
                // Kiểm tra email nếu trường email đang hiển thị
                if ($("#emailInputGroup").is(":visible") && email === "") {
                    $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Vui lòng nhập email của bạn");
                    $("#email").focus();
                    return;
                }
                
                // Kiểm tra mật khẩu cũ trước khi gửi OTP
                $.ajax({
                    url: "<?= BASE_URL ?>app/controllers/ChangePasswordController.php",
                    type: "POST",
                    data: {
                        action: "verifyOldPassword",
                        oldPassword: oldPassword
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $("#sendOtpBtn").prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang kiểm tra...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mật khẩu cũ đúng, tiếp tục gửi OTP
                            sendOTPRequest(email);
                        } else {
                            if (response.message && response.message.includes("không có email")) {
                                // Hiển thị trường nhập email
                                $("#emailInputGroup").show();
                                $("#otpMessage").removeClass("text-success").addClass("text-danger")
                                    .text("Không tìm thấy email. Vui lòng nhập email của bạn.");
                                $("#email").focus();
                            } else {
                                $("#otpMessage").removeClass("text-success").addClass("text-danger")
                                    .text(response.message || "Mật khẩu cũ không đúng");
                                $("#oldPassword").focus();
                            }
                            $("#sendOtpBtn").prop("disabled", false).html('<i class="fas fa-paper-plane mr-2"></i>Gửi mã xác nhận');
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#otpMessage").removeClass("text-success").addClass("text-danger")
                            .text("Lỗi kết nối: " + error);
                        $("#sendOtpBtn").prop("disabled", false).html('<i class="fas fa-paper-plane mr-2"></i>Gửi mã xác nhận');
                    }
                });
            });
            
            // Hàm gửi yêu cầu OTP
            function sendOTPRequest(email) {
                var data = {
                    action: "sendVerificationCode"
                };
                
                // Thêm email vào request nếu có
                if (email) {
                    data.email = email;
                }
                
                $.ajax({
                    url: "<?= BASE_URL ?>app/controllers/ChangePasswordController.php",
                    type: "POST",
                    data: data,
                    dataType: "json",
                    beforeSend: function() {
                        $("#sendOtpBtn").html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...');
                    },
                    success: function(response) {
                        console.log("Response received:", response); // Debug log
                        if (response.success) {
                            // Hiển thị OTP container và cập nhật preview
                            $("#otpContainer").show();
                            
                            // Hiển thị ký tự đầu tiên của OTP và che phần còn lại
                            if (response.otp && response.otp.length > 0) {
                                $("#otpPreview").text(response.otp.substring(0, 1) + "*****");
                            }
                            
                            // Hiển thị thông báo thành công
                            $("#otpMessage").removeClass("text-danger").addClass("text-success")
                                .text("Mã OTP đã được gửi đến email của bạn.");
                            
                            // Kích hoạt input OTP và nút submit
                            $("#verification_code").prop("disabled", false).val("").focus();
                            $("#submitPasswordBtn").show();
                            
                            // Đổi text của nút gửi OTP
                            $("#sendOtpBtn").html('<i class="fas fa-paper-plane mr-2"></i>Gửi lại mã OTP');
                        } else {
                            if (response.message && response.message.includes("không có email")) {
                                // Hiển thị trường nhập email
                                $("#emailInputGroup").show();
                                $("#otpMessage").removeClass("text-success").addClass("text-danger")
                                    .text("Không tìm thấy email. Vui lòng nhập email của bạn.");
                                $("#email").focus();
                            } else {
                                $("#otpMessage").removeClass("text-success").addClass("text-danger")
                                    .text("Lỗi: " + (response.message || "Không thể gửi mã OTP"));
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error); // Debug log
                        $("#otpMessage").removeClass("text-success").addClass("text-danger")
                            .text("Lỗi kết nối: " + error);
                    },
                    complete: function() {
                        $("#sendOtpBtn").prop("disabled", false);
                    }
                });
            }
            
            // Kiểm tra form trước khi submit
            $("#changePasswordForm").submit(function(e) {
                var newPass = $("#newPassword").val();
                var confirmPass = $("#confirmPassword").val();
                
                // Kiểm tra điều kiện mật khẩu mới
                var passwordValidation = validatePassword(newPass);
                if (passwordValidation !== true) {
                    e.preventDefault();
                    alert(passwordValidation);
                    $("#newPassword").focus();
                    return false;
                }
                
                if (newPass !== confirmPass) {
                    e.preventDefault();
                    alert("Mật khẩu mới và xác nhận mật khẩu không khớp!");
                    return false;
                }
                
                if ($("#verification_code").val().trim() === "") {
                    e.preventDefault();
                    alert("Vui lòng nhập mã OTP!");
                    return false;
                }
                
                return true;
            });
            
            // Hàm kiểm tra mật khẩu
            function validatePassword(password) {
                if (password.length < 6) {
                    return "Mật khẩu phải có ít nhất 6 ký tự";
                }
                return true;
            }
        });
    </script>
</body>
</html>
