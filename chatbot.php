<?php
// Bật hiển thị lỗi để debug (xóa khi lên production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Header JSON cho frontend
header('Content-Type: application/json');

// Kiểm tra yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["reply" => "Phương thức không hợp lệ."]);
    exit();
}

// Nhận tin nhắn từ người dùng
$userMessage = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if (empty($userMessage)) {
    echo json_encode(["reply" => "Tin nhắn không được để trống."]);
    exit();
}

// Kết nối database
require_once 'app/config/database.php';

// Google Gemini API key - Cảnh báo: Nên lưu trong biến môi trường
$apiKey = "AIzaSyAJA-Eqw-cgV1LYCFDyj1F_CEsl6H1f4hc";
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

// Hàm lấy thông tin từ database
function getHospitalInfo($conn) {
    $info = [
        'doctors' => [],
        'departments' => [],
        'services' => [],
        'hospital_info' => [
            'name' => 'Four Rock Hospital',
            'address' => 'Khu E Hutech, Quận 9, TP. Hồ Chí Minh',
            'phone' => '(0123) 456-789',
            'email' => 'info@fourrock.com',
            'working_hours' => '24/7 - Hỗ trợ cả ngày lẫn đêm',
            'emergency' => 'Cấp cứu 24/7'
        ]
    ];
    
    // Lấy thông tin bác sĩ
    $sql = "SELECT bs.MaBacSi, bs.HoTen, bs.MoTa, k.TenKhoa, bs.SoDienThoai, bs.Email 
            FROM BacSi bs 
            LEFT JOIN Khoa k ON bs.MaKhoa = k.MaKhoa";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $info['doctors'][] = [
                'id' => $row['MaBacSi'],
                'name' => $row['HoTen'],
                'department' => $row['TenKhoa'] ?: 'Chưa xác định',
                'description' => $row['MoTa'] ?: 'Không có mô tả',
                'phone' => $row['SoDienThoai'] ?: 'Chưa cập nhật',
                'email' => $row['Email'] ?: 'Chưa cập nhật'
            ];
        }
    }
    
    // Lấy thông tin khoa
    $sql = "SELECT MaKhoa, TenKhoa, MoTa FROM Khoa";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $info['departments'][] = [
                'id' => $row['MaKhoa'],
                'name' => $row['TenKhoa'],
                'description' => $row['MoTa'] ?: 'Không có mô tả'
            ];
        }
    }
    
    return $info;
}

// Hàm làm sạch phản hồi từ Gemini
function cleanResponse($text) {
    // Loại bỏ ký hiệu ** và các ký tự định dạng không cần thiết
    $text = str_replace('**', '', $text);
    // Thay thế nhiều dấu xuống dòng liên tiếp bằng một dấu
    $text = preg_replace("/\n\s*\n/", "\n", $text);
    // Loại bỏ khoảng trắng thừa
    $text = trim($text);
    // Đảm bảo câu trả lời lịch sự và phù hợp với ngữ cảnh bệnh viện
    if (empty($text)) {
        $text = "Xin lỗi, tôi không hiểu câu hỏi của bạn. Bạn có thể hỏi về thông tin bác sĩ, dịch vụ, địa chỉ hoặc đặt lịch khám.";
    }
    return $text;
}

