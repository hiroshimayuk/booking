<?php
session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$host = "localhost";
$user = "root";
$pass = "";
$db = "datlichkhambenh";
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy MaBacSi từ URL
$maBacSi = isset($_GET['MaBacSi']) ? intval($_GET['MaBacSi']) : 0;

// Lấy thông tin chi tiết bác sĩ
$sql = "SELECT b.MaBacSi, b.HoTen, b.MoTa, b.HinhAnhBacSi, k.TenKhoa,
        COALESCE(ROUND(AVG(d.DiemDanhGia), 1), 0) as avg_rating,
        COUNT(d.MaDanhGia) as total_reviews
        FROM bacsi b 
        LEFT JOIN khoa k ON b.MaKhoa = k.MaKhoa
        LEFT JOIN danhgia d ON b.MaBacSi = d.MaBacSi
        WHERE b.MaBacSi = ?
        GROUP BY b.MaBacSi";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $maBacSi);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Lấy đánh giá của bác sĩ
$sql_reviews = "SELECT d.*, bn.HoTen as TenBenhNhan
               FROM danhgia d
               LEFT JOIN benhnhan bn ON d.MaBenhNhan = bn.MaBenhNhan
               WHERE d.MaBacSi = ?
               ORDER BY d.NgayDanhGia DESC
               LIMIT 5";

