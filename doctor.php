<?php
session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Kết nối CSDL
require_once __DIR__ . '/app/config/database.php';
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy danh sách các khoa
$sql_khoa = "SELECT * FROM khoa ORDER BY TenKhoa";
$khoa_result = $conn->query($sql_khoa);
$khoa_list = [];
if ($khoa_result && $khoa_result->num_rows > 0) {
    while ($khoa = $khoa_result->fetch_assoc()) {
        $khoa_list[$khoa['MaKhoa']] = $khoa;
    }
}

// Lấy danh sách bác sĩ cho form đăng ký
$doctors = [];
$sql_doctors = "SELECT MaBacSi, HoTen FROM BacSi ORDER BY HoTen ASC";
$result_doctors = $conn->query($sql_doctors);
if ($result_doctors && $result_doctors->num_rows > 0) {
    while ($row = $result_doctors->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Lọc theo khoa nếu có
$filter_khoa = isset($_GET['khoa']) ? intval($_GET['khoa']) : 0;
$where_clause = $filter_khoa > 0 ? "WHERE b.MaKhoa = $filter_khoa" : "";

// Phân trang
$items_per_page = 8;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Lấy tổng số bác sĩ
$count_sql = "SELECT COUNT(*) as total FROM bacsi b $where_clause";
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Lấy danh sách bác sĩ với phân trang
$sql = "SELECT b.*, k.TenKhoa,
    COALESCE(ROUND(AVG(d.DiemDanhGia), 1), 0) as avg_rating,
    COUNT(d.MaDanhGia) as total_reviews
FROM bacsi b 
LEFT JOIN khoa k ON b.MaKhoa = k.MaKhoa
LEFT JOIN danhgia d ON b.MaBacSi = d.MaBacSi
WHERE 1=1 ";

if ($filter_khoa > 0) {
    $sql .= " AND b.MaKhoa = " . $filter_khoa;
}

$sql .= " AND (d.DiemDanhGia IS NULL OR (d.DiemDanhGia BETWEEN 1 AND 5))
GROUP BY b.MaBacSi, b.HoTen, k.TenKhoa
ORDER BY b.HoTen
LIMIT $offset, $items_per_page";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đội ngũ Bác sĩ - Four Rock</title>

    <!-- Google Fonts & Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../public/css/style.css">

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
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../public/img/banner.png') no-repeat center center;
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

        /* Filters Section */
        .filters {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 16px;
            margin: 2rem 0;
            box-shadow: var(--shadow);
        }

        .filters .form-select {
            border-radius: 8px;
            border: 2px solid var(--medium-gray);
            padding: 0.65rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filters .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Doctor Cards */
        .doctor-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .doctor-card img {
            height: 220px;
            object-fit: cover;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .doctor-card .card-body {
            padding: 1.5rem;
            text-align: center;
        }

        .doctor-card .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .specialty-badge {
            background: var(--success-color);
            color: white;
            font-size: 0.8rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }

        .rating .text-muted {
            font-size: 0.85rem;
        }

        /* Pagination */
        .pagination .page-link {
            border-radius: 8px;
            margin: 0 0.2rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .pagination .page-link:hover {
            background: var(--light-gray);
            color: var(--secondary-color);
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
            0% { left: -100%; }
            100% { left: 100%; }
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
    </style>
</head>

<body>
    <!-- Header Navigation -->
    <?php include __DIR__ . '/view/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section fade-in">
        <div class="container">
            <h1>Đội ngũ Bác sĩ</h1>
            <p>Gặp gỡ đội ngũ bác sĩ chuyên nghiệp và tận tâm của Four Rock</p>
        </div>
    </section>

    <!-- Filters and Doctor List -->
    <section class="container fade-in" style="padding: 5rem 0;">
        <div class="filters">
            <form action="" method="get" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label for="khoa" class="form-label">Chọn khoa:</label>
                    <select class="form-select" id="khoa" name="khoa" onchange="this.form.submit()">
                        <option value="0">Tất cả các khoa</option>
                        <?php foreach ($khoa_list as $khoa): ?>
                            <option value="<?php echo $khoa['MaKhoa']; ?>" <?php echo ($filter_khoa == $khoa['MaKhoa']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($khoa['TenKhoa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($doctor = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card doctor-card h-100">
                            <?php
                            if (!empty($doctor['HinhAnhBacSi'])) {
                                $imagePath = str_replace('/booking/', '/', $doctor['HinhAnhBacSi']);
                                $imagePath = '/' . ltrim($imagePath, '/');
                            ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                     alt="<?php echo htmlspecialchars($doctor['HoTen']); ?>"
                                     class="card-img-top"
                                     onerror="this.src='../public/uploads/default.png'">
                            <?php } else { ?>
                                <img src="../public/uploads/default.png"
                                     alt="Ảnh mặc định"
                                     class="card-img-top">
                            <?php } ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($doctor['HoTen']); ?></h5>
                                <p><span class="specialty-badge"><?php echo htmlspecialchars($doctor['TenKhoa']); ?></span></p>
                                <?php if (!empty($doctor['avg_rating'])): ?>
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
                                <?php else: ?>
                                    <div class="rating">
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <span class="text-muted">(Chưa có đánh giá)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-center">
                                <a href="chitietbacsi.php?MaBacSi=<?php echo $doctor['MaBacSi']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye mr-2"></i>Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Không tìm thấy bác sĩ nào</div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Phân trang" class="my-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $filter_khoa ? '&khoa=' . $filter_khoa : ''; ?>">
                                « Trang trước
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_khoa ? '&khoa=' . $filter_khoa : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $filter_khoa ? '&khoa=' . $filter_khoa : ''; ?>">
                                Trang sau »
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
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
                        <li><a href="../index.php#services" class="text-white-50">Dịch vụ</a></li>
                        <li><a href="../hospital-about.php" class="text-white-50">Giới thiệu</a></li>
                        <li><a href="../hospital-blog.php" class="text-white-50">Blog</a></li>
                        <li><a href="../index.php#contact" class="text-white-50">Liên hệ</a></li>
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

<?php $conn->close(); ?>