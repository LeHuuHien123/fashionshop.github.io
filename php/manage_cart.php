<?php
session_start();
include 'datk.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action == 'delete_selected') {
    $ids = mysqli_real_escape_string($conn, $_POST['ids']);
    // Chỉ xóa các đơn hàng 'pending' (trong giỏ hàng) của chính người dùng đó
    $sql = "DELETE FROM orders WHERE id IN ($ids) AND user_id = $user_id AND status = 'pending'";
    
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }
} 
elseif ($action == 'delete_all') {
    $sql = "DELETE FROM orders WHERE user_id = $user_id AND status = 'pending'";
    
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }
}
?>