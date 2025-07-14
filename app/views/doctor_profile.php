<?php
// Giả định biến $doctor được truyền từ controller
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/BOOKING/');
}
?>

<?php include __DIR__ . '/../../header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân của bác sĩ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            max-width: 600px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="email"],
        form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form input[type="file"] {
            margin-top: 5px;
        }

        form input[type="submit"] {
            margin-top: 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .profile-image {
            text-align: center;
            margin-bottom: 15px;
        }

        .profile-image img {
            max-width: 150px;
            border-radius: 50%;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">


        <div class="section">
            <h1 class="h1 mb-4"><i class="fa-solid fa-user-doctor"></i> Thông tin cá nhân</h1>

            <?php if (isset($_GET["success"])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Cập nhật thông tin thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>DoctorProfile?action=update" enctype="multipart/form-data">
                <div class="profile-image">
                    <?php if (!empty($doctor['HinhAnhBacSi'])): ?>
                        <img src="/<?= ltrim(htmlspecialchars($doctor['HinhAnhBacSi']), '/') ?>?v=<?= time() ?>" 
                             alt="Ảnh bác sĩ" 
                             class="img-thumbnail"
                             onerror="this.src='<?= BASE_URL ?>public/uploads/default.png'">
                    <?php else: ?>
                        <img src="<?= BASE_URL ?>public/uploads/default.png" alt="Ảnh mặc định" class="img-thumbnail">
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="HoTen" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?= htmlspecialchars($doctor['HoTen']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="SoDienThoai" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="SoDienThoai" name="SoDienThoai" value="<?= htmlspecialchars($doctor['SoDienThoai']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="Email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="Email" name="Email" value="<?= htmlspecialchars($doctor['Email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="MoTa" class="form-label">Mô tả</label>
                    <textarea class="form-control summernote" id="MoTa" name="MoTa"><?= htmlspecialchars($doctor['MoTa']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="HinhAnhBacSi" class="form-label">Hình ảnh bác sĩ</label>
                    <input type="file" class="form-control" id="HinhAnhBacSi" name="HinhAnhBacSi" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                    <input type="hidden" name="OldHinhAnh" value="<?= htmlspecialchars($doctor['HinhAnhBacSi'] ?? '') ?>">
                    <div class="mt-2">
                        <img id="imagePreview" alt="Hình ảnh bác sĩ" width="100" class="img-thumbnail" style="display: none;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and Summernote JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-vi-VN.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Summernote
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
                        uploadImage(files[0], this);
                    }
                }
            });
        });

        function uploadImage(file, editor) {
            var data = new FormData();
            data.append("file", file);
            $.ajax({
                url: '<?= BASE_URL ?>/upload_image.php',
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                success: function(url) {
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

        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 3000);
        });
    </script>
</body>
</html>