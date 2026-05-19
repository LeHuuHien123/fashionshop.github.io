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

    // Xử lý an toàn chuỗi IDs nhằm phòng chống lỗi SQL Injection
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

    // ĐÃ ĐIỀU CHỈNH: Cả tài khoản 'admin' và 'staff' đều có quyền xử lý quản trị đơn hàng
    if ($role === 'admin' || $role === 'staff') {
        $sql = "UPDATE orders SET status = '$status' WHERE id IN ($ids_string)";
    } else {
        // Tài khoản khách hàng thông thường (user) chỉ được phép tự HỦY đơn hàng của chính họ
        // Đổi trạng thái 'cancelled' thành 'Đã hủy' để đồng bộ tiếng Việt
        if ($status !== 'Đã hủy' && $status !== 'cancelled') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            exit();
        }
        
        // Cập nhật trạng thái hủy đồng bộ chuỗi tiếng Việt
        $sql = "UPDATE orders SET status = 'Đã hủy' 
                WHERE id IN ($ids_string) 
                AND user_id = $session_user_id 
                AND (status = 'Chờ xử lý' OR status = 'waiting_confirm')";
    }
    
    if (mysqli_query($conn, $sql)) {
        // 2. GHI NHẬT KÝ HOẠT ĐỘNG ĐỒNG BỘ TRỰC TIẾP LÊN HỆ THỐNG LOGS 3 GIÂY
        $status_names = [
            'waiting_confirm'=> 'Chờ xử lý',
            'Chờ xử lý'      => 'Chờ xử lý',
            'confirmed'      => 'Đang xử lý',
            'Đang xử lý'     => 'Đang xử lý',
            'in_transit'     => 'Đang giao',
            'Đang giao'      => 'Đang giao',
            'delivered'      => 'Đã giao',
            'Đã giao'        => 'Đã giao',
            'cancelled'      => 'Đã hủy',
            'Đã hủy'         => 'Đã hủy'
        ];
        $status_text = $status_names[$status] ?? $status;
        
        // Gọi hàm ghi log hoạt động kèm tên nhóm quyền thực hiện cho rõ ràng
        $log_role = ($role === 'admin') ? 'Admin' : (($role === 'staff') ? 'Staff' : 'Khách hàng');
        logActivity($conn, $session_user_id, 'Cập nhật đơn hàng', '[' . $log_role . '] Đã thay đổi trạng thái nhóm đơn hàng ID [' . $ids_string . '] thành: ' . $status_text);

        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . mysqli_error($conn)]);
        exit();
    }
}
?>  