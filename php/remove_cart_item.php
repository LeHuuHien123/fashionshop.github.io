<?php
session_start();
include 'datk.php';

if (!isset($_SESSION['user_id'])) { die("Truy cập bị từ chối"); }
$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Trường hợp xóa tất cả
if ($action === 'delete_all') {
    $sql = "DELETE FROM orders WHERE user_id = $user_id AND status = 'pending'";
} 
// Trường hợp xóa nhiều mục đã chọn
else if ($action === 'delete_selected' && isset($_POST['ids'])) {
    $ids = json_decode($_POST['ids']);
    $clean_ids = implode(',', array_map('intval', $ids));
    $sql = "DELETE FROM orders WHERE id IN ($clean_ids) AND user_id = $user_id AND status = 'pending'";
} 
// Trường hợp xóa 1 mục duy nhất
else {
    $id = intval($_POST['id'] ?? 0);
    $sql = "DELETE FROM orders WHERE id = $id AND user_id = $user_id AND status = 'pending'";
}

if (mysqli_query($conn, $sql)) {
    echo 'success';
} else {
    echo 'Lỗi: ' . mysqli_error($conn);
}
?>