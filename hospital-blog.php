<?php
session_start();
// Lấy thông tin user nếu đã đăng nhập (không bắt buộc)
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// KHÔNG CẦN KIỂM TRA ĐĂNG NHẬP - Mọi người đều có thể xem blog

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
    <title>Four Rock - Blog Y Tế</title>

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

        /* Blog Header Section */
        .blog-header {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--light-gray), #ffffff);
            text-align: center;
            margin-top: 20px;
        }

        .blog-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .blog-header p {
            font-size: 1.2rem;
            color: var(--dark-gray);
        }

        /* Blog Posts Section */
        #blog-posts {
            padding: 5rem 0;
            background: var(--light-gray);
        }

        #blog-posts h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
        }

        #blog-posts h2::after {
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

        #blog-posts .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        #blog-posts .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        #blog-posts .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        #blog-posts .card:hover .card-img-top {
            transform: scale(1.05);
        }

        #blog-posts .card-body {
            padding: 1.5rem;
        }

        #blog-posts .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
            line-height: 1.4;
        }

        /* Article Modal Styles */
        .modal-lg {
            max-width: 900px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .modal-header .close {
            color: white;
            opacity: 0.8;
            text-shadow: none;
        }

        .modal-header .close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 2rem;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .article-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .article-date {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .article-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-dark);
        }

        .article-content h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 2rem 0 1rem 0;
        }

        .article-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
        }

        .article-content ul {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .article-content li {
            margin-bottom: 0.5rem;
        }

        .article-tags {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-gray);
        }

        .article-tags .badge {
            margin-right: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .share-buttons {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .share-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .share-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        .share-facebook { background: #1877f2; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-copy { background: var(--dark-gray); }

        /* CTA Section */
        #cta {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        #cta h3 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        #cta p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        #cta .form-control {
            max-width: 300px;
            margin: 0 auto;
            border-radius: 8px;
            border: none;
        }

        #cta .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 2rem;
        }

        /* Contact Section */
        #contact {
            padding: 5rem 0;
            background: white;
        }

        #contact h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            text-align: center;
        }

        .contact-info {
            padding: 2rem;
            border-radius: 12px;
            background: var(--light-gray);
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
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

        /* Footer */
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
            .registration-overlay {
                position: static;
                margin: 2rem auto;
                max-width: 100%;
                padding: 0 1rem;
            }

            #blog-posts h2,
            #contact h2,
            #cta h3 {
                font-size: 2rem;
            }


            .modal-lg {
                max-width: 95%;
                margin: 1rem auto;
            }

            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .share-buttons {
                justify-content: center;
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

    <!-- Blog Header Section -->
    <section class="blog-header fade-in">
        <div class="container">
            <h1>Blog Y Tế</h1>
            <p>Cập nhật tin tức, kiến thức & chia sẻ kinh nghiệm sức khỏe</p>
        </div>
    </section>

    <!-- Blog Posts Section -->
    <section id="blog-posts" class="fade-in">
        <div class="container">
            <h2>Tin tức & Bài viết</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="public/images/besthospital.png" class="card-img-top" alt="Bài viết 1">
                        <div class="card-body">
                            <span class="badge badge-primary mb-2">Tin tức</span>
                            <h4 class="card-title">Các bệnh viện hàng đầu năm 2025</h4>
                            <p class="card-text">Khám phá danh sách các bệnh viện uy tín với tiêu chuẩn quốc tế đang dẫn đầu xu hướng.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>15/05/2024
                                </small>
                                <button class="btn btn-primary btn-sm" onclick="showArticle(1)">
                                    <i class="fas fa-book-open mr-1"></i>Đọc tiếp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="public/images/techhosptal.png" class="card-img-top" alt="Bài viết 2">
                        <div class="card-body">
                            <span class="badge badge-success mb-2">Công nghệ</span>
                            <h4 class="card-title">Xu hướng công nghệ y tế 2025</h4>
                            <p class="card-text">Các đột phá công nghệ thay đổi cách chăm sóc sức khỏe và cải thiện chất lượng dịch vụ.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>10/05/2024
                                </small>
                                <button class="btn btn-primary btn-sm" onclick="showArticle(2)">
                                    <i class="fas fa-book-open mr-1"></i>Đọc tiếp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="public/images/househospital.png" class="card-img-top" alt="Bài viết 3">
                        <div class="card-body">
                            <span class="badge badge-warning mb-2">Sức khỏe</span>
                            <h4 class="card-title">Chăm sóc sức khỏe tại nhà</h4>
                            <p class="card-text">Những mẹo và chiến lược để giữ gìn sức khỏe ngay tại nhà qua những biện pháp hiệu quả.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>05/05/2024
                                </small>
                                <button class="btn btn-primary btn-sm" onclick="showArticle(3)">
                                    <i class="fas fa-book-open mr-1"></i>Đọc tiếp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Article Modal -->
    <div class="modal fade" id="articleModal" tabindex="-1" role="dialog" aria-labelledby="articleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="articleModalLabel">Tiêu đề bài viết</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="article-meta">
                        <div class="article-author">
                            <i class="fas fa-user-md text-primary"></i>
                            <span id="articleAuthor">BS. Nguyễn Văn A</span>
                        </div>
                        <div class="article-date">
                            <i class="fas fa-calendar text-muted mr-1"></i>
                            <span id="articleDate">15/05/2024</span>
                        </div>
                    </div>
                    
                    <img id="articleImage" src="" alt="Article Image" class="article-image">
                    
                    <div id="articleContent" class="article-content">
                        <!-- Nội dung bài viết sẽ được load vào đây -->
                    </div>
                    
                    <div class="article-tags">
                        <strong>Tags: </strong>
                        <span id="articleTags">
                            <!-- Tags sẽ được load vào đây -->
                        </span>
                    </div>
                    
                    <div class="share-buttons">
                        <strong>Chia sẻ: </strong>
                        <a href="#" class="share-btn share-facebook" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f mr-1"></i>Facebook
                        </a>
                        <a href="#" class="share-btn share-twitter" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter mr-1"></i>Twitter
                        </a>
                        <a href="#" class="share-btn share-linkedin" onclick="shareOnLinkedIn()">
                            <i class="fab fa-linkedin-in mr-1"></i>LinkedIn
                        </a>
                        <button class="share-btn share-copy" onclick="copyArticleLink()">
                            <i class="fas fa-link mr-1"></i>Sao chép link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section id="cta" class="fade-in">
        <div class="container">
            <h3>Đăng ký nhận tin tức</h3>
            <p>Nhận cập nhật mới nhất từ chuyên gia và các tin tức y tế hàng đầu.</p>
            <form class="form-inline justify-content-center">
                <input type="email" class="form-control mb-2 mr-sm-2" placeholder="Nhập email của bạn" required>
                <button type="submit" class="btn btn-light mb-2">Đăng ký</button>
            </form>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="fade-in">
        <div class="container">
            <h2>Liên hệ với chúng tôi</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="contact-info">
                        <i class="fas fa-phone-alt fa-2x"></i>
                        <p>Hotline: 1900 1234</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="contact-info">
                        <i class="fas fa-envelope fa-2x"></i>
                        <p>Email: info@fourrockhospital.com</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="contact-info">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                        <p>Địa chỉ: 123 Đường Sức Khỏe, TP. HCM</p>
                    </div>
                </div>
            </div>
            <div class="map-responsive mt-4">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.6696842978494!2d106.68006931474894!3d10.759922092332988!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f1b7c3ed289%3A0xa06651894598e488!2sHo%20Chi%20Minh%20City!5e0!3m2!1sen!2s!4v1601234567890!5m2!1sen!2s" width="100%" height="400" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
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
                        <li><a href="#blog-posts" class="text-white-50">Blog</a></li>
                        <li><a href="#contact" class="text-white-50">Liên hệ</a></li>
                        <li><a href="index.php#services" class="text-white-50">Dịch vụ</a></li>
                        <li><a href="index.php#about" class="text-white-50">Giới thiệu</a></li>
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Dữ liệu bài viết (trong thực tế sẽ lấy từ database)
        const articles = {
            1: {
                title: "Các bệnh viện hàng đầu năm 2025",
                author: "BS. Nguyễn Minh Hoàng",
                date: "15/05/2024",
                category: "Tin tức",
                image: "public/images/besthospital.png",
                tags: ["Bệnh viện", "Y tế", "Tin tức", "2025"],
                content: `
                    <p>Năm 2025 đánh dấu một bước ngoặt quan trọng trong lĩnh vực chăm sóc sức khỏe tại Việt Nam. Với sự đầu tư mạnh mẽ vào công nghệ y tế hiện đại và nâng cao chất lượng dịch vụ, nhiều bệnh viện đã vươn lên dẫn đầu về tiêu chuẩn quốc tế.</p>
                    
                    <h3>Tiêu chí đánh giá bệnh viện hàng đầu</h3>
                    <p>Để được xếp hạng trong danh sách các bệnh viện hàng đầu, các cơ sở y tế cần đáp ứng những tiêu chí nghiêm ngặt:</p>
                    <ul>
                        <li>Trang thiết bị y tế hiện đại, đồng bộ</li>
                        <li>Đội ngũ bác sĩ giỏi, có trình độ chuyên môn cao</li>
                        <li>Quy trình điều trị chuẩn quốc tế</li>
                        <li>Tỷ lệ thành công cao trong các ca phẫu thuật phức tạp</li>
                        <li>Dịch vụ chăm sóc khách hàng xuất sắc</li>
                        <li>Môi trường điều trị an toàn, thân thiện</li>
                    </ul>
                    
                    <h3>Xu hướng phát triển của ngành y tế</h3>
                    <p>Các bệnh viện hàng đầu hiện nay đều hướng tới việc ứng dụng công nghệ 4.0 vào quy trình điều trị. Từ việc sử dụng trí tuệ nhân tạo trong chẩn đoán hình ảnh, robot phẫu thuật cho các ca bệnh phức tạp, đến hệ thống quản lý bệnh án điện tử toàn diện.</p>
                    
                    <p>Đặc biệt, xu hướng y học cá nhân hóa đang được nhiều bệnh viện áp dụng, giúp tối ưu hóa phương án điều trị cho từng bệnh nhân dựa trên đặc điểm sinh học và di truyền cá thể.</p>
                    
                    <h3>Lời khuyên cho bệnh nhân</h3>
                    <p>Khi lựa chọn bệnh viện để điều trị, bệnh nhân nên tham khảo các yếu tố như chuyên khoa mạnh của bệnh viện, kinh nghiệm của đội ngũ y bác sĩ, cũng như các đánh giá từ bệnh nhân trước đó. Việc tìm hiểu kỹ thông tin sẽ giúp bạn đưa ra quyết định đúng đắn cho sức khỏe của mình.</p>
                `
            },
            2: {
                title: "Xu hướng công nghệ y tế 2025",
                author: "TS. Phạm Thị Lan",
                date: "10/05/2024",
                category: "Công nghệ",
                image: "public/images/techhosptal.png",
                tags: ["Công nghệ", "Y tế", "AI", "Robots"],
                content: `
                    <p>Năm 2025 chứng kiến sự bùng nổ của nhiều công nghệ đột phá trong lĩnh vực y tế. Từ trí tuệ nhân tạo đến công nghệ nano, các ứng dụng này đang thay đổi căn bản cách thức chẩn đoán và điều trị bệnh.</p>
                    
                    <h3>Trí tuệ nhân tạo trong chẩn đoán</h3>
                    <p>AI đã trở thành công cụ đắc lực của các bác sĩ trong việc phân tích hình ảnh y khoa. Các hệ thống AI có thể:</p>
                    <ul>
                        <li>Phát hiện ung thư sớm qua hình ảnh X-quang, CT, MRI</li>
                        <li>Dự đoán nguy cơ mắc bệnh dựa trên dữ liệu sức khỏe</li>
                        <li>Hỗ trợ chẩn đoán các bệnh hiếm gặp</li>
                        <li>Tối ưu hóa phác đồ điều trị cá nhân hóa</li>
                    </ul>
                    
                    <h3>Robot phẫu thuật thế hệ mới</h3>
                    <p>Các robot phẫu thuật hiện đại mang lại độ chính xác cao hơn, giảm thiểu chấn thương và thời gian hồi phục cho bệnh nhân. Công nghệ này đặc biệt hữu ích trong các ca phẫu thuật tim mạch, thần kinh và ung thư.</p>
                    
                    <h3>Y học từ xa (Telemedicine)</h3>
                    <p>Dịch vụ y tế từ xa đã phát triển mạnh mẽ, cho phép bệnh nhân tương tác trực tiếp với bác sĩ qua video call, nhận tư vấn và theo dõi sức khỏe mà không cần đến bệnh viện. Điều này đặc biệt có ý nghĩa với những bệnh nhân ở vùng sâu, vùng xa.</p>
                    
                    <h3>Công nghệ wearable và IoT</h3>
                    <p>Các thiết bị đeo thông minh như đồng hồ, vòng tay theo dõi sức khỏe đã trở nên phổ biến. Chúng có thể giám sát liên tục các chỉ số sinh hiệu, cảnh báo sớm các vấn đề sức khỏe và gửi dữ liệu trực tiếp cho bác sĩ điều trị.</p>
                `
            },
            3: {
                title: "Chăm sóc sức khỏe tại nhà",
                author: "BS. Lê Văn Tùng",
                date: "05/05/2024",
                category: "Sức khỏe",
                image: "public/images/househospital.png",
                tags: ["Sức khỏe", "Chăm sóc", "Tại nhà", "Phòng ngừa"],
                content: `
                    <p>Chăm sóc sức khỏe tại nhà không chỉ là xu hướng mà còn là nhu cầu thiết yếu trong cuộc sống hiện đại. Việc duy trì sức khỏe tốt ngay tại nhà giúp tiết kiệm thời gian, chi phí và giảm nguy cơ lây nhiễm bệnh tật.</p>
                    
                    <h3>Nguyên tắc cơ bản</h3>
                    <p>Để chăm sóc sức khỏe hiệu quả tại nhà, bạn cần tuân thủ những nguyên tắc sau:</p>
                    <ul>
                        <li>Duy trì chế độ ăn uống cân bằng, đầy đủ dinh dưỡng</li>
                        <li>Tập thể dục đều đặn ít nhất 30 phút mỗi ngày</li>
                        <li>Đảm bảo giấc ngủ chất lượng 7-8 tiếng mỗi đêm</li>
                        <li>Quản lý stress thông qua thiền định, yoga hoặc các hoạt động thư giãn</li>
                        <li>Kiểm tra sức khỏe định kỳ và theo dõi các chỉ số quan trọng</li>
                    </ul>
                    
                    <h3>Dinh dưỡng và chế độ ăn</h3>
                    <p>Một chế độ ăn uống lành mạnh là nền tảng của sức khỏe tốt. Hãy ưu tiên:</p>
                    <ul>
                        <li>Tăng cường rau xanh, trái cây tươi</li>
                        <li>Chọn protein nạc từ cá, gà, đậu hạt</li>
                        <li>Sử dụng ngũ cốc nguyên hạt thay vì tinh chế</li>
                        <li>Hạn chế đường, muối và thực phẩm chế biến sẵn</li>
                        <li>Uống đủ 2-3 lít nước mỗi ngày</li>
                    </ul>
                    
                    <h3>Vận động và thể dục</h3>
                    <p>Không cần thiết bị phức tạp, bạn có thể duy trì sức khỏe với các bài tập đơn giản tại nhà như yoga, pilates, aerobic nhẹ, hoặc thậm chí là làm việc nhà. Điều quan trọng là duy trì sự đều đặn.</p>
                    
                    <h3>Sức khỏe tinh thần</h3>
                    <p>Chăm sóc sức khỏe tinh thần cũng quan trọng không kém. Hãy dành thời gian cho các hoạt động yêu thích, kết nối với gia đình bạn bè, và học cách quản lý căng thẳng một cách tích cực.</p>
                    
                    <p>Nhớ rằng, chăm sóc sức khỏe tại nhà không thể thay thế hoàn toàn việc khám bác sĩ định kỳ. Hãy luôn tham khảo ý kiến chuyên gia khi cần thiết.</p>
                `
            }
        };

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
        

        // Hiển thị bài viết trong modal
        function showArticle(articleId) {
            const article = articles[articleId];
            if (!article) return;

            document.getElementById('articleModalLabel').textContent = article.title;
            document.getElementById('articleAuthor').textContent = article.author;
            document.getElementById('articleDate').textContent = article.date;
            document.getElementById('articleImage').src = article.image;
            document.getElementById('articleContent').innerHTML = article.content;
            
            const tagsHtml = article.tags.map(tag => 
                `<span class="badge badge-secondary">${tag}</span>`
            ).join(' ');
            document.getElementById('articleTags').innerHTML = tagsHtml;

            $('#articleModal').modal('show');
        }

        // Chức năng chia sẻ
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.getElementById('articleModalLabel').textContent);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&t=${title}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.getElementById('articleModalLabel').textContent);
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank');
        }

        function shareOnLinkedIn() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.getElementById('articleModalLabel').textContent);
            window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`, '_blank');
        }

        function copyArticleLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Đã sao chép liên kết bài viết!');
            });
        }

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