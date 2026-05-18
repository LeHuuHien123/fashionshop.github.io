<?php
session_start();
include 'datk.php';
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập lại!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'] ?? '';

    // Bắt đầu câu lệnh SQL cơ bản
    $sql = "UPDATE users SET fullname='$fullname', phone='$phone', address='$address'";

    // Nếu người dùng có nhập mật khẩu mới, mới thêm vào câu lệnh UPDATE
    if (!empty($password)) {
        // Nên dùng password_hash nếu bạn muốn bảo mật cao, 
        // nhưng ở đây mình làm theo cấu trúc hiện tại của bạn:
        $sql .= ", password='$password'"; 
    }

    $sql .= " WHERE id='$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['fullname'] = $fullname; // Cập nhật hiển thị ngay lập tức
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
?>