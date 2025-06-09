<?php
// Định nghĩa BASE_URL
define('BASE_URL', '/booking/admin/');

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include các controller
require_once "app/controllers/DashboardController.php";
require_once "app/controllers/PatientController.php";
require_once "app/controllers/DoctorController.php";
require_once "app/controllers/ScheduleController.php";
require_once __DIR__ . '/app/controllers/LoginController.php';

// Lấy controller và action từ URL
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Xử lý login
if ($controller === 'login') {
    $loginController = new LoginController();
    if ($action === 'index') {
        $loginController->index();
    } elseif ($action === 'authenticate') {
        $loginController->authenticate();
    } elseif ($action === 'logout') {
        $loginController->logout();
    }
    exit();
}

// Kiểm tra đăng nhập cho các trang admin
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['VaiTro'] !== 'Quản trị') {
    header("Location: " . BASE_URL . "web.php?controller=login");
    exit();
}

// Xử lý các controller khác
switch ($controller) {
    case 'dashboard':
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;

    case 'benhnhan':
        $benhNhanController = new PatientController();
        if ($action === 'index') {
            $benhNhanController->index();
        } elseif ($action === 'update') {
            $benhNhanController->update();
        } elseif ($action === 'destroy') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $benhNhanController->destroy();
            } else {
                echo "Lỗi: Phương thức HTTP không hợp lệ!";
            }
        } else {
            echo "Lỗi: Hành động không hợp lệ!";
        }
        break;
        
    case 'bacsi':
        $bacSiController = new BacSiController();
        if ($action === 'index') {
            $bacSiController->index();
        } elseif ($action === 'store') {
            $bacSiController->store();
        } elseif ($action === 'update') {
            $bacSiController->update();
        } elseif ($action === 'destroy') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $bacSiController->destroy();
            } else {
                echo "Lỗi: Phương thức HTTP không hợp lệ!";
            }
        } else {
            echo "Lỗi: Hành động không hợp lệ!";
        }
        break;

    case 'lichkham':
        $lichKhamController = new LichLamViecController();
        if ($action === 'index') {
            $lichKhamController->index();
        } elseif ($action === 'store') {
            $lichKhamController->store();
        } elseif ($action === 'update') {
            $lichKhamController->update();
        } elseif ($action === 'destroy') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $lichKhamController->destroy();
            } else {
                echo "Lỗi: Phương thức HTTP không hợp lệ!";
            }
        } elseif ($action === 'updateStatus') {
            $lichKhamController->updateStatus();
        } else {
            echo "Lỗi: Hành động không hợp lệ!";
        }
        break;

    default:
        echo "Lỗi: Trang không tồn tại!";
}
