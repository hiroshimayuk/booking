<?php
class DoiMatKhau {
    private $conn;
    private $table = "NguoiDung"; // Sử dụng bảng NguoiDung theo SQL đã cho

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy thông tin người dùng theo MaNguoiDung
    public function getUserById($maNguoiDung) {
        $sql = "SELECT * FROM $this->table WHERE MaNguoiDung = ?";
        $stmt = $this->conn->prepare($sql) or die("Lỗi SQL: " . $this->conn->error);
        $stmt->bind_param("i", $maNguoiDung);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Cập nhật mật khẩu cho người dùng (lưu trực tiếp mật khẩu dưới dạng plaintext)
    public function updatePassword($maNguoiDung, $newPassword) {
        $sql = "UPDATE $this->table SET MatKhau = ? WHERE MaNguoiDung = ?";
        $stmt = $this->conn->prepare($sql) or die("Lỗi SQL: " . $this->conn->error);
        $stmt->bind_param("si", $newPassword, $maNguoiDung);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Lấy thông tin bệnh nhân theo MaNguoiDung
    public function getBenhNhanByMaNguoiDung($maNguoiDung) {
        $sql = "SELECT * FROM BenhNhan WHERE MaNguoiDung = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $maNguoiDung);
        $stmt->execute();
        $result = $stmt->get_result();
        $benhNhan = $result->fetch_assoc();
        $stmt->close();
        return $benhNhan;
    }

    // Lấy email của bác sĩ từ bảng DoctorProfile
    public function getDoctorEmailByUserId($maNguoiDung) {
        $sql = "SELECT Email FROM DoctorProfile WHERE MaNguoiDung = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $maNguoiDung);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['Email'] : null;
    }

    // Thêm phương thức để debug cấu trúc bảng DoctorProfile
    public function getDoctorProfileStructure() {
        $sql = "DESCRIBE DoctorProfile";
        $result = $this->conn->query($sql);
        $structure = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $structure[] = $row;
            }
        }
        return $structure;
    }

    // Lấy thông tin bác sĩ đầy đủ
    public function getDoctorProfileByUserId($maNguoiDung) {
        $sql = "SELECT * FROM DoctorProfile WHERE MaNguoiDung = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $maNguoiDung);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctor = $result->fetch_assoc();
        $stmt->close();
        return $doctor;
    }
}
?>
