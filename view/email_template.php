<?php
// Chỉ hiển thị nội dung email khi được gọi trực tiếp
if (!defined('DIRECT_ACCESS')) {
    $userName = isset($userName) ? $userName : "Người dùng";
    $otp = isset($otp) ? $otp : "123456";
    $currentYear = isset($currentYear) ? $currentYear : date('Y');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khôi phục mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #2E86C1;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
            color: #333333;
        }
        .otp {
            font-size: 32px;
            color: #2E86C1;
            text-align: center;
            font-weight: bold;
            margin: 25px 0;
            letter-spacing: 5px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #cccccc;
        }
        .footer {
            padding: 20px;
            font-size: 12px;
            color: #777777;
            text-align: center;
            background-color: #f9f9f9;
            border-top: 1px solid #eeeeee;
        }
        .note {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            font-size: 14px;
            color: #666666;
            border-left: 4px solid #2E86C1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>BỆNH VIỆN FOUR_ROCK</h2>
        </div>
        <div class="content">
            <p>Xin chào <strong><?php echo $userName; ?></strong>,</p>

            <p>Bạn đã yêu cầu khôi phục mật khẩu tài khoản tại <strong>BỆNH VIỆN FOUR_ROCK</strong>.</p>

            <p>Vui lòng sử dụng mã xác nhận bên dưới để tiếp tục:</p>

            <div class="otp"><?php echo $otp; ?></div>

            <p>Mã xác nhận này sẽ hết hạn sau 5 phút.</p>

            <p>Nếu bạn không yêu cầu hành động này, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi nếu bạn có bất kỳ thắc mắc nào.</p>

            <div class="note">
                <p><strong>Lưu ý:</strong> Đây là email tự động, vui lòng không trả lời email này.</p>
            </div>

            <br>
            <p>Trân trọng,<br>Đội ngũ <strong>BỆNH VIỆN FOUR_ROCK</strong></p>
        </div>
        <div class="footer">
            &copy; <?php echo $currentYear; ?> BỆNH VIỆN FOUR_ROCK. Mọi quyền được bảo lưu.
        </div>
    </div>
</body>
</html>