// Hàm kiểm tra và xử lý câu hỏi về thông tin bệnh viện
function processLocalQuery($userMessage, $conn) {
    $message = strtolower($userMessage);
    $hospitalInfo = getHospitalInfo($conn);
    
    // Kiểm tra câu hỏi về bác sĩ
    if (strpos($message, 'bác sĩ') !== false || strpos($message, 'bác sí') !== false || strpos($message, 'doctor') !== false) {
        // Tìm bác sĩ cụ thể
        foreach ($hospitalInfo['doctors'] as $doctor) {
            $doctorName = strtolower($doctor['name']);
            if (strpos($message, $doctorName) !== false) {
                return "Thông tin về Bác sĩ {$doctor['name']}:\n" .
                       "- Chuyên khoa: {$doctor['department']}\n" .
                       "- Mô tả: {$doctor['description']}\n" .
                       "- Số điện thoại: {$doctor['phone']}\n" .
                       "- Email: {$doctor['email']}\n\n" .
                       "Bạn có thể đặt lịch khám với bác sĩ này qua website của chúng tôi.";
            }
        }
        
        // Liệt kê tất cả bác sĩ
        if (strpos($message, 'danh sách') !== false || strpos($message, 'có những') !== false || strpos($message, 'list') !== false) {
            $doctorList = "Danh sách bác sĩ tại Four Rock Hospital:\n\n";
            foreach ($hospitalInfo['doctors'] as $index => $doctor) {
                $doctorList .= ($index + 1) . ". {$doctor['name']} - {$doctor['department']}\n";
            }
            $doctorList .= "\nVui lòng hỏi thêm nếu bạn muốn biết chi tiết về bác sĩ nào.";
            return $doctorList;
        }
        
        // Hỏi về chuyên khoa
        foreach ($hospitalInfo['departments'] as $dept) {
            if (strpos($message, strtolower($dept['name'])) !== false) {
                $deptDoctors = array_filter($hospitalInfo['doctors'], function($doc) use ($dept) {
                    return $doc['department'] === $dept['name'];
                });
                
                $response = "Thông tin về Khoa {$dept['name']}:\n" .
                           "- Mô tả: {$dept['description']}\n\n";
                
                if (!empty($deptDoctors)) {
                    $response .= "Bác sĩ thuộc khoa:\n";
                    foreach ($deptDoctors as $doc) {
                        $response .= "- {$doc['name']}\n";
                    }
                } else {
                    $response .= "Hiện tại chưa có thông tin bác sĩ thuộc khoa này.\n";
                }
                return $response;
            }
        }
    }
    
    // Kiểm tra câu hỏi về địa chỉ, liên hệ
    if (strpos($message, 'địa chỉ') !== false || strpos($message, 'ở đâu') !== false || strpos($message, 'address') !== false) {
        return "Thông tin liên hệ Four Rock Hospital:\n" .
               "- Địa chỉ: {$hospitalInfo['hospital_info']['address']}\n" .
               "- Hotline: {$hospitalInfo['hospital_info']['phone']}\n" .
               "- Email: {$hospitalInfo['hospital_info']['email']}\n\n" .
               "Chúng tôi hoạt động {$hospitalInfo['hospital_info']['working_hours']}.";
    }
    
    // Kiểm tra câu hỏi về thời gian khám
    if (strpos($message, 'thời gian') !== false || strpos($message, 'giờ') !== false || strpos($message, 'mở cửa') !== false || strpos($message, 'time') !== false) {
        return "Thời gian hoạt động:\n" .
               "- {$hospitalInfo['hospital_info']['working_hours']}\n" .
               "- {$hospitalInfo['hospital_info']['emergency']}\n\n" .
               "Bạn có thể đặt lịch khám qua website hoặc gọi hotline {$hospitalInfo['hospital_info']['phone']}.";
    }
    
    // Kiểm tra câu hỏi về dịch vụ
    if (strpos($message, 'dịch vụ') !== false || strpos($message, 'service') !== false || strpos($message, 'khám') !== false) {
        return "Dịch vụ chính tại Four Rock Hospital:\n" .
               "- Khám tổng quát\n" .
               "- Tim mạch\n" .
               "- Xét nghiệm\n" .
               "- Cấp cứu 24/7\n" .
               "- Nội trú\n" .
               "- Điều trị chuyên khoa\n\n" .
               "Liên hệ {$hospitalInfo['hospital_info']['phone']} để biết thêm chi tiết.";
    }
    
    // Kiểm tra câu hỏi về đặt lịch
    if (strpos($message, 'đặt lịch') !== false || strpos($message, 'book') !== false || strpos($message, 'appointment') !== false) {
        return "Hướng dẫn đặt lịch khám tại Four Rock Hospital:\n" .
               "- Cách 1: Đặt lịch trực tuyến qua website.\n" .
               "- Cách 2: Gọi hotline {$hospitalInfo['hospital_info']['phone']}.\n" .
               "- Cách 3: Đến trực tiếp tại {$hospitalInfo['hospital_info']['address']}.\n\n" .
               "Thời gian đặt lịch: {$hospitalInfo['hospital_info']['working_hours']}.\n" .
               "Bạn muốn đặt lịch với bác sĩ nào?";
    }
    
    return null; // Không tìm thấy thông tin local
}

// Xử lý tin nhắn
$localResponse = processLocalQuery($userMessage, $conn);

if ($localResponse) {
    // Trả về thông tin từ database
    echo json_encode(["reply" => $localResponse]);
    exit();
}

// Nếu không tìm thấy thông tin local, sử dụng Gemini AI
$hospitalInfo = getHospitalInfo($conn);

// Tạo context cho Gemini với thông tin bệnh viện
$context = "Bạn là trợ lý AI của Four Rock Hospital. Thông tin bệnh viện:\n\n";
$context .= "Tên: Four Rock Hospital\n";
$context .= "Địa chỉ: Khu E Hutech, Quận 9, TP. Hồ Chí Minh\n";
$context .= "Hotline: (0123) 456-789\n";
$context .= "Email: info@fourrock.com\n";
$context .= "Thời gian: 24/7\n\n";

$context .= "Danh sách bác sĩ:\n";
foreach ($hospitalInfo['doctors'] as $doctor) {
    $context .= "- {$doctor['name']} (Chuyên khoa: {$doctor['department']})\n";
}

$context .= "\nDịch vụ chính: Khám tổng quát, Tim mạch, Xét nghiệm, Cấp cứu 24/7\n\n";
$context .= "Hãy trả lời câu hỏi sau một cách thân thiện và chuyên nghiệp. Nếu câu hỏi không liên quan đến y tế hoặc bệnh viện, hãy lịch sự chuyển hướng về dịch vụ y tế:\n\n";

$fullMessage = $context . "Câu hỏi: " . $userMessage;

// Tạo dữ liệu yêu cầu gửi đến API
$requestBody = json_encode([
    "contents" => [
        [
            "parts" => [
                ["text" => $fullMessage]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
]);

// Gửi yêu cầu đến Gemini API bằng cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);

// Kiểm tra lỗi cURL
if (curl_errno($ch)) {
    echo json_encode(["reply" => "Lỗi kết nối. Vui lòng thử lại sau."]);
    curl_close($ch);
    exit();
}

curl_close($ch);

// Giải mã phản hồi JSON từ Gemini
$responseData = json_decode($response, true);

// Xử lý phản hồi
if (isset($responseData["candidates"][0]["content"]["parts"][0]["text"])) {
    $botReply = cleanResponse($responseData["candidates"][0]["content"]["parts"][0]["text"]);
} elseif (isset($responseData["error"]["message"])) {
    $botReply = "Xin lỗi, tôi gặp vấn đề kỹ thuật. Vui lòng liên hệ hotline (0123) 456-789 để được hỗ trợ.";
} else {
    $botReply = "Xin lỗi, tôi không hiểu câu hỏi của bạn. Bạn có thể hỏi về thông tin bác sĩ, dịch vụ, địa chỉ hoặc đặt lịch khám.";
}

// Trả kết quả về frontend
echo json_encode(["reply" => $botReply]);
exit();
?>