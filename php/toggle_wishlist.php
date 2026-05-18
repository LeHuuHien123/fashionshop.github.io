<?php
// Tắt hiển thị lỗi trực tiếp ra màn hình để tránh làm hỏng cấu trúc JSON
error_reporting(0);
session_start();
include 'datk.php'; // Kiểm tra đường dẫn này có đúng chưa (thường là cùng thư mục php)

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'not_logged_in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;

if ($product_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID không hợp lệ']);
    exit;
}

// Kiểm tra trạng thái
$check = mysqli_query($conn, "SELECT id FROM favorites WHERE user_id = '$user_id' AND product_id = '$product_id'");

if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "DELETE FROM favorites WHERE user_id = '$user_id' AND product_id = '$product_id'");
    echo json_encode(['status' => 'removed']);
} else {
    mysqli_query($conn, "INSERT INTO favorites (user_id, product_id) VALUES ('$user_id', '$product_id')");
    echo json_encode(['status' => 'added']);
}
?>