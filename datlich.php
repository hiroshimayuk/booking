```php
<?php
session_start();
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Kết nối CSDL
require_once __DIR__ . '/app/config/database.php';
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// DEBUG: Kiểm tra thông tin session
echo "<!-- DEBUG SESSION: " . print_r($_SESSION, true) . " -->";

// Lấy thông tin bác sĩ từ URL
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$doctor_name = isset($_GET['doctor_name']) ? urldecode($_GET['doctor_name']) : '';
$doctor_info = null;
$doctor_valid = false;

if ($doctor_id > 0) {
    $sql = "SELECT bs.MaBacSi, bs.HoTen, bs.MoTa, bs.HinhAnhBacSi, k.TenKhoa 
            FROM BacSi bs 
            LEFT JOIN Khoa k ON bs.MaKhoa = k.MaKhoa 
            WHERE bs.MaBacSi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $doctor_valid = true;
        $doctor_info = $result->fetch_assoc();
        $doctor_name = $doctor_info['HoTen'];
    }
    $stmt->close();
}

// Kiểm tra xem user có phải là bệnh nhân không
if ($loggedInUser && !isset($_SESSION['user']['MaBenhNhan'])) {
    $user_id = $_SESSION['user']['MaNguoiDung'] ?? 0;
    if ($user_id > 0) {
        $sql_patient = "SELECT * FROM BenhNhan WHERE MaNguoiDung = ?";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param("i", $user_id);
        $stmt_patient->execute();
        $result_patient = $stmt_patient->get_result();

        if ($result_patient->num_rows > 0) {
            $patient_data = $result_patient->fetch_assoc();
            $_SESSION['user'] = array_merge($_SESSION['user'], $patient_data);
        } else {
            $message = '<div class="alert alert-warning">
                Vui lòng cập nhật thông tin cá nhân trước khi đặt lịch hẹn.
                <a href="/profile.php" class="btn btn-sm btn-primary ml-2">Cập nhật thông tin</a>
            </div>';
        }
        $stmt_patient->close();
    }
}

// Thêm debug cho lịch làm việc của bác sĩ
if ($doctor_valid) {
    echo "<!-- DEBUG: Doctor ID = $doctor_id -->";

    $sql_debug = "SELECT * FROM LichLamViec WHERE MaBacSi = ? AND NgayLamViec >= CURDATE()";
    $stmt_debug = $conn->prepare($sql_debug);
    $stmt_debug->bind_param("i", $doctor_id);
    $stmt_debug->execute();
    $result_debug = $stmt_debug->get_result();

    echo "<!-- DEBUG: Available schedules = " . $result_debug->num_rows . " -->";

    while ($row = $result_debug->fetch_assoc()) {
        echo "<!-- Schedule: " . print_r($row, true) . " -->";
    }
    $stmt_debug->close();
}

// Xử lý form khi submit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['get_times'])) {
    $patient_name = $_POST['patient_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $symptoms = $_POST['symptoms'] ?? '';

    // Sửa cách lấy MaBenhNhan
    $ma_benh_nhan = 0;
    if (isset($_SESSION['user']['MaBenhNhan'])) {
        $ma_benh_nhan = $_SESSION['user']['MaBenhNhan'];
    } else {
        $user_id = $_SESSION['user']['MaNguoiDung'] ?? 0;
        if ($user_id > 0) {
            $sql_find = "SELECT MaBenhNhan FROM BenhNhan WHERE MaNguoiDung = ?";
            $stmt_find = $conn->prepare($sql_find);
            $stmt_find->bind_param("i", $user_id);
            $stmt_find->execute();
            $result_find = $stmt_find->get_result();
            if ($result_find->num_rows > 0) {
                $patient = $result_find->fetch_assoc();
                $ma_benh_nhan = $patient['MaBenhNhan'];
            }
            $stmt_find->close();
        }
    }

    echo "<!-- DEBUG: MaBenhNhan = $ma_benh_nhan -->";

    if (!$ma_benh_nhan) {
        $message = '<div class="alert alert-danger">
            Vui lòng đăng nhập để đặt lịch hẹn.
        </div>';
    } elseif ($doctor_valid && $patient_name && $phone && $email && $appointment_date && $appointment_time) {
        $appointment_datetime = $appointment_date . ' ' . $appointment_time;

        $sql = "SELECT MaLich FROM LichLamViec 
                WHERE MaBacSi = ? AND NgayLamViec = ? 
                AND TIME(?) BETWEEN GioBatDau AND GioKetThuc 
                AND TrangThai = 'Đã xác nhận'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sql_check = "SELECT MaLich FROM LichHen 
                         WHERE MaBacSi = ? AND NgayGio = ? AND TrangThai != 'Đã hủy'";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("is", $doctor_id, $appointment_datetime);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows == 0) {
                $sql_insert = "INSERT INTO LichHen (MaBenhNhan, MaBacSi, NgayGio, TrieuChung, TrangThai) 
                              VALUES (?, ?, ?, ?, 'Chờ xác nhận')";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iiss", $ma_benh_nhan, $doctor_id, $appointment_datetime, $symptoms);

                if ($stmt_insert->execute()) {
                    $message = '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        Đặt lịch hẹn thành công! Vui lòng chờ xác nhận từ bác sĩ.
                    </div>';
                    $_POST = array();
                } else {
                    $message = '<div class="alert alert-danger">
                        Lỗi khi đặt lịch hẹn: ' . htmlspecialchars($conn->error) . '
                    </div>';
                    echo "<!-- SQL Error: " . $conn->error . " -->";
                }
                $stmt_insert->close();
            } else {
                $message = '<div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Thời gian này đã có lịch hẹn. Vui lòng chọn thời gian khác.
                </div>';
            }
            $stmt_check->close();
        } else {
            $message = '<div class="alert alert-warning">
                <i class="fas fa-calendar-times"></i>
                Bác sĩ không có lịch làm việc vào thời gian này. Vui lòng chọn thời gian khác.
            </div>';
        }
        $stmt->close();
    } else {
        $missing_fields = [];
        if (!$patient_name) $missing_fields[] = "Họ tên";
        if (!$phone) $missing_fields[] = "Số điện thoại";
        if (!$email) $missing_fields[] = "Email";
        if (!$appointment_date) $missing_fields[] = "Ngày hẹn";
        if (!$appointment_time) $missing_fields[] = "Giờ hẹn";
        if (!$symptoms) $missing_fields[] = "Triệu chứng";

        $message = '<div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            Vui lòng điền đầy đủ thông tin: ' . implode(', ', $missing_fields) . '
        </div>';
    }
}

// Lấy danh sách ngày làm việc của bác sĩ
$available_dates = [];
if ($doctor_valid) {
    $sql = "SELECT DISTINCT NgayLamViec FROM LichLamViec WHERE MaBacSi = ? AND TrangThai = 'Đã xác nhận' AND NgayLamViec >= CURDATE() ORDER BY NgayLamViec";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $available_dates[] = $row['NgayLamViec'];
    }
    $stmt->close();
}

// Xử lý AJAX request để lấy giờ hẹn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_times'])) {
    $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';

    if ($doctor_id > 0 && $date) {
        $sql = "SELECT GioBatDau, GioKetThuc FROM LichLamViec 
                WHERE MaBacSi = ? AND NgayLamViec = ? AND TrangThai = 'Đã xác nhận'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $doctor_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sql_all_times = "SELECT DISTINCT GioBatDau, GioKetThuc FROM LichLamViec 
                             WHERE MaBacSi = ? AND NgayLamViec = ? AND TrangThai = 'Đã xác nhận'
                             ORDER BY GioBatDau";
            $stmt_all = $conn->prepare($sql_all_times);
            $stmt_all->bind_param("is", $doctor_id, $date);
            $stmt_all->execute();
            $result_all = $stmt_all->get_result();

            $sql_booked = "SELECT TIME(NgayGio) as booked_time FROM LichHen 
                          WHERE MaBacSi = ? AND DATE(NgayGio) = ? AND TrangThai != 'Đã hủy'";
            $stmt_booked = $conn->prepare($sql_booked);
            $stmt_booked->bind_param("is", $doctor_id, $date);
            $stmt_booked->execute();
            $result_booked = $stmt_booked->get_result();

            $booked_times = [];
            while ($row = $result_booked->fetch_assoc()) {
                $booked_times[] = $row['booked_time'];
            }

            $available_times = [];
            while ($schedule = $result_all->fetch_assoc()) {
                $start = strtotime($schedule['GioBatDau']);
                $end = strtotime($schedule['GioKetThuc']);

                for ($time = $start; $time < $end; $time += 1800) {
                    $time_slot = date('H:i:s', $time);
                    if (!in_array($time_slot, $booked_times)) {
                        $available_times[] = $time_slot;
                    }
                }
            }

            sort($available_times);

            echo '<option value="">Chọn giờ</option>';
            foreach ($available_times as $time) {
                echo '<option value="' . htmlspecialchars($time) . '">' .
                    htmlspecialchars(date('H:i', strtotime($time))) . '</option>';
            }

            $stmt_all->close();
            $stmt_booked->close();
        } else {
            echo '<option value="">Bác sĩ không có lịch làm việc trong ngày này</option>';
        }
        $stmt->close();
    } else {
        echo '<option value="">Dữ liệu không hợp lệ</option>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch hẹn - Four Rock</title>
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

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--light-gray);
        }

        .container {
            margin: 2rem auto;
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
        }

        .doctor-info-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .doctor-info-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .doctor-info-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .doctor-info-card .specialty {
            font-size: 1rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .doctor-info-card .description {
            font-size: 0.9rem;
            color: var(--dark-gray);
            line-height: 1.6;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
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

        .form-control {
            font-size: 0.85rem;
            padding: 0.48rem;
            border-radius: 8px;
            border: 2px solid var(--medium-gray);
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
        }

        .form-group label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }

        .alert {
            border-radius: 8px;
        }

        .loading {
            color: #666;
            font-style: italic;
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-control.is-invalid+.invalid-feedback {
            display: block;
        }

        @media (max-width: 768px) {
            .doctor-info-card {
                margin-bottom: 1.5rem;
            }

            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/view/header.php'; ?>
    <div class="container">
        <h2><i class="fas fa-calendar-check mr-2"></i>Đặt lịch hẹn</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!$doctor_valid): ?>
            <div class="alert alert-danger">Bác sĩ không hợp lệ. Vui lòng chọn lại.</div>
            <a href="../index.php" class="btn btn-primary"><i class="fas fa-arrow-left mr-2"></i>Quay lại</a>
        <?php else: ?>
            <div class="row">
                <!-- Doctor Info (Left Column) -->
                <div class="col-md-4">
                    <div class="doctor-info-card">
                        <?php
                        $imagePath = !empty($doctor_info['HinhAnhBacSi']) ? htmlspecialchars($doctor_info['HinhAnhBacSi']) : '/public/images/doctors/default.jpg';
                        if (strpos($imagePath, '/') !== 0) {
                            $imagePath = '/' . $imagePath;
                        }
                        ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($doctor_info['HoTen']); ?>" onerror="this.src='/public/images/doctors/fallback.jpg';">
                        <h3><?php echo htmlspecialchars($doctor_info['HoTen']); ?></h3>
                        <div class="specialty"><?php echo htmlspecialchars($doctor_info['TenKhoa'] ?: 'Chưa có chuyên khoa'); ?></div>
                        <div class="description">
                            <?php
                            // Sanitize MoTa to prevent XSS
                            $description = !empty($doctor_info['MoTa']) ? strip_tags($doctor_info['MoTa'], '<p><br>') : 'Không có mô tả.';
                            echo $description;
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Appointment Form (Right Column) -->
                <div class="col-md-8">
                    <div class="form-container">
                        <form method="POST" id="appointmentForm">
                            <div class="form-group">
                                <label for="doctor_name"><i class="fas fa-user-doctor mr-1"></i>Bác sĩ</label>
                                <input type="text" class="form-control" id="doctor_name" name="doctor_name" value="<?php echo htmlspecialchars($doctor_name); ?>" readonly>
                                <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id); ?>">
                            </div>


                            <div class="form-group">
                                <label for="patient_name"><i class="fas fa-user mr-1"></i>Họ và tên bệnh nhân</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" value="<?php echo htmlspecialchars($_SESSION['user']['HoTen'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Vui lòng cung cấp họ tên và tên.</div>
                            </div>


                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone mr-1"></i>Số điện thoại</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['SoDienThoai'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Vui lòng cung cấp số điện thoại hợp lệ (10-11 chữ số).</div>
                            </div>


                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope mr-1"></i>Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['Email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Vui lòng cung cấp email hợp lệ (ví dụ: example@example.com).</div>
                            </div>


                            <div class="form-group">
                                <label for="appointment_date"><i class="fas fa-calendar mr-1"></i>Ngày hẹn</label>
                                <select class="form-control" id="appointment_date" name="appointment_date" required>
                                    <option value="">Chọn ngày</option>
                                    <?php foreach ($available_dates as $date): ?>
                                        <option value="<?php echo htmlspecialchars($date); ?>">
                                            <?php echo htmlspecialchars(date('d/m/Y', strtotime($date))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Vui lòng chọn ngày hẹn.</div>
                            </div>

                            <div class="form-group">
                                <label for="appointment_time"><i class="fas fa-clock mr-1"></i>Giờ hẹn</label>
                                <select class="form-control" id="appointment_time" name="appointment_time" required disabled>
                                    <option value="">Chọn giờ</option>
                                </select>
                                <div class="invalid-feedback">Vui lòng chọn giờ hẹn.</div>
                            </div>

                            <div class="form-group">
                                <label for="symptoms"><i class="fas fa-notes-medical mr-1"></i>Triệu chứng</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="4" placeholder="Mô tả triệu chứng của bạn" required></textarea>
                                <div class="invalid-feedback">Vui lòng nhập triệu chứng.</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i>Đặt lịch
                            </button>

                            <a href="../index.php" class="btn btn-outline-primary btn-block mt-2">
                                <i class="fas fa-arrow-left mr-2"></i>Quay lại
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            const dateSelect = $('#appointment_date');
            const timeSelect = $('#appointment_time');
            const doctorId = <?php echo json_encode($doctor_id); ?>;
            const form = $('#appointmentForm');

            // Log initial data
            console.log('Doctor ID:', doctorId);
            console.log('Available dates:', <?php echo json_encode($available_dates); ?>);

            // Handle date change to load available times
            dateSelect.change(function() {
                const date = $(this).val();
                console.log('Selected date:', date);

                timeSelect.prop('disabled', true);
                timeSelect.html('<option value="" class="loading">Đang tải...</option>');

                if (doctorId && date) {
                    $.ajax({
                        url: window.location.href,
                        method: 'POST',
                        data: { // ĐÂY LÀ LỖI - BẠN ĐÃ VIẾT 'margin' THAY VÌ 'data'
                            get_times: true,
                            doctor_id: doctorId,
                            date: date
                        },
                        success: function(response) {
                            console.log('AJAX Response:', response);
                            timeSelect.html(response);
                            timeSelect.prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', {
                                xhr,
                                status,
                                error
                            });
                            timeSelect.html('<option value="">Lỗi khi tải giờ hẹn</option>');
                            timeSelect.prop('disabled', false);
                        }
                    });
                } else {
                    timeSelect.html('<option value="">Chọn giờ</option>');
                    timeSelect.prop('disabled', false);
                }
            });

            // Enhanced form validation
            form.submit(function(e) {
                let isValid = true;

                // Reset validation states
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').hide();

                // Validate each field
                const patientName = $('#patient_name').val().trim();
                const phone = $('#phone').val().trim();
                const email = $('#email').val().trim();
                const appointmentDate = $('#appointment_date').val();
                const appointmentTime = $('#appointment_time').val();
                const symptoms = $('#symptoms').val().trim();

                // Name validation
                if (!patientName) {
                    $('#patient_name').addClass('is-invalid');
                    $('#patient_name').next('.invalid-feedback').show();
                    isValid = false;
                }

                // Phone validation (10-11 digits)
                const phoneRegex = /^[0-9]{10,11}$/;
                if (!phone || !phoneRegex.test(phone)) {
                    $('#phone').addClass('is-invalid');
                    $('#phone').next('.invalid-feedback').show();
                    isValid = false;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRegex.test(email)) {
                    $('#email').addClass('is-invalid');
                    $('#email').next('.invalid-feedback').show();
                    isValid = false;
                }

                // Date and time validation
                if (!appointmentDate) {
                    $('#appointment_date').addClass('is-invalid');
                    $('#appointment_date').next('.invalid-feedback').show();
                    isValid = false;
                }
                if (!appointmentTime) {
                    $('#appointment_time').addClass('is-invalid');
                    $('#appointment_time').next('.invalid-feedback').show();
                    isValid = false;
                }

                // Symptoms validation
                if (!symptoms) {
                    $('#symptoms').addClass('is-invalid');
                    $('#symptoms').next('.invalid-feedback').show();
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng kiểm tra lại thông tin!');
                }
            });

            // Real-time validation on input
            $('#patient_name, #phone, #email, #symptoms').on('input', function() {
                const field = $(this);
                const value = field.val().trim();

                field.removeClass('is-invalid');
                field.next('.invalid-feedback').hide();

                if (field.attr('id') === 'phone' && value && !/^[0-9]{10,11}$/.test(value)) {
                    field.addClass('is-invalid');
                    field.next('.invalid-feedback').show();
                } else if (field.attr('id') === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    field.addClass('is-invalid');
                    field.next('.invalid-feedback').show();
                }
            });
        });
    </script>
</body>

</html>