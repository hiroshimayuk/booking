<?php
session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Kết nối CSDL
require_once 'app/config/database.php';
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem người dùng đã đăng nhập và có MaBenhNhan chưa
if (!$loggedInUser || !isset($_SESSION['user']['MaBenhNhan'])) {
    header("Location: view/login.php");
    exit;
}

$ma_benh_nhan = $_SESSION['user']['MaBenhNhan'];
$appointments = [];

// Lấy danh sách lịch hẹn của bệnh nhân
$sql = "SELECT lh.MaLich, lh.NgayGio, lh.TrieuChung, lh.TrangThai, bs.HoTen AS TenBacSi, k.TenKhoa
        FROM LichHen lh
        JOIN BacSi bs ON lh.MaBacSi = bs.MaBacSi
        JOIN Khoa k ON bs.MaKhoa = k.MaKhoa
        WHERE lh.MaBenhNhan = ?
        ORDER BY lh.NgayGio DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ma_benh_nhan);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch hẹn của tôi - Four Rock</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-gray: #f8fafc;
            --medium-gray: #e2e8f0;
            --dark-gray: #475569;
            --text-dark: #1e293b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--light-gray);
        }
        .container {
            max-width: 900px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
        }
        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        .table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }
        .table tbody tr:hover {
            background-color: var(--light-gray);
        }
        .status-pending {
            color: var(--warning-color);
            font-weight: 500;
        }
        .status-confirmed {
            color: var(--success-color);
            font-weight: 500;
        }
        .status-cancelled {
            color: var(--danger-color);
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .no-appointments {
            text-align: center;
            color: var(--dark-gray);
            font-style: italic;
        }
        @media (max-width: 576px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/view/header.php'; ?>
    <div class="container">
        <h2><i class="fas fa-calendar-alt mr-2"></i>Lịch hẹn của tôi</h2>

        <?php if (empty($appointments)): ?>
            <p class="no-appointments">Bạn chưa có lịch hẹn nào.</p>
            <div class="text-center">
                <a href="datlich.php?doctor_id=" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Đặt lịch hẹn mới
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bác sĩ</th>
                            <th>Chuyên khoa</th>
                            <th>Ngày giờ hẹn</th>
                            <th>Triệu chứng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $index => $appointment): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($appointment['TenBacSi']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['TenKhoa']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($appointment['NgayGio']))); ?></td>
                                <td><?php echo htmlspecialchars($appointment['TrieuChung']); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower(str_replace('Đã ', '', $appointment['TrangThai'])); ?>">
                                        <?php echo htmlspecialchars($appointment['TrangThai']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="../datlich.php" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Đặt lịch hẹn mới
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>