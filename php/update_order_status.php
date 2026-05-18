<?php
session_start();
include 'datk.php'; 
include 'functions.php'; // 1. NHÚNG FILE HÀM LOGS

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids']) && isset($_POST['status'])) {
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập.']);
        exit();
    }

    // Xử lý an toàn chuỗi IDs
    $raw_ids = explode(',', $_POST['ids']);
    $clean_ids_array = array_map('intval', $raw_ids);
    $ids_string = implode(',', $clean_ids_array);

    if (empty($ids_string)) {
        echo json_encode(['success' => false, 'message' => 'Danh sách ID không hợp lệ.']);
        exit();
    }

    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $session_user_id = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'] ?? 'user';

    // Xây dựng câu lệnh SQL dựa trên vai trò
    if ($role === 'admin') {
        $sql = "UPDATE orders SET status = '$status' WHERE id IN ($ids_string)";
    } else {
        if ($status !== 'cancelled') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            exit();
        }
        $sql = "UPDATE orders SET status = '$status' 
                WHERE id IN ($ids_string) 
                AND user_id = $session_user_id \n                AND status = 'waiting_confirm'";
    }
    
    if (mysqli_query($conn, $sql)) {
        // 2. GHI NHẬT KÝ HOẠT ĐỘNG KHI ĐỔI TRẠNG THÁI THÀNH CÔNG
        $status_names = [
            'waiting_confirm' => 'Chờ duyệt',
            'confirmed'       => 'Xác nhận đơn',
            'in_transit'      => 'Đang giao hàng',
            'delivered'       => 'Đã hoàn tất đơn',
            'cancelled'       => 'Đã hủy đơn'
        ];
        $status_text = $status_names[$status] ?? $status;
        
        logActivity($conn, $session_user_id, 'Cập nhật đơn hàng', 'Đã thay đổi trạng thái nhóm đơn hàng ID [' . $ids_string . '] thành: ' . $status_text);

        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . mysqli_error($conn)]);
        exit();
    }
}
?>