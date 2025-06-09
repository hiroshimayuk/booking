<?php
require_once 'app/config/database.php';
header('Content-Type: application/json');

if (isset($_GET['doctor_id'])) {
    $doctor_id = intval($_GET['doctor_id']);

    // Lấy danh sách ngày làm việc
    if (!isset($_GET['date'])) {
        $sql = "SELECT DISTINCT NgayLamViec FROM LichLamViec WHERE MaBacSi = ? AND TrangThai = 'Đã xác nhận' AND NgayLamViec >= CURDATE() ORDER BY NgayLamViec ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['NgayLamViec'];
        }
        echo json_encode($dates);
        exit;
    }

    // Lấy khung giờ làm việc chia 30 phút
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        $sql = "SELECT GioBatDau, GioKetThuc FROM LichLamViec WHERE MaBacSi = ? AND NgayLamViec = ? AND TrangThai = 'Đã xác nhận'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $doctor_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $slots = [];
        $all_slots = [];
        while ($row = $result->fetch_assoc()) {
            $start = strtotime($row['GioBatDau']);
            $end = strtotime($row['GioKetThuc']);
            while ($start < $end) {
                $slotStart = date('H:i', $start);
                $all_slots[] = $slotStart;
                $start += 30*60;
            }
        }
        // Lấy các giờ đã có lịch hẹn
        $sql2 = "SELECT TIME(NgayGio) as time FROM LichHen WHERE MaBacSi = ? AND DATE(NgayGio) = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("is", $doctor_id, $date);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $booked = [];
        while ($row2 = $result2->fetch_assoc()) {
            $booked[] = substr($row2['time'], 0, 5);
        }
        // Trả về slot và trạng thái
        $response = [];
        foreach ($all_slots as $slot) {
            $response[] = [
                'start' => $slot,
                'booked' => in_array($slot, $booked)
            ];
        }
        echo json_encode($response);
        exit;
    }
}
echo json_encode([]);