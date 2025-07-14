<?php
session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$customer_id = isset($_SESSION['user']['MaNguoiDung']) ? $_SESSION['user']['MaNguoiDung'] : null;
$customer_name = isset($_SESSION['user']['TenDangNhap']) ? $_SESSION['user']['TenDangNhap'] : "Khách";
$cskh_id = 1; // ID cố định của CSKH

// Kết nối CSDL
require_once 'app/config/database.php';

// Lấy danh sách bác sĩ và chuyên khoa
$doctors = [];
$sql = "SELECT bs.MaBacSi, bs.HoTen, bs.MoTa, bs.HinhAnhBacSi, k.TenKhoa 
        FROM BacSi bs 
        LEFT JOIN Khoa k ON bs.MaKhoa = k.MaKhoa";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Four Rock - Chăm sóc sức khỏe hàng đầu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #0ea5e9;
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #ffffff;
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

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .hero-section {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .hero-section .carousel-item img {
            height: 100vh;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .carousel-caption {
            bottom: 30%;
            left: 5%;
            right: auto;
            text-align: left;
            max-width: 600px;
        }

        .carousel-caption h2 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .carousel-caption p {
            font-size: 1.3rem;
            font-weight: 400;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            opacity: 0.95;
        }

        #services, #doctors, #about, #news, #contact {
            padding: 5rem 0;
            background: var(--light-gray);
        }

        #services .container, #doctors .container, #about .container, #news .container, #contact .container {
            border: 1px solid var(--medium-gray);
            border-radius: 12px;
            padding: 2rem;
            background: white;
        }

        #services h2, #doctors h2, #about h2, #news h2, #contact h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
        }

        #services h2::after, #doctors h2::after, #news h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        #services .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        #services .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        #services .card-body {
            padding: 2.5rem 2rem;
        }

        #services .card-body i {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
        }

        #services .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        #services .card-text {
            color: var(--dark-gray);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .doctors-list {
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
            gap: 1.5rem;
            padding-bottom: 1rem;
        }

        .doctor-card {
            width: 100%;
            max-width: 280px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            flex: 0 0 calc(25% - 1.5rem);
            max-width: calc(25% - 1.5rem);
        }

        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .doctor-card img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
            border-bottom: none;
        }

        .doctor-card .doctor-info {
            padding: 1.5rem;
            text-align: center;
        }

        .doctor-card h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
            color: var(--text-dark);
        }

        .doctor-card .specialty {
            font-size: 1rem;
            color: var(--primary-color);
            margin: 0;
        }

        .doctors-nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            z-index: 10;
        }

        .doctors-nav-button:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-50%) scale(1.1);
        }

        .doctors-nav-button.prev {
            left: 0;
        }

        .doctors-nav-button.next {
            right: 0;
        }

        #about p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--dark-gray);
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        #news .card {
            border: 10px solid transparent;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        #news .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        #news .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        #news .card:hover .card-img-top {
            transform: scale(1.05);
        }

        #news .card-body {
            padding: 1.5rem;
        }

        #news .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .contact-info {
            padding: 2rem;
            border-radius: 12px;
            background: var(--light-gray);
            transition: all 0.3s ease;
            height: 100%;
        }

        .contact-info:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .contact-info i {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .contact-info p {
            font-weight: 500;
            color: var(--text-dark);
            margin: 0;
        }

        .map-responsive iframe {
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        footer {
            background: linear-gradient(135deg, var(--text-dark), #334155);
            padding: 2rem 0;
        }

        footer p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }

        #chat-icon,
        #chatbot-icon {
            position: fixed;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            font-size: 24px;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        #chat-icon {
            bottom: 90px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        #chatbot-icon {
            bottom: 20px;
            background: linear-gradient(135deg, var(--success-color), #10b981);
            color: white;
        }

        #chat-icon:hover,
        #chatbot-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        #chat-box,
        #chatbot-box {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 320px;
            max-height: 450px;
            background: white;
            border: none;
            border-radius: 16px;
            display: none;
            flex-direction: column;
            z-index: 9999;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        #chatbot-box {
            bottom: 20px;
        }

        #chat-header,
        #chatbot-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #chatbot-header {
            background: linear-gradient(135deg, var(--success-color), #10b981);
        }

        #chat-messages,
        #chatbot-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            max-height: 300px;
            background: #f8fafc;
        }

        #chat-input,
        #chatbot-input {
            border-top: 1px solid var(--medium-gray);
            display: flex;
            background: white;
        }

        #chat-input input,
        #chatbot-input input {
            flex: 1;
            border: none;
            padding: 1rem;
            font-size: 0.9rem;
            outline: none;
        }

        #chat-input button,
        #chatbot-input button {
            border: none;
            padding: 1rem 1.5rem;
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        #chatbot-input button {
            background: var(--success-color);
        }

        #chat-input button:hover {
            background: var(--secondary-color);
        }

        #chatbot-input button:hover {
            background: #047857;
        }

        #chatbot-messages .message {
            margin-bottom: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            line-height: 1.4;
            max-width: 85%;
            word-wrap: break-word;
        }

        #chatbot-messages .user-message {
            text-align: right;
            margin-left: auto;
            background: #c8e6c9;
        }

        #chatbot-messages .bot-message {
            text-align: left;
            margin-right: auto;
            background: #e8f5e8;
        }

        #chatbot-messages .message strong {
            font-weight: 600;
            color: var(--text-dark);
        }

        #chatbot-messages .typing-indicator {
            text-align: left;
            margin-bottom: 12px;
            padding: 10px 12px;
            background: #f1f8e9;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .carousel-caption h2 {
                font-size: 2.5rem;
            }

            .carousel-caption p {
                font-size: 1.1rem;
            }

            .hero-section {
                height: auto;
                min-height: 100vh;
            }

            #services h2,
            #doctors h2,
            #about h2,
            #news h2,
            #contact h2 {
                font-size: 2rem;
            }

            #chat-box,
            #chatbot-box {
                width: 280px;
                right: 10px;
            }

            #chat-icon,
            #chatbot-icon {
                right: 10px;
                width: 50px;
                height: 50px;
                line-height: 50px;
                font-size: 20px;
            }

            .doctors-container {
                padding: 0 40px;
            }

            .doctors-list {
                gap: 1rem;
            }

            .doctor-card {
                flex: 0 0 calc(50% - 1.5rem);
                max-width: calc(50% - 1.5rem);
            }

            .doctor-card img {
                width: 150px;
                height: 150px;
            }

            .doctors-nav-button {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .doctors-nav-button.prev {
                left: 5px;
            }

            .doctors-nav-button.next {
                right: 5px;
            }
        }

        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/view/header.php'; ?>
    <section id="hero" class="hero-section">
        <div id="heroCarousel" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
                <li data-target="#heroCarousel" data-slide-to="1"></li>
                <li data-target="#heroCarousel" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="public/images/slide1.png" class="d-block w-100" alt="Slide 1">
                    <div class="carousel-caption d-none d-md-block">
                        <h2>Chăm sóc sức khỏe<br>toàn diện</h2>
                        <p>Tiên phong trong đổi mới dịch vụ y tế với công nghệ hiện đại</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="public/images/slide2.png" class="d-block w-100" alt="Slide 2">
                    <div class="carousel-caption d-none d-md-block">
                        <h2>Công nghệ<br>hiện đại</h2>
                        <p>Ứng dụng thiết bị y tế tiên tiến và trí tuệ nhân tạo</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="public/images/slide3.png" class="d-block w-100" alt="Slide 3">
                    <div class="carousel-caption d-none d-md-block">
                        <h2>Đội ngũ bác sĩ<br>chuyên nghiệp</h2>
                        <p>Luôn sẵn sàng chăm sóc sức khỏe của bạn 24/7</p>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="fade-in">
        <div class="container text-center">
            <h2>Dịch vụ nổi bật</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-stethoscope fa-4x mb-3"></i>
                            <h4 class="card-title">Khám tổng quát</h4>
                            <p class="card-text">Khám sức khỏe ban đầu, phát hiện sớm các bệnh lý tiềm ẩn với đội ngũ bác sĩ chuyên nghiệp.</p>
                            <a href="general-checkup.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right mr-2"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-heartbeat fa-4x mb-3"></i>
                            <h4 class="card-title">Tim mạch</h4>
                            <p class="card-text">Giám sát và điều trị bệnh về tim mạch với chuyên gia hàng đầu cùng thiết bị hiện đại.</p>
                            <a href="cardiology.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right mr-2"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-vial fa-4x mb-3"></i>
                            <h4 class="card-title">Xét nghiệm</h4>
                            <p class="card-text">Cung cấp các dịch vụ xét nghiệm hiện đại với độ chính xác cao và kết quả nhanh chóng.</p>
                            <a href="testing.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right mr-2"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors" class="fade-in">
        <div class="container">
            <h2 class="text-center">Đội ngũ bác sĩ</h2>
            <div class="doctors-container position-relative">
                <button class="doctors-nav-button prev" onclick="scrollDoctors(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="doctors-list">
                    <?php if (empty($doctors)): ?>
                        <p class="text-center text-muted">Hiện tại chưa có thông tin bác sĩ.</p>
                    <?php else: ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <?php
                            $imagePath = !empty($doctor['HinhAnhBacSi']) ? htmlspecialchars($doctor['HinhAnhBacSi']) : '/public/images/doctors/default.jpg';
                            if (strpos($imagePath, '/') !== 0) {
                                $imagePath = '/' . $imagePath;
                            }
                            ?>
                            <a href="datlich.php?doctor_id=<?php echo htmlspecialchars($doctor['MaBacSi']); ?>&doctor_name=<?php echo urlencode($doctor['HoTen']); ?>" class="doctor-card">
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($doctor['HoTen']); ?>" onerror="this.src='/public/images/doctors/fallback.jpg';">
                                <div class="doctor-info">
                                    <h5><?php echo htmlspecialchars($doctor['HoTen']); ?></h5>
                                    <div class="specialty"><?php echo htmlspecialchars($doctor['TenKhoa'] ?: 'Chuyên khoa không xác định'); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button class="doctors-nav-button next" onclick="scrollDoctors(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="fade-in">
        <div class="container text-center">
            <h2>Giới thiệu về Four Rock</h2>
            <p>Four Rock tự hào là địa chỉ chăm sóc sức khỏe hàng đầu với cơ sở vật chất hiện đại, đội ngũ bác sĩ chuyên nghiệp và dịch vụ y tế toàn diện. Chúng tôi cam kết mang lại sự an tâm và hài lòng cho bệnh nhân thông qua chất lượng dịch vụ và ứng dụng công nghệ tiên tiến.</p>
            <div class="row mt-4">
                <div class="col-md-3 mb-3">
                    <div class="text-center">
                        <i class="fas fa-award fa-3x text-primary mb-2"></i>
                        <h4>15+</h4>
                        <p>Năm kinh nghiệm</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-center">
                        <i class="fas fa-user-doctor fa-3x text-primary mb-2"></i>
                        <h4>50+</h4>
                        <p>Bác sĩ chuyên khoa</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-center">
                        <i class="fas fa-users fa-3x text-primary mb-2"></i>
                        <h4>10,000+</h4>
                        <p>Bệnh nhân tin tưởng</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-center">
                        <i class="fas fa-star fa-3x text-primary mb-2"></i>
                        <h4>4.9/5</h4>
                        <p>Đánh giá từ khách hàng</p>
                    </div>
                </div>
            </div>
            <a href="view/register.php" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-user-plus mr-2"></i>Đăng ký ngay
            </a>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="fade-in">
        <div class="container">
            <h2 class="text-center">Tin tức & Sự kiện</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="public/images/new1.png" class="card-img-top" alt="Tin tức 1">
                        <div class="card-body">
                            <span class="badge badge-primary mb-2">Tin tức</span>
                            <h4 class="card-title">Hành trình 7 năm đồng hành cùng khách hàng</h4>
                            <p class="card-text">Four Rock kỷ niệm 7 năm với các cột mốc ấn tượng trong việc chăm sóc sức khỏe cộng đồng.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>15/05/2024
                                </small>
                                <a href="#" class="btn btn-primary btn-sm">
                                    <i class="fas fa-read mr-1"></i>Đọc tiếp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="public/images/new2.png" class="card-img-top" alt="Tin tức 2">
                        <div class="card-body">
                            <span class="badge badge-success mb-2">Sự kiện</span>
                            <h4 class="card-title">Triển lãm công nghệ y tế 2025</h4>
                            <p class="card-text">Cập nhật xu hướng công nghệ mới trong ngành y tế và các thiết bị hiện đại nhất.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>10/05/2024
                                </small>
                                <a href="#" class="btn btn-primary btn-sm">
                                    <i class="fas fa-read mr-1"></i>Đọc tiếp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="public/images/new3.png" class="card-img-top" alt="Tin tức 3">
                        <div class="card-body">
                            <span class="badge badge-warning mb-2">Khuyến mãi</span>
                            <h4 class="card-title">Chương trình khuyến mãi đặc biệt</h4>
                            <p class="card-text">Những ưu đãi hấp dẫn dành cho khách hàng thân thiết trong tháng 5.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>05/05/2024
                                </small>
                                <a href="#" class="btn btn-primary btn-sm">
                                    <i class="fas fa-read mr-1"></i>Đọc tiếp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="fade-in">
        <div class="container">
            <h2 class="text-center">Liên hệ với chúng tôi</h2>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="contact-info">
                        <a href="https://www.google.com/maps" target="_blank" class="text-decoration-none">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                            <h5 class="mt-2">Địa chỉ</h5>
                            <p>Khu E Hutech, Quận 9<br>TP. Hồ Chí Minh</p>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="contact-info">
                        <i class="fas fa-phone fa-2x"></i>
                        <h5 class="mt-2">Hotline</h5>
                        <p>(0123) 456-789<br>Hỗ trợ 24/7</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="contact-info">
                        <i class="fas fa-envelope fa-2x"></i>
                        <h5 class="mt-2">Email</h5>
                        <p>info@fourrock.com<br>support@fourrock.com</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="map-responsive">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.2353806617543!2d106.78303187508985!3d10.855738189297968!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175276e7ea103df%3A0xb6cf10bb7d719327!2zSFVURUNIIC0gxJDhuqFpIGjhu41jIEPDtG5nIG5naOG7hyBUUC5IQ00gKFRodSBEdWMgQ2FtcHVzKQ!5e1!3m2!1svi!2s!4v1749117069821!5m2!1svi!2s"                         
                            style="border:0; width:100%; height:350px;"
                            allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-paper-plane mr-2"></i>Gửi tin nhắn</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Họ và tên" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="form-group">
                                    <input type="tel" class="form-control" placeholder="Số điện thoại" required>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" rows="4" placeholder="Nội dung tin nhắn" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-send mr-2"></i>Gửi tin nhắn
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-white text-center">
        <div class="container">
            <div class="row py-4">
                <div class="col-md-4">
                    <h5><i class="fas fa-hospital-alt mr-2"></i>Four Rock</h5>
                    <p>Chăm sóc sức khỏe hàng đầu với công nghệ hiện đại và đội ngũ y bác sĩ chuyên nghiệp.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="#services" class="text-white-50">Dịch vụ</a></li>
                        <li><a href="#about" class="text-white-50">Giới thiệu</a></li>
                        <li><a href="#news" class="text-white-50">Tin tức</a></li>
                        <li><a href="#contact" class="text-white-50">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Theo dõi chúng tôi</h5>
                    <div class="social-links">
                        <a href="#" class="text-white-50 mr-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white-50 mr-3"><i class="fab fa-youtube fa-2x"></i></a>
                        <a href="#" class="text-white-50 mr-3"><i class="fab fa-instagram fa-2x"></i></a>
                        <a href="#" class="text-white-50"><i class="fab fa-twitter fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-white-50">
            <p>© 2024 Four Rock Hospital. All Rights Reserved. | Thiết kế bởi Four Rock Team</p>
        </div>
    </footer>

    <!-- Chat với CSKH -->
    <div id="chat-icon" title="Chat với CSKH">💬</div>
    <div id="chat-box">
        <div id="chat-header">
            <span><i class="fas fa-headset mr-2"></i>Chat với CSKH</span>
            <button onclick="toggleChatBox()" style="background:none; border:none; color:white; font-size:18px; cursor:pointer;">×</button>
        </div>
        <div id="chat-messages">
            <!-- Tin nhắn sẽ được load ở đây -->
        </div>
        <div id="chat-input">
            <input type="text" id="messageInput" placeholder="Nhập tin nhắn...">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Chatbot -->
    <div id="chatbot-icon" title="Chat với AI">🤖</div>
    <div id="chatbot-box">
        <div id="chatbot-header">
            <span><i class="fas fa-robot mr-2"></i>Chat với AI</span>
            <button onclick="toggleChatbotBox()" style="background:none; border:none; color:white; font-size:18px;">×</button>
        </div>
        <div id="chatbot-messages">
            <!-- Tin nhắn sẽ được load ở đây -->
        </div>
        <div id="chatbot-input">
            <input type="text" id="chatbotMessageInput" placeholder="Nhập tin nhắn...">
            <button onclick="sendChatbotMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        const customer_id = <?php echo isset($_SESSION['user']['MaNguoiDung']) ? $_SESSION['user']['MaNguoiDung'] : 'null'; ?>;
        const cskh_id = 1;

        function loadMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "controller/ChatController.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            let msgsHtml = "";
                            res.messages.forEach(function(msg) {
                                if (parseInt(msg.sender_id) === customer_id) {
                                    msgsHtml += `<div style='text-align:right; margin-bottom:10px; padding:8px; background:#bbdefb; border-radius:8px;'><strong>Bạn:</strong> ${msg.message}</div>`;
                                } else {
                                    msgsHtml += `<div style='text-align:left; margin-bottom:10px; padding:8px; background:#e3f2fd; border-radius:8px;'><strong>CSKH:</strong> ${msg.message}</div>`;
                                }
                            });
                            document.getElementById("chat-messages").innerHTML = msgsHtml;
                            document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
                        } else {
                            document.getElementById("chat-messages").innerHTML = "<div>Không có tin nhắn nào.</div>";
                        }
                    } catch (e) {
                        console.error("Lỗi parse JSON:", e);
                    }
                }
            };
            xhr.send(`action=getMessages&user1=${customer_id}&user2=${cskh_id}`);
        }

        function sendMessage() {
            if (!customer_id || customer_id === null) {
                window.location.href = 'view/login.php';
                return;
            }

            const message = document.getElementById("messageInput").value.trim();
            if (message === "") {
                alert("Vui lòng nhập tin nhắn!");
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "controller/ChatController.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            document.getElementById("messageInput").value = "";
                            loadMessages();
                        } else {
                            alert(res.message || "Không thể gửi tin nhắn!");
                        }
                    } catch (e) {
                        console.error("Lỗi parse JSON:", e);
                    }
                }
            };
            xhr.send(`action=sendMessage&sender_id=${customer_id}&receiver_id=${cskh_id}&message=${encodeURIComponent(message)}`);
        }

        let chatBoxVisible = false;

        function toggleChatBox() {
            if (!customer_id || customer_id === null) {
                window.location.href = 'view/login.php';
                return;
            }

            const chatBox = document.getElementById("chat-box");
            chatBoxVisible = !chatBoxVisible;
            chatBox.style.display = chatBoxVisible ? "flex" : "none";

            if (chatBoxVisible) {
                loadMessages();
                const chatMessages = document.getElementById("chat-messages");
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        document.getElementById("chat-icon").addEventListener("click", toggleChatBox);

        document.getElementById("messageInput").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                sendMessage();
            }
        });

        setInterval(function() {
            const chatBox = document.getElementById("chat-box");
            if (window.getComputedStyle(chatBox).display === "flex") {
                loadMessages();
            }
        }, 5000);

        let chatbotVisible = false;

        function toggleChatbotBox() {
            const chatbotBox = document.getElementById("chatbot-box");
            chatbotVisible = !chatbotVisible;
            chatbotBox.style.display = chatbotVisible ? "flex" : "none";
            if (chatbotVisible) {
                const chatbotMessages = document.getElementById("chatbot-messages");
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
        }

        document.getElementById("chatbot-icon").addEventListener("click", toggleChatbotBox);

        function sendChatbotMessage() {
            const messageInput = document.getElementById("chatbotMessageInput");
            const message = messageInput.value.trim();
            const chatbotMessages = document.getElementById("chatbot-messages");

            if (!message) {
                alert("Vui lòng nhập tin nhắn!");
                return;
            }

            // Hiển thị tin nhắn của người dùng
            chatbotMessages.innerHTML += `
                <div class="message user-message">
                    <strong>Bạn:</strong> ${message}
                </div>`;

            // Hiển thị chỉ báo "Đang nhập..."
            const typing = document.createElement("div");
            typing.id = "typing-indicator";
            typing.className = "typing-indicator";
            typing.innerHTML = "<strong>AI:</strong> <em>Đang nhập...</em>";
            chatbotMessages.appendChild(typing);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            messageInput.value = "";

            // Gửi yêu cầu AJAX đến chatbot.php
            $.ajax({
                url: 'chatbot.php',
                type: 'POST',
                data: { message: message },
                dataType: 'json',
                success: function(response) {
                    // Xóa chỉ báo "Đang nhập..."
                    const typing = document.getElementById("typing-indicator");
                    if (typing) typing.remove();

                    // Hiển thị phản hồi từ AI
                    if (response.reply) {
                        // Thay thế ký tự xuống dòng (\n) thành thẻ <br> để hiển thị đúng định dạng
                        const formattedReply = response.reply.replace(/\n/g, '<br>');
                        chatbotMessages.innerHTML += `
                            <div class="message bot-message">
                                <strong>AI:</strong> ${formattedReply}
                            </div>`;
                    } else {
                        chatbotMessages.innerHTML += `
                            <div class="message bot-message">
                                <strong>AI:</strong> Có lỗi xảy ra. Vui lòng thử lại sau.
                            </div>`;
                    }
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                },
                error: function(xhr, status, error) {
                    // Xóa chỉ báo "Đang nhập..."
                    const typing = document.getElementById("typing-indicator");
                    if (typing) typing.remove();

                    // Hiển thị thông báo lỗi
                    chatbotMessages.innerHTML += `
                        <div class="message bot-message">
                            <strong>AI:</strong> Lỗi kết nối với máy chủ. Vui lòng thử lại sau.
                        </div>`;
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                }
            });
        }

        document.getElementById("chatbotMessageInput").addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                sendChatbotMessage();
            }
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        function scrollDoctors(direction) {
            const list = document.querySelector('.doctors-list');
            const cardWidth = document.querySelector('.doctor-card').offsetWidth;
            const gap = 24;
            const scrollAmount = (cardWidth + gap) * 4;

            list.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }

        const doctorsList = document.querySelector('.doctors-list');
        const prevButton = document.querySelector('.doctors-nav-button.prev');
        const nextButton = document.querySelector('.doctors-nav-button.next');

        doctorsList.addEventListener('scroll', () => {
            const { scrollLeft, scrollWidth, clientWidth } = doctorsList;
            prevButton.style.display = scrollLeft > 0 ? 'flex' : 'none';
            nextButton.style.display = scrollLeft + clientWidth < scrollWidth - 10 ? 'flex' : 'none';
        });

        prevButton.style.display = 'none';
        if (doctorsList.scrollWidth > doctorsList.clientWidth) {
            nextButton.style.display = 'flex';
        }
    </script>
</body>

</html> 