$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $maBacSi);
$stmt_reviews->execute();
$reviews = $stmt_reviews->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($doctor['HoTen'] ?? 'Chi tiết bác sĩ'); ?> - Chi tiết bác sĩ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #1977cc;
            --secondary-color: #f8f9fa;
            --accent-color: #3fbbc0;
            --text-color: #2c4964;
            --text-dark: #2c4964;
            --light-color: #f8f9fa;
            --dark-color: #333;
            --light-gray: #f5f8fa;
            --medium-gray: #e9ecef;
            --shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Roboto', sans-serif;
            color: var(--text-color);
            background-color: var(--light-gray);
            padding-top: 76px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--medium-gray);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-color) !important;
            text-decoration: none;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
        }

        .navbar-nav .nav-link:hover {
            background-color: var(--light-gray);
            color: var(--primary-color) !important;
            transform: translateY(-1px);
        }

        .page-banner {
            background: linear-gradient(rgba(42, 57, 80, 0.7), rgba(42, 57, 80, 0.7)),
                url('../../assets/img/hospital-banner.jpg') center center;
            background-size: cover;
            padding: 80px 0;
            color: white;
            margin-bottom: 30px;
            border-radius: 12px;
        }

        .page-banner h1 {
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }

        .doctor-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
        }

        .doctor-profile-img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
        }

        .doctor-info {
            padding: 25px;
            text-align: center;
        }

        .doctor-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .specialty-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 15px;
        }

        .rating {
            color: #ffc107;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .content-section {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .content-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .review-card {
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
            background-color: var(--secondary-color);
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .review-author {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1rem;
        }

        .review-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .doctor-actions .btn {
            font-size: 1rem;
        }

        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 50px 0 20px;
            margin-top: 40px;
        }

        footer h5 {
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--accent-color);
            font-size: 1.25rem;
        }

        footer a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        footer a:hover {
            color: var(--accent-color);
        }

        footer .social-icons a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        footer .social-icons a:hover {
            background-color: var(--primary-color);
        }

        .contact-info {
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .contact-info i {
            width: 30px;
            color: var(--accent-color);
        }

        @media (max-width: 767px) {
            .page-banner {
                padding: 60px 0;
            }

            .page-banner h1 {
                font-size: 2rem;
            }

            .doctor-profile-img {
                height: 250px;
            }

            .doctor-name {
                font-size: 1.5rem;
            }

            .content-section h3 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-hospital-alt me-2"></i>Four Rock
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="hospital-blog.php"><i class="fas fa-blog me-1"></i>Blog</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="serviceDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-stethoscope me-1"></i>Dịch vụ
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="general-checkup.php"><i class="fas fa-user-md me-2"></i>Khám tổng quát</a></li>
                                <li><a class="dropdown-item" href="cardiology.php"><i class="fas fa-heartbeat me-2"></i>Tim mạch</a></li>
                                <li><a class="dropdown-item" href="testing.php"><i class="fas fa-vial me-2"></i>Xét nghiệm</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-info-circle me-1"></i>Giới thiệu
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="hospital-about.php"><i class="fas fa-building me-2"></i>Bệnh viện</a></li>
                                <li><a class="dropdown-item" href="doctor.php"><i class="fas fa-user-doctor me-2"></i>Bác sĩ</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="qa_doctor.php"><i class="fas fa-question-circle me-1"></i>Hỏi đáp</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php"><i class="fas fa-phone me-1"></i>Liên hệ</a>
                        </li>
                        <?php if ($loggedInUser): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Xin chào, <?php echo htmlspecialchars($loggedInUser["TenDangNhap"]); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="view/change_password.php">Đổi mật khẩu</a></li>
                                    <li><a class="dropdown-item" href="view/update_profile.php">Thay đổi thông tin cá nhân</a></li>
                                    <li><a class="dropdown-item" href="view_appointments.php">Xem lịch</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="view/logout.php">Đăng xuất</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="btn btn-primary me-2" href="view/register.php">Đăng ký</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-outline-primary" href="view/login.php">Đăng nhập</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Page Banner -->
    <div class="page-banner">
        <div class="container text-center">
            <h1><i class="fas fa-user-md me-2"></i>Chi tiết bác sĩ</h1>
            <p class="lead">Tìm hiểu về bác sĩ và đánh giá từ bệnh nhân</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <?php if ($doctor): ?>
            <div class="row">
                <!-- Thông tin bác sĩ - Phía bên trái -->
                <div class="col-lg-4 mb-4">
                    <div class="doctor-card h-100">
                        <div class="text-center p-3">
                            <?php
                            if (!empty($doctor['HinhAnhBacSi'])) {
                                $imagePath = str_replace('/booking/', '/', $doctor['HinhAnhBacSi']);
                                $imagePath = '/' . ltrim($imagePath, '/');
                            ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>"
                                    alt="<?= htmlspecialchars($doctor['HoTen']) ?>"
                                    class="doctor-profile-img mb-3"
                                    onerror="this.src='/public/uploads/default.png'">
                            <?php } else { ?>
                                <img src="/public/uploads/default.png"
                                    alt="Ảnh mặc định"
                                    class="doctor-profile-img mb-3">
                            <?php } ?>
                        </div>

                        <div class="doctor-info">
                            <h2 class="doctor-name"><?php echo htmlspecialchars($doctor['HoTen']); ?></h2>
                            <span class="specialty-badge">
                                <i class="fas fa-clinic-medical me-1"></i>
                                <?php echo htmlspecialchars($doctor['TenKhoa'] ?? 'Chưa có chuyên khoa'); ?>
                            </span>

                            <div class="rating">
                                <?php
                                $avg = round($doctor['avg_rating'], 1);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $avg) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 <= $avg) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                echo " <span class='text-muted'>($avg / {$doctor['total_reviews']} đánh giá)</span>";
                                ?>
                            </div>

                            <div class="doctor-actions">
                                <a href="datlich.php?doctor_id=<?= htmlspecialchars($doctor['MaBacSi']) ?>&doctor_name=<?= urlencode($doctor['HoTen']) ?>"
                                    class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-calendar-check me-2"></i>Đặt lịch khám
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin chi tiết - Phía bên phải -->
                <div class="col-lg-8">
                    <!-- Mô tả -->
                    <div class="content-section">
                        <h3><i class="fas fa-info-circle me-2"></i>Mô tả</h3>
                        <div class="doctor-description">
                            <?php if (!empty($doctor['MoTa'])): ?>
                                <p><?php echo strip_tags($doctor['MoTa'], '<p><br>'); ?></p>
                            <?php else: ?>
                                <p class="text-muted fst-italic">Chưa có thông tin mô tả.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Đánh giá từ bệnh nhân -->
                    <div class="content-section">
                        <h3><i class="fas fa-comments me-2"></i>Đánh giá từ bệnh nhân</h3>
                        <?php if ($reviews && $reviews->num_rows > 0): ?>
                            <?php while ($review = $reviews->fetch_assoc()): ?>
                                <div class="review-card">
                                    <div class="rating mb-2">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['DiemDanhGia']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="review-text mb-3"><?php echo htmlspecialchars($review['NhanXet']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="review-author">
                                            <i class="fas fa-user-circle me-1"></i>
                                            <?php echo htmlspecialchars($review['TenBenhNhan']); ?>
                                        </span>
                                        <span class="review-date">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($review['NgayDanhGia'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="far fa-comment-dots fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có đánh giá nào.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Không tìm thấy thông tin bác sĩ.
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Hệ Thống Đặt Lịch Khám Bệnh</h5>
                    <p>Chăm sóc sức khỏe tận tâm, đặt lịch dễ dàng, theo dõi tiện lợi.</p>
                    <div class="social-icons mt-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Thông tin liên hệ</h5>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Đường Khám Bệnh, Thành phố XYZ</p>
                        <p><i class="fas fa-phone"></i> 0123 456 789</p>
                        <p><i class="fas fa-envelope"></i> info@khambenh.com</p>
                        <p><i class="fas fa-clock"></i> Thứ 2 - Thứ 6: 8:00 - 17:00</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php"><i class="fas fa-angle-right me-2"></i>Trang chủ</a></li>
                        <li><a href="view/doctor.php"><i class="fas fa-angle-right me-2"></i>Bác sĩ</a></li>
                        <li><a href="general-checkup.html"><i class="fas fa-angle-right me-2"></i>Dịch vụ</a></li>
                        <li><a href="#contact"><i class="fas fa-angle-right me-2"></i>Liên hệ</a></li>
                        <li><a href="view/register.php"><i class="fas fa-angle-right me-2"></i>Đăng ký</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4" style="background-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">© 2025 Hệ Thống Đặt Lịch Khám Bệnh. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>
```