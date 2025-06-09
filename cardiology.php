<?php
session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Kết nối CSDL
require_once 'app/config/database.php';

// Lấy danh sách bác sĩ
$doctors = [];
$sql = "SELECT bs.MaBacSi, bs.HoTen FROM BacSi bs ORDER BY bs.HoTen ASC";
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
    <title>Tim Mạch - Four Rock</title>

    <!-- Google Fonts & Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/style.css">

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

        /* Enhanced Navigation */
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

        /* Enhanced Hero Section */
        .hero-section {
            position: relative;
            height: 60vh;
            overflow: hidden;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('public/images/cardiology-hero.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-section p {
            font-size: 1.3rem;
            font-weight: 400;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            opacity: 0.95;
        }

        /* Service Introduction Section */
        #service-intro {
            padding: 5rem 0;
            background: white;
        }

        #service-intro h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
        }

        #service-intro h2::after {
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

        #service-intro img {
            border-radius: 16px;
            box-shadow: var(--shadow);
            max-width: 100%;
        }

        /* Process Section */
        #process {
            padding: 5rem 0;
            background: var(--light-gray);
        }

        #process h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
        }

        #process h2::after {
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

        #process .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        #process .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        #process .card-body {
            padding: 2rem;
            text-align: center;
        }

        #process .card-body i {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
        }

        #process .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        /* Benefits Section */
        #benefits {
            padding: 5rem 0;
            background: white;
        }

        #benefits h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
        }

        #benefits h2::after {
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

        #benefits .list-group-item {
            border: none;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            color: var(--dark-gray);
            background: var(--light-gray);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        #benefits .list-group-item:hover {
            background: var(--medium-gray);
            transform: translateX(5px);
        }

        /* CTA Section */
        #cta {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        #cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        #cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        #cta .btn {
            font-size: 1rem;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Enhanced Footer */
        footer {
            background: linear-gradient(135deg, var(--text-dark), #334155);
            padding: 2rem 0;
        }

        footer p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1.1rem;
            }

            .registration-overlay {
                position: static;
                margin: 2rem auto;
                max-width: 100%;
                padding: 0 1rem;
            }

            .hero-section {
                height: 50vh;
            }

            #service-intro h2,
            #process h2,
            #benefits h2,
            #cta h2 {
                font-size: 2rem;
            }

            
        }

        /* Scroll animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Loading states */
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
            0% { left: -100%; }
            100% { left: 100%; }
        }
    </style>
</head>

<body>
    <!-- Header Navigation -->
    <?php include __DIR__ . '/view/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section fade-in">
        <div class="container">
            <h1>Chăm sóc Tim Mạch</h1>
            <p>Giải pháp toàn diện cho sức khỏe tim mạch của bạn</p>
        </div>
    </section>

    <!-- Service Introduction Section -->
    <section id="service-intro" class="fade-in">
        <div class="container">
            <h2>Giới thiệu dịch vụ</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="public/images/timmach.png" alt="Dịch vụ Tim Mạch" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <h3>Định hướng chăm sóc tim mạch</h3>
                    <p>Tại Four Rock, chúng tôi tập trung vào việc sử dụng công nghệ tiên tiến để đánh giá và cải thiện tình trạng tim mạch của bạn. Dịch vụ của chúng tôi bao gồm:</p>
                    <ul>
                        <li>Khám và kiểm tra chức năng tim qua siêu âm và EKG.</li>
                        <li>Phân tích dữ liệu tim mạch bằng các thiết bị hiện đại.</li>
                        <li>Hỗ trợ tư vấn điều trị và theo dõi liên tục.</li>
                    </ul>
                    <p>Quá trình chăm sóc được thực hiện bởi đội ngũ bác sĩ chuyên nghiệp, tận tâm nhằm mang lại sự an tâm và sức khỏe tốt nhất.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section id="process" class="fade-in">
        <div class="container">
            <h2>Quy trình chăm sóc tim mạch</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-notes-medical fa-4x mb-3"></i>
                            <h4 class="card-title">Bước 1: Khám sơ bộ</h4>
                            <p class="card-text">Đánh giá mức độ và dấu hiệu bất thường của tim.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-heart fa-4x mb-3"></i>
                            <h4 class="card-title">Bước 2: Chẩn đoán</h4>
                            <p class="card-text">Sử dụng công nghệ hiện đại để xác định tình trạng tim mạch.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-hand-holding-heart fa-4x mb-3"></i>
                            <h4 class="card-title">Bước 3: Điều trị & Tư vấn</h4>
                            <p class="card-text">Bác sĩ chuyên khoa sẽ đưa ra phác đồ điều trị phù hợp.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="fade-in">
        <div class="container">
            <h2>Lợi ích dịch vụ</h2>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Phát hiện sớm các vấn đề tim mạch tiềm ẩn.</li>
                <li class="list-group-item">Theo dõi và điều trị tim mạch hiệu quả với công nghệ tiên tiến.</li>
                <li class="list-group-item">Tư vấn cá nhân hóa từ các bác sĩ chuyên khoa tim mạch.</li>
                <li class="list-group-item">Tăng cường sức khỏe tim mạch và chất lượng cuộc sống.</li>
            </ul>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="cta" class="fade-in">
        <div class="container">
            <h2>Đăng ký khám tim mạch ngay hôm nay!</h2>
            <p>Hãy đặt lịch để được khám và tư vấn bởi các chuyên gia hàng đầu.</p>
            <a href="view/register.php" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus mr-2"></i>Đăng ký ngay
            </a>
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
                        <li><a href="index.php#services" class="text-white-50">Dịch vụ</a></li>
                        <li><a href="index.php#about" class="text-white-50">Giới thiệu</a></li>
                        <li><a href="hospital-blog.php" class="text-white-50">Blog</a></li>
                        <li><a href="index.php#contact" class="text-white-50">Liên hệ</a></li>
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
            <p>© 2025 Four Rock Hospital. All Rights Reserved. | Thiết kế bởi Four Rock Team</p>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Scroll animations
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
        // Smooth scrolling for anchor links
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

        // Initialize tooltips
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

</html>