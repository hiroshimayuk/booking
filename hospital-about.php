```php
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
    <title>Giới thiệu Bệnh viện - Four Rock</title>

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
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('public/images/hospital-hero.jpg') no-repeat center center;
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

        /* Enhanced Registration Form */
        .registration-overlay {
            position: fixed;
            top: 80px;
            right: 3%;
            width: 90%;
            max-width: 320px;
            z-index: 2;
            animation: slideInRight 0.8s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .registration-overlay .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .registration-overlay .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem;
            text-align: center;
            border: none;
        }

        .registration-overlay .card-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
        }

        .registration-overlay .card-body {
            padding: 1.25rem;
        }

        .registration-overlay .form-control {
            font-size: 0.85rem;
            padding: 0.65rem;
            border-radius: 8px;
            border: 2px solid var(--medium-gray);
            transition: all 0.3s ease;
            background-color: white;
        }

        .registration-overlay .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .registration-overlay .form-group {
            margin-bottom: 0.75rem;
        }

        .registration-overlay label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }

        .registration-overlay .btn {
            font-size: 0.9rem;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Section Styling */
        #introduction,
        #history,
        #facilities,
        #team {
            padding: 5rem 0;
            background: white;
        }

        #history,
        #team {
            background: var(--light-gray);
        }

        section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
        }

        section h2::after {
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

        section img {
            border-radius: 16px;
            box-shadow: var(--shadow);
            max-width: 100%;
        }

        /* Team Section */
        #team .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        #team .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        #team .card-body {
            padding: 2rem;
            text-align: center;
        }

        #team .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
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

        /* Enhanced Chat Features */
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

            section h2 {
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
    <!-- Header Navigation -->
    <?php include __DIR__ . '/view/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section fade-in">
        <div class="container">
            <h1>Giới thiệu Bệnh viện Four Rock</h1>
            <p>Chăm sóc sức khỏe toàn diện với công nghệ tiên tiến và đội ngũ tận tâm</p>
        </div>
    </section>

    <!-- Introduction Section -->
    <section id="introduction" class="fade-in">
        <div class="container">
            <h2>Về Bệnh viện Four Rock</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="public/images/hospital.png" alt="Bệnh viện Four Rock" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <h3 style="text-align: center;">Sứ mệnh và tầm nhìn</h3>
                    <p style="text-indent: 2em;">Bệnh viện Four Rock được thành lập với sứ mệnh mang lại dịch vụ chăm sóc sức khỏe chất lượng cao, kết hợp công nghệ tiên tiến và sự tận tâm của đội ngũ y tế. Chúng tôi hướng đến:</p>
                    <ul style="list-style-type: none; padding-left: 2em;">
                        <li>Đem lại trải nghiệm chăm sóc sức khỏe tốt nhất cho bệnh nhân.</li>
                        <li>Cung cấp dịch vụ y tế toàn diện, chính xác và nhanh chóng.</li>
                        <li>Ứng dụng công nghệ hiện đại để nâng cao hiệu quả chẩn đoán và điều trị.</li>
                        <li>Xây dựng môi trường thân thiện, lấy bệnh nhân làm trung tâm.</li>
                    </ul>
                    <p style="text-indent: 2em;">Tầm nhìn của chúng tôi là trở thành bệnh viện hàng đầu khu vực, được tin cậy bởi cộng đồng và các đối tác y tế.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section id="history" class="fade-in">
        <div class="container">
            <h2 style="text-align: center;">Lịch sử hình thành</h2>
            <div class="row">
                <div class="col-md-12">
                    <p style="text-indent: 2em;">Bệnh viện Four Rock được thành lập vào năm 2010, bắt đầu từ một phòng khám nhỏ với mong muốn cải thiện chất lượng chăm sóc sức khỏe tại địa phương. Qua hơn một thập kỷ phát triển, chúng tôi đã:</p>
                    <ul style="list-style-type: none; padding-left: 2em;">
                        <li>Mở rộng quy mô với hơn 200 giường bệnh và các khoa chuyên môn.</li>
                        <li>Đầu tư vào trang thiết bị hiện đại như máy MRI, CT, và phòng xét nghiệm tiên tiến.</li>
                        <li>Hợp tác với các tổ chức y tế quốc tế để nâng cao chuyên môn.</li>
                    </ul>
                    <p style="text-indent: 2em;">Hôm nay, Four Rock tự hào là một trong những bệnh viện đa khoa uy tín, phục vụ hàng ngàn bệnh nhân mỗi năm.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
    <section id="facilities" class="fade-in">
        <div class="container">
            <h2 class="text-center">Cơ sở vật chất</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-hospital fa-4x mb-3"></i>
                            <h4 class="card-title">Phòng khám hiện đại</h4>
                            <p class="card-text">Trang bị các thiết bị chẩn đoán tiên tiến như siêu âm 4D và EKG.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-flask fa-4x mb-3"></i>
                            <h4 class="card-title">Phòng xét nghiệm</h4>
                            <p class="card-text">Ứng dụng công nghệ PCR và phân tích gen để chẩn đoán chính xác.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-ambulance fa-4x mb-3"></i>
                            <h4 class="card-title">Khoa cấp cứu 24/7</h4>
                            <p class="card-text">Luôn sẵn sàng tiếp nhận và xử lý các ca khẩn cấp với đội ngũ bác sĩ trực chuyên môn cao.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-md fa-4x mb-3"></i>
                            <h4 class="card-title">Phòng phẫu thuật vô trùng</h4>
                            <p class="card-text">Được trang bị đầy đủ hệ thống kiểm soát nhiễm khuẩn và thiết bị mổ hiện đại.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="fade-in">
        <div class="container">
            <h2>Đội ngũ y bác sĩ</h2>
            <div class="row">
                <?php if (empty($doctors)): ?>
                    <div class="col-12 text-center">
                        <p>Không có thông tin bác sĩ nào hiện tại.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <i class="fas fa-user-doctor fa-4x mb-3"></i>
                                    <h4 class="card-title"><?php echo htmlspecialchars($doctor['HoTen']); ?></h4>
                                    <p class="card-text">Bác sĩ chuyên khoa với nhiều năm kinh nghiệm.</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="cta" class="fade-in">
        <div class="container">
            <h2>Khám sức khỏe tại Four Rock</h2>
            <p>Đặt lịch ngay hôm nay để trải nghiệm dịch vụ y tế chất lượng cao.</p>
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
                        <li><a href="hospital-about.php" class="text-white-50">Giới thiệu</a></li>
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

        // Chat functionality


        // Form handling for appointment booking
        document.getElementById('doctor').addEventListener('change', function() {
            const doctorId = this.value;
            const dateSelect = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('appointment_time');

            dateSelect.disabled = true;
            timeSelect.disabled = true;
            dateSelect.innerHTML = '<option value="">-- Chọn ngày khám --</option>';
            timeSelect.innerHTML = '<option value="">-- Chọn giờ khám --</option>';

            if (doctorId) {
                dateSelect.disabled = false;
                dateSelect.innerHTML += '<option value="2025-05-23">23/05/2025</option>';
                dateSelect.innerHTML += '<option value="2025-05-24">24/05/2025</option>';
                dateSelect.innerHTML += '<option value="2025-05-25">25/05/2025</option>';
            }
        });

        document.getElementById('appointment_date').addEventListener('change', function() {
            const doctorId = document.getElementById('doctor').value;
            const date = this.value;
            const timeSelect = document.getElementById('appointment_time');

            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">-- Chọn giờ khám --</option>';

            if (doctorId && date) {
                timeSelect.disabled = false;
                timeSelect.innerHTML += '<option value="08:00">08:00</option>';
                timeSelect.innerHTML += '<option value="09:00">09:00</option>';
                timeSelect.innerHTML += '<option value="10:00">10:00</option>';
                timeSelect.innerHTML += '<option value="14:00">14:00</option>';
                timeSelect.innerHTML += '<option value="15:00">15:00</option>';
            }
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
```