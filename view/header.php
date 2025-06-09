<?php
// session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<style>
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
</style>
<body>
    <header>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
      <div class="container-fluid"> <!-- Changed from container to container-fluid -->
        <a class="navbar-brand" href="index.php">
          <i class="fas fa-hospital-alt mr-2"></i>Four Rock
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home mr-1"></i>Trang chủ</a></li>
            <li class="nav-item"><a class="nav-link" href="hospital-blog.php"><i class="fas fa-blog mr-1"></i>Blog</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="serviceDropdown" role="button" data-toggle="dropdown">
                <i class="fas fa-stethoscope mr-1"></i>Dịch vụ
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="general-checkup.php"><i class="fas fa-user-md mr-2"></i>Khám tổng quát</a>
                <a class="dropdown-item" href="cardiology.php"><i class="fas fa-heartbeat mr-2"></i>Tim mạch</a>
                <a class="dropdown-item" href="testing.php"><i class="fas fa-vial mr-2"></i>Xét nghiệm</a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button" data-toggle="dropdown">
                <i class="fas fa-info-circle mr-1"></i>Giới thiệu
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="hospital-about.php"><i class="fas fa-building mr-2"></i>Bệnh viện</a>
                <a class="dropdown-item" href="doctor.php"><i class="fas fa-user-doctor mr-2"></i>Bác sĩ</a>
              </div>
            </li>
            <!-- <li class="nav-item"><a class="nav-link" href=""><i class="fas fa-newspaper mr-1"></i>Tin tức</a></li> -->
            <li class="nav-item">
              <a class="nav-link" href="qa_doctor.php"><i class="fas fa-question-circle mr-1"></i>Hỏi đáp</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="contact.php"><i class="fas fa-phone mr-1"></i>Liên hệ</a></li>
            <!-- User Menu Placeholder -->
            <?php if ($loggedInUser): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  Xin chào, <?php echo htmlspecialchars($loggedInUser["TenDangNhap"]); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                  <a class="dropdown-item" href="view/change_password.php">Đổi mật khẩu</a>
                  <a class="dropdown-item" href="view/update_profile.php">Thay đổi thông tin cá nhân</a>
                  <a class="dropdown-item" href="view_appointments.php">Xem lịch</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="view/logout.php">Đăng xuất</a>
                </div>
              </li>
            <?php else: ?>
              <li class="nav-item"><a class="btn btn-primary mr-2" href="view/register.php">Đăng ký</a></li>
              <li class="nav-item"><a class="btn btn-outline-primary" href="view/login.php">Đăng nhập</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</body>
</html>