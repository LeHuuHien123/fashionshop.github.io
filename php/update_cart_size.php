<?php
session_start();
include 'datk.php'; // Rà soát lại đường dẫn này!

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $order_id = intval($_POST['order_id']);
    $new_size = mysqli_real_escape_string($conn, $_POST['new_size']);
    $user_id = $_SESSION['user_id'];

    // Câu lệnh SQL: Rà soát tên cột 'size' trong bảng 'orders'
    $sql = "UPDATE orders SET size = '$new_size' WHERE id = $order_id AND user_id = $user_id AND status = 'pending'";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn SQL']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
}