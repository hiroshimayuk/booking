<?php
// view/change_password.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
$message = isset($_SESSION['cp_message']) ? $_SESSION['cp_message'] : "";
unset($_SESSION['cp_message']);

// Hiển thị mẫu email nếu được yêu cầu
if (isset($_GET['show_email']) && $_GET['show_email'] == 1) {
    $userName = isset($user['HoTen']) ? $user['HoTen'] : $user['TenDangNhap'];
    $otp = isset($_SESSION['verification_code']) ? $_SESSION['verification_code'] : "123456";
    $currentYear = date('Y');
    define('DIRECT_ACCESS', true);
    include_once 'change_password_email.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <title>Đổi mật khẩu - Four Rock</title>
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <!-- Bootstrap, Font Awesome và Google Fonts -->
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
   <style>
      body {
         color: #000;
         font-family: 'Roboto', sans-serif;
         background-color: #f2f2f2;
      }
      /* Header giống trang index */
      .navbar-brand,
      .navbar-nav .nav-link {
         color: #000 !important;
      }
      .navbar-nav .nav-link:hover,
      .navbar-brand:hover {
         color: #000 !important;
      }
      /* Đặt khoảng cách khi header fixed */
      .profile-container {
         margin-top: 120px;
         background: #fff;
         padding: 30px;
         border-radius: 8px;
         box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      }
      /* Input có icon toggle */
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
      /* Vùng thông báo OTP */
      #otpMessage {
         font-size: 0.9rem;
         color: #28a745; /* Màu xanh cho thông báo thành công */
         margin-top: 5px;
      }
      /* Hiển thị OTP */
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
   </style>
