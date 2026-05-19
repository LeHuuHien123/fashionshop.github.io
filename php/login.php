<?php
session_start();
include 'datk.php';

header('Content-Type: application/json; charset=utf-8');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối database thất bại']);
    exit();
}

$action = $_GET['action'] ?? '';

// ==========================================
// XỬ LÝ ĐĂNG KÝ (Xử lý qua tham số action)
// ==========================================
if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'field' => 'general', 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit();
    }

    // 1. Kiểm tra email tồn tại chưa
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // Trả về JSON báo trùng email
        echo json_encode(['success' => false, 'field' => 'email', 'message' => 'Email này đã được đăng ký trên hệ thống!']);
        $stmtCheck->close();
        exit();
    }
    $stmtCheck->close();

    // 2. Tiến hành chèn dữ liệu mới
    $pass = password_hash($password, PASSWORD_DEFAULT); 
    $stmtInsert = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmtInsert->bind_param("sss", $name, $email, $pass);
    
    if ($stmtInsert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đăng ký tài khoản thành công!']);
    } else {
        echo json_encode(['success' => false, 'field' => 'general', 'message' => 'Lỗi hệ thống khi đăng ký!']);
    }
    $stmtInsert->close();
    $conn->close();
    exit();
}

// ==========================================
// XỬ LÝ ĐĂNG NHẬP (Xử lý qua tham số action)
// ==========================================
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmtLogin = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmtLogin->bind_param("s", $email);
    $stmtLogin->execute();
    $resultLogin = $stmtLogin->get_result();

    if ($resultLogin->num_rows > 0) {
        $row = $resultLogin->fetch_assoc();
        
        // Kiểm tra mật khẩu
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];

            // Điều hướng dựa vào nhóm quyền
            $redirect = ($row['role'] === 'admin' || $row['role'] === 'staff') ? 'admin.php' : 'index.php';
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            // SAI MẬT KHẨU -> Gửi lỗi đích danh trường password
            echo json_encode(['success' => false, 'field' => 'password', 'message' => 'Mật khẩu nhập vào không chính xác!']);
        }
    } else {
        // SAI EMAIL -> Gửi lỗi đích danh trường email
        echo json_encode(['success' => false, 'field' => 'email', 'message' => 'Tài khoản Email này không tồn tại!']);
    }
    $stmtLogin->close();
    $conn->close();
    exit();
}
?>