<?php
session_start();
include 'datk.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['order_ids'])) {
    // 1. Xử lý chuỗi IDs an toàn
    $raw_ids = explode(',', $_POST['order_ids']);
    $clean_ids = array_map('intval', $raw_ids); 
    $ids_string = implode(',', $clean_ids);

    if (empty($ids_string)) {
        die("Danh sách ID không hợp lệ");
    }

    // --- CHỨC NĂNG XÓA (Xóa khỏi giỏ hàng) ---
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Chỉ cho phép xóa các dòng đang là 'pending' của chính user đó
        $sql = "DELETE FROM orders WHERE id IN ($ids_string) AND user_id = $user_id AND status = 'pending'";
        
        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo "error";
        }

    // --- CHỨC NĂNG CẬP NHẬT TRẠNG THÁI (Hủy đơn hàng đã đặt) ---
    } elseif (isset($_POST['status'])) {
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Bảo mật: Khách hàng chỉ được phép đổi sang trạng thái 'cancelled'
        if ($_SESSION['role'] !== 'admin' && $status !== 'cancelled') {
             die("Hành động không hợp lệ");
        }

        // Chỉ cập nhật những đơn chưa giao/hoàn tất (tránh khách hàng hủy đơn khi đang giao)
        $sql = "UPDATE orders SET status = '$status' 
                WHERE id IN ($ids_string) 
                AND user_id = $user_id 
                AND status IN ('waiting_confirm', 'confirmed')";

        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo "error";
        }
    }
}
?>