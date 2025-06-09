<?php
if (!isset($bacSis)) {
    $bacSis = [];
}
?>
<?php
require_once __DIR__ . '/../../../auth.php';
?>
<?php include __DIR__ . '/../../../header.php'; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bác Sĩ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/asset/css/style.css">

    <!-- jQuery (cần thiết cho Summernote) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Summernote CSS và JS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    
    <!-- Summernote language pack (Vietnamese) -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-vi-VN.min.js"></script>
</head>
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .breadcrumb {
        margin: 0;
        padding: 0.5rem 3rem;
        background-color: #ffffff;
        font-size: 1rem;
        border-radius: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .breadcrumb a {
        text-decoration: none;
        color: #4e73df;
    }

    .h1 {
        font-weight: bold;
        color: #333;
        float: left;
        padding-left: 10px;
    }

    .section {
        padding-top: 10px;
        border-radius: 5px;
        width: 100%;
        height: calc(100vh - 56px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
        overflow-y: auto;
        border-top: 2px solid #000000;
    }

    .d-flex {
        padding-top: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .h2 {
        font-weight: bold;
        color: #333;
    }

    .table-responsive {
        margin-top: 20px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .search-form {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }

    .doctor-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }

    /* Summernote customization */
    .note-editor {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .note-editor .note-toolbar {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .note-editor .note-editing-area {
        min-height: 200px;
    }

    /* Mobile responsiveness for Summernote */
    @media (max-width: 768px) {
        .note-editor .note-toolbar {
            padding: 5px;
        }
        
        .note-editor .note-toolbar .note-btn-group {
            margin-bottom: 5px;
        }
        
        .note-editor .note-editing-area {
            min-height: 150px;
        }
    }

    /* Table responsive styles */
    .table-responsive {
        margin-top: 20px;
        padding-left: 10px;
        padding-right: 10px;
        overflow-x: auto;
    }

    /* Description column styling */
    .description-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .description-cell:hover {
        white-space: normal;
        overflow: visible;
        position: relative;
        z-index: 10;
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        padding: 8px;
        border-radius: 4px;
    }

    /* Mobile specific styles */
    @media screen and (max-width: 768px) {
        .h1 {
            font-size: 1.5rem;
        }

        .d-flex {
            flex-direction: column;
            align-items: start;
        }

        .add-schedule-btn {
            margin-top: 10px;
            align-self: flex-end;
        }

        .table th,
        .table td {
            font-size: 0.85rem;
            padding: 0.5rem;
        }

        /* Optimize table for mobile */
        .mobile-hide {
            display: none;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .action-buttons button,
        .action-buttons form button {
            width: 100%;
        }

        /* Adjust search form */
        .search-form {
            width: 100%;
        }

        .search-form form {
            width: 100%;
        }

        #searchInput {
            width: 100%;
        }

        .description-cell {
            max-width: 100px;
        }
    }

    /* Modal responsive styles */
    @media screen and (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-body .form-control {
            font-size: 0.9rem;
        }
    }
</style>

<body>
    <div id="content" class="container-fluid">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bác sĩ</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fa-solid fa-arrow-right"></i> Bác sĩ</h1>
            <button class="btn btn-primary add-schedule-btn" onclick="openAddModal()">Thêm</button>
        </div>
        <div class="search-form">
            <form method="GET" action="<?= $_SERVER['SCRIPT_NAME'] ?>">
                <input type="hidden" name="controller" value="bacsi">
                <input type="hidden" name="action" value="index">
                <input type="text" name="keyword" id="searchInput" class="form-control" placeholder="Nhập tên bác sĩ..." value="<?= isset($keyword) ? htmlspecialchars($keyword) : '' ?>">
            </form>
        </div>
        <!-- Title -->
        <div class="container mt-4">

            <section class="section mt-4">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Thêm bác sĩ thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Cập nhật bác sĩ thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Xóa bác sĩ thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Mã Bác Sĩ</th>
                                <th>Hình ảnh</th>
                                <th>Họ tên</th>
                                <th>Khoa</th>
                                <th>SĐT</th>
                                <th>Email</th>
                                <th>Mô tả</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bacSis)): ?>
                                <?php foreach ($bacSis as $bacSi): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($bacSi['MaBacSi']); ?></td>
                                        <td>
                                            <?php
                                            if (!empty($bacSi['HinhAnhBacSi'])) {
                                                // Loại bỏ ProjectBooking khỏi đường dẫn nếu có
                                                $imagePath = str_replace('/booking/', '/', $bacSi['HinhAnhBacSi']);
                                                $imagePath = '/' . ltrim($imagePath, '/');
                                            ?>
                                                <img src="<?= htmlspecialchars($imagePath) ?>"
                                                    alt="Ảnh bác sĩ"
                                                    class="doctor-img"
                                                    onerror="this.src='/public/uploads/default.png'"
                                                    style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php } else { ?>
                                                <img src="/public/uploads/default.png"
                                                    alt="Ảnh mặc định"
                                                    class="doctor-img"
                                                    style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php } ?>
                                        </td>
                                        <td><?= htmlspecialchars($bacSi['HoTen']); ?></td>
                                        <td><?= htmlspecialchars($bacSi['TenKhoa']); ?></td>
                                        <td><?= htmlspecialchars($bacSi['SoDienThoai']); ?></td>
                                        <td><?= htmlspecialchars($bacSi['Email']); ?></td>
                                        <td class="description-cell" title="<?= htmlspecialchars(strip_tags($bacSi['MoTa'] ?? '')); ?>">
                                            <?= htmlspecialchars(substr(strip_tags($bacSi['MoTa'] ?? ''), 0, 50)); ?>
                                            <?= strlen(strip_tags($bacSi['MoTa'] ?? '')) > 50 ? '...' : ''; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <button class="btn btn-warning btn-sm" onclick="editDoctor(<?= htmlspecialchars(json_encode($bacSi)); ?>)">Sửa</button>
                                            <form method="POST" action="<?= BASE_URL ?>/bacsi/destroy" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bác sĩ này không?');" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $bacSi['MaBacSi']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Không có dữ liệu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
        </div>
        </section>
    </div>

    <!-- Modal Thêm Bác Sĩ -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Thêm Bác Sĩ Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDoctorForm" method="POST" action="<?= BASE_URL ?>/bacsi/store" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="addHoTen" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" id="addHoTen" name="HoTen" required>
                        </div>

                        <div class="mb-3">
                            <label for="addMaKhoa" class="form-label">Khoa</label>
                            <select class="form-control" id="addMaKhoa" name="MaKhoa" required>
                                <option value="">-- Chọn Khoa --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['MaKhoa']; ?>"><?= $dept['TenKhoa']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="addSoDienThoai" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="addSoDienThoai" name="SoDienThoai" required>
                        </div>

                        <div class="mb-3">
                            <label for="addEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="addEmail" name="Email" required>
                        </div>

                        <!-- Mô tả với Summernote -->
                        <div class="mb-3">
                            <label for="addMoTa" class="form-label">Mô tả</label>
                            <textarea class="form-control summernote" id="addMoTa" name="MoTa" placeholder="Nhập mô tả về bác sĩ..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="addHinhAnh" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="addHinhAnh" name="HinhAnh" accept="image/*" onchange="previewImage(this, 'addImagePreview')">
                            <div class="mt-2">
                                <img id="addImagePreview" alt="Hình ảnh bác sĩ" width="100" class="img-thumbnail" style="display: none;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Thêm mới</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Bác Sĩ -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Sửa Thông Tin Bác Sĩ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDoctorForm" method="POST" action="<?= BASE_URL ?>/bacsi/update" enctype="multipart/form-data">
                        <input type="hidden" name="MaBacSi" id="editMaBacSi">

                        <div class="mb-3">
                            <label for="editHoTen" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" id="editHoTen" name="HoTen" required>
                        </div>

                        <div class="mb-3">
                            <label for="editMaKhoa" class="form-label">Khoa</label>
                            <select class="form-control" id="editMaKhoa" name="MaKhoa" required>
                                <option value="">-- Chọn Khoa --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['MaKhoa']; ?>"><?= $dept['TenKhoa']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="editSoDienThoai" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="editSoDienThoai" name="SoDienThoai" required>
                        </div>

                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="Email" required>
                        </div>

                        <!-- Mô tả với Summernote -->
                        <div class="mb-3">
                            <label for="editMoTa" class="form-label">Mô tả</label>
                            <textarea class="form-control summernote" id="editMoTa" name="MoTa" placeholder="Nhập mô tả về bác sĩ..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="editHinhAnh" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="editHinhAnh" name="HinhAnh" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                            <input type="hidden" id="editOldHinhAnh" name="OldHinhAnh">
                            <div class="mt-2">
                                <img id="editImagePreview" alt="Hình ảnh bác sĩ" width="100" class="img-thumbnail">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Khởi tạo Summernote
        $(document).ready(function() {
            initSummernote();
        });

        function initSummernote() {
            $('.summernote').summernote({
                height: 200,
                lang: 'vi-VN',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                styleTags: [
                    'p',
                    { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
                    'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
                ],
                fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana'],
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36', '48'],
                callbacks: {
                    onImageUpload: function(files) {
                        // Xử lý upload ảnh nếu cần
                        uploadImage(files[0], this);
                    }
                }
            });
        }

        function uploadImage(file, editor) {
            // Tạo FormData để upload ảnh
            var data = new FormData();
            data.append("file", file);
            
            // Gửi AJAX request để upload ảnh
            $.ajax({
                url: '<?= BASE_URL ?>/upload_image.php', // Tạo file này để xử lý upload
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                success: function(url) {
                    // Chên ảnh vào editor
                    $(editor).summernote('insertImage', url);
                },
                error: function() {
                    alert('Lỗi khi upload ảnh');
                }
            });
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAddModal() {
            document.getElementById('addDoctorForm').reset();
            document.getElementById('addImagePreview').style.display = 'none';
            
            // Reset Summernote
            $('#addMoTa').summernote('reset');
            
            new bootstrap.Modal(document.getElementById('addModal')).show();
        }

        function editDoctor(bacSi) {
            document.getElementById('editMaBacSi').value = bacSi.MaBacSi;
            document.getElementById('editHoTen').value = bacSi.HoTen;
            document.getElementById('editMaKhoa').value = bacSi.MaKhoa;
            document.getElementById('editSoDienThoai').value = bacSi.SoDienThoai;
            document.getElementById('editEmail').value = bacSi.Email;
            document.getElementById('editOldHinhAnh').value = bacSi.HinhAnhBacSi;
            
            // Set Summernote content
            $('#editMoTa').summernote('code', bacSi.MoTa || '');

            const preview = document.getElementById('editImagePreview');
            if (bacSi.HinhAnhBacSi) {
                preview.src = '/' + bacSi.HinhAnhBacSi.replace(/^\/?(booking\/)?/, '');
                preview.style.display = 'block';
            } else {
                preview.src = '/public/uploads/default.png';
                preview.style.display = 'block';
            }

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        // Xử lý khi modal đóng
        $('#addModal, #editModal').on('hidden.bs.modal', function () {
            // Destroy summernote khi modal đóng để tránh conflicts
            $('.summernote').summernote('destroy');
            // Khởi tạo lại
            initSummernote();
        });

        // Tự động ẩn thông báo sau 3 giây
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 3000);
        });

        // Add responsive class to table columns
        document.addEventListener('DOMContentLoaded', function() {
            // Identify which columns to hide on mobile
            const adjustTableForMobile = () => {
                if (window.innerWidth <= 768) {
                    // Hide specific columns on mobile
                    document.querySelectorAll('th:nth-child(2), td:nth-child(2)').forEach(element => {
                        element.classList.add('mobile-hide');
                    });
                    document.querySelectorAll('th:nth-child(5), td:nth-child(5)').forEach(element => {
                        element.classList.add('mobile-hide');
                    });
                    document.querySelectorAll('th:nth-child(6), td:nth-child(6)').forEach(element => {
                        element.classList.add('mobile-hide');
                    });
                    document.querySelectorAll('th:nth-child(7), td:nth-child(7)').forEach(element => {
                        element.classList.add('mobile-hide');
                    });
                } else {
                    // Show all columns on desktop
                    document.querySelectorAll('.mobile-hide').forEach(element => {
                        element.classList.remove('mobile-hide');
                    });
                }
            };

            // Run on page load
            adjustTableForMobile();

            // Run on window resize
            window.addEventListener('resize', adjustTableForMobile);
        });
    </script>
    <script src="<?= BASE_URL ?>//js/script.js"></script>
</body>

</html>