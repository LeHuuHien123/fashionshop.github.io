<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'datk.php'; 
include 'functions.php'; // 1. NHÚNG FILE HÀM LOGS

if (!$conn) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

$action = $_POST['action'] ?? '';
$session_user_id = (int)$_SESSION['user_id']; // Lấy ID người quản trị thao tác

if ($action === 'save') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'user');
    $password = $_POST['password'] ?? '';

    if ($id > 0) {
        // CẬP NHẬT (SỬA)
        $updateFields = "fullname='$fullname', email='$email', role='$role'";
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $updateFields .= ", password='$hashed_pass'";
        }
        $sql = "UPDATE users SET $updateFields WHERE id = $id";
        $log_action = 'Cập nhật tài khoản';
        $log_target = 'Đã sửa tài khoản khách hàng: ' . $email . ' thành nhóm quyền [' . strtoupper($role) . ']';
    } else {
        // THÊM MỚI
        if (empty($password)) {
            echo "Mật khẩu là bắt buộc khi thêm mới!";
            exit;
        }
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$hashed_pass', '$role')";
        $log_action = 'Tạo tài khoản mới';
        $log_target = 'Đã tạo thủ công tài khoản thành viên mới: ' . $email . ' với quyền [' . strtoupper($role) . ']';
    }

    if (mysqli_query($conn, $sql)) {
        // 2. GHI LOG KHI LƯU THÀNH CÔNG
        logActivity($conn, $session_user_id, $log_action, $log_target);
        echo "success";
    } else {
        echo "Lỗi SQL: " . mysqli_error($conn);
    }
    exit; 
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    if ($id > 0) {
        if (mysqli_query($conn, "DELETE FROM users WHERE id = $id")) {
            // 3. GHI LOG KHI XÓA TÀI KHOẢN THÀNH CÔNG
            logActivity($conn, $session_user_id, 'Xóa tài khoản', 'Đã xóa hoàn toàn tài khoản người dùng có ID #' . $id);
            echo "success";
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }
    }
    exit;
}
?>