</head>
<body>
   <!-- Header giống index -->
   <header>
      <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-light shadow-sm">
         <div class="container">
            <a class="navbar-brand" href="/booking/index.php">Four Rock</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
               <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
               <ul class="navbar-nav ml-auto">
                  <li class="nav-item"><a class="nav-link" href="/booking/index.php#hero">Trang chủ</a></li>
                  <li class="nav-item"><a class="nav-link" href="/booking/hospital-blog.html">Blog</a></li>
                  <li class="nav-item dropdown">
                     <a class="nav-link dropdown-toggle" href="#" id="serviceDropdown" role="button" data-toggle="dropdown">Dịch vụ</a>
                     <div class="dropdown-menu" aria-labelledby="serviceDropdown">
                        <a class="dropdown-item" href="/booking/general-checkup.html">Khám tổng quát</a>
                        <a class="dropdown-item" href="/booking/cardiology.html">Tim mạch</a>
                        <a class="dropdown-item" href="/booking/testing.html">Xét nghiệm</a>
                     </div>
                  </li>
                  <li class="nav-item dropdown">
                     <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button" data-toggle="dropdown">Giới thiệu</a>
                     <div class="dropdown-menu" aria-labelledby="aboutDropdown">
                        <a class="dropdown-item" href="/booking/hospital-blog.html#benhvien">Bệnh viện</a>
                        <a class="dropdown-item" href="/booking/hospital-blog.html#bacsi">Bác sĩ</a>
                     </div>
                  </li>
                  <li class="nav-item"><a class="nav-link" href="/booking/index.php#news">Tin tức</a></li>
                  <li class="nav-item"><a class="nav-link" href="/booking/index.php#contact">Liên hệ</a></li>
                  <li class="nav-item dropdown">
                     <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                        Xin chào, <?php echo htmlspecialchars($user["TenDangNhap"]); ?>
                     </a>
                     <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="change_password.php">Đổi mật khẩu</a>
                        <a class="dropdown-item" href="update_profile.php">Cập nhật thông tin cá nhân</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/booking/view/logout.php">Đăng xuất</a>
                     </div>
                  </li>
               </ul>
            </div>
         </div>
      </nav>
   </header>

   <div class="container profile-container">
      <h2 class="text-center mb-4">Đổi mật khẩu</h2>
      <?php if ($message): ?>
         <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <!-- Hiển thị OTP khi được gửi -->
      <div class="otp-container" id="otpContainer">
         <p>Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra và nhập mã xác nhận:</p>
         <div class="otp-box" id="otpPreview">******</div>
         <p class="text-muted">Mã OTP có hiệu lực trong 5 phút</p>
      </div>

      <!-- Form đổi mật khẩu -->
      <form id="changePasswordForm" action="/booking/controller/UserController.php" method="post">
         <input type="hidden" name="action" value="changePassword">
         <div class="form-group position-relative">
            <label for="old_password">Mật khẩu cũ:</label>
            <input type="password" name="old_password" id="old_password" class="form-control" required>
            <i class="fas fa-eye toggle-password" data-target="#old_password"></i>
         </div>
         <div class="form-group position-relative">
            <label for="new_password">Mật khẩu mới:</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
            <i class="fas fa-eye toggle-password" data-target="#new_password"></i>
         </div>
         <div class="form-group position-relative">
            <label for="confirm_password">Xác nhận mật khẩu mới:</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            <i class="fas fa-eye toggle-password" data-target="#confirm_password"></i>
         </div>
         <div class="form-group">
            <label for="verification_code">Mã xác nhận (OTP):</label>
            <input type="text" name="verification_code" id="verification_code" class="form-control" required disabled>
         </div>
         <div id="otpMessage" class="text-success mb-3"></div>
         <div class="text-center">
            <button type="button" id="sendOtpBtn" class="btn btn-primary">Gửi mã xác nhận</button>
            <button type="submit" id="submitPasswordBtn" class="btn btn-success" style="display: none;">Xác nhận đổi mật khẩu</button>
         </div>
      </form>
   </div>

   <!-- Modal hiển thị mẫu email -->
   <div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
     <div class="modal-dialog" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title" id="emailPreviewModalLabel">Mẫu email xác nhận đổi mật khẩu</h5>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           <iframe class="email-frame" src="change_password.php?show_email=1"></iframe>
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
         
         // Kiểm tra điều kiện mật khẩu
         function validatePassword(password) {
            // Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số
            if (password.length < 8) {
               return "Mật khẩu phải có ít nhất 8 ký tự";
            }
            if (!/[A-Z]/.test(password)) {
               return "Mật khẩu phải chứa ít nhất một chữ hoa";
            }
            if (!/[a-z]/.test(password)) {
               return "Mật khẩu phải chứa ít nhất một chữ thường";
            }
            if (!/[0-9]/.test(password)) {
               return "Mật khẩu phải chứa ít nhất một chữ số";
            }
            return true;
         }
         
         // Xử lý gửi OTP
         $("#sendOtpBtn").click(function() {
            // Kiểm tra mật khẩu cũ đã được nhập chưa
            var oldPassword = $("#old_password").val().trim();
            var newPassword = $("#new_password").val().trim();
            var confirmPassword = $("#confirm_password").val().trim();
            
            if (oldPassword === "") {
               $("#otpMessage").removeClass("text-success").addClass("text-danger")
                  .text("Vui lòng nhập mật khẩu cũ");
               $("#old_password").focus();
               return;
            }
            
            if (newPassword === "") {
               $("#otpMessage").removeClass("text-success").addClass("text-danger")
                  .text("Vui lòng nhập mật khẩu mới");
               $("#new_password").focus();
               return;
            }
            
            // Kiểm tra điều kiện mật khẩu mới
            var passwordValidation = validatePassword(newPassword);
            if (passwordValidation !== true) {
               $("#otpMessage").removeClass("text-success").addClass("text-danger")
                  .text(passwordValidation);
               $("#new_password").focus();
               return;
            }
            
            if (confirmPassword === "") {
               $("#otpMessage").removeClass("text-success").addClass("text-danger")
                  .text("Vui lòng xác nhận mật khẩu mới");
               $("#confirm_password").focus();
               return;
            }
            
            if (newPassword !== confirmPassword) {
               $("#otpMessage").removeClass("text-success").addClass("text-danger")
                  .text("Mật khẩu mới và xác nhận mật khẩu không khớp");
               $("#confirm_password").focus();
               return;
            }
            
            // Kiểm tra mật khẩu cũ trước khi gửi OTP
            $.ajax({
               url: "/booking/controller/UserController.php",
               type: "POST",
               data: {
                  action: "verifyOldPassword",
                  old_password: oldPassword
               },
               dataType: "json",
               beforeSend: function() {
                  $("#sendOtpBtn").prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang kiểm tra...');
               },
               success: function(response) {
                  if (response.success) {
                     // Mật khẩu cũ đúng, tiếp tục gửi OTP
                     sendOTPRequest();
                  } else {
                     $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Mật khẩu cũ không đúng");
                     $("#old_password").focus();
                     $("#sendOtpBtn").prop("disabled", false).html('Gửi mã xác nhận');
                  }
               },
               error: function(xhr, status, error) {
                  $("#otpMessage").removeClass("text-success").addClass("text-danger")
                     .text("Lỗi kết nối: " + error);
                  $("#sendOtpBtn").prop("disabled", false).html('Gửi mã xác nhận');
               }
            });
         });
         
         // Hàm gửi yêu cầu OTP
         function sendOTPRequest() {
            $.ajax({
               url: "/booking/controller/UserController.php",
               type: "POST",
               data: {
                  action: "sendVerificationCode"
               },
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
                     $("#otpMessage").removeClass("text-success").addClass("text-danger")
                        .text("Lỗi: " + (response.message || "Không thể gửi mã OTP"));
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
            var newPass = $("#new_password").val();
            var confirmPass = $("#confirm_password").val();
            
            // Kiểm tra điều kiện mật khẩu mới
            var passwordValidation = validatePassword(newPass);
            if (passwordValidation !== true) {
               e.preventDefault();
               alert(passwordValidation);
               $("#new_password").focus();
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
      });
   </script>
</body>
           <h5
