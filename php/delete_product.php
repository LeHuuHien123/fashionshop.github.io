<?php
session_start();
include 'datk.php';
include 'functions.php'; // Nhúng file hàm

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $session_user_id = (int)$_SESSION['user_id']; // Lấy ID người thực hiện
    
    mysqli_query($conn, "DELETE FROM orders WHERE product_id = $id");
    
    $sql = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        // Ghi nhật ký xóa sản phẩm thành công
        logActivity($conn, $session_user_id, 'Xóa sản phẩm', 'Đã xóa sản phẩm ID #' . $id . ' thông qua lệnh gọi nhanh.');
        echo "success";
    } else {
        echo "Lỗi SQL: " . mysqli_error($conn);
    }
}
?>