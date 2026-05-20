<?php
session_start();
include 'datk.php'; 

header('Content-Type: application/json');

// Kiểm tra quyền (bảo mật)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
    exit();
}

// Hỗ trợ nhận ID đơn hàng (thường là POST hoặc GET)
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id > 0) {
    // 1. Thực hiện xóa trong database
    $sql = "DELETE FROM orders WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        // Trả về success: true để JS biết mà xóa dòng trên giao diện
        echo json_encode(['success' => true, 'deleted_id' => $id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ.']);
}
?>