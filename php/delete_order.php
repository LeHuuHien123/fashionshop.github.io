<?php
session_start();
include 'datk.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids'])) {
    $ids = mysqli_real_escape_string($conn, $_POST['ids']);
    
    // Xóa đơn hàng dựa trên danh sách ID gộp
    $sql = "DELETE FROM orders WHERE id IN ($ids)";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu gửi lên không hợp lệ.']);
}
?>