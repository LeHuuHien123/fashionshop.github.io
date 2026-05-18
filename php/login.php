<?php
session_start();
include 'datk.php';

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// XỬ LÝ ĐĂNG KÝ
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    // Mã hóa mật khẩu bảo mật cao bằng BCRYPT
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // 1. Kiểm tra email tồn tại chưa bằng PREPARED STATEMENT
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->bind_param("s", $email); // "s" đại diện cho kiểu dữ liệu string
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "<script>alert('Email này đã được đăng ký!'); window.location.href='../login.html';</script>";
    } else {
        // 2. Tiến hành chèn dữ liệu mới bằng PREPARED STATEMENT
        $stmtInsert = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'user')");
        // "sss" tương ứng với 3 biến truyền vào đều là chuỗi (string)
        $stmtInsert->bind_param("sss", $name, $email, $pass);
        
        if ($stmtInsert->execute() === TRUE) {
            echo "<script>alert('Đăng ký thành công!'); window.location.href='../login.html';</script>";
        } else {
            echo "Lỗi hệ thống đăng ký vui lòng thử lại sau.";
        }
        $stmtInsert->close();
    }
    $stmtCheck->close();
}

// XỬ LÝ ĐĂNG NHẬP
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Kiểm tra đăng nhập bằng PREPARED STATEMENT
    $stmtLogin = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmtLogin->bind_param("s", $email);
    $stmtLogin->execute();
    $resultLogin = $stmtLogin->get_result();

    if ($resultLogin->num_rows > 0) {
        $row = $resultLogin->fetch_assoc();
        
        // Kiểm tra mật khẩu mã hóa có khớp hay không
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname']; // Đồng bộ lưu fullname hiển thị lên navbar
            $_SESSION['role'] = $row['role'];

            // Phân quyền: Admin vào admin.php, User vào trang chủ index.php
            if ($row['role'] == 'admin') {
                header("Location: ../admin.php");
            } else {
                header("Location: ../index.php");
            }
            exit(); // Chặn code chạy tiếp sau khi chuyển hướng
        } else {
            echo "<script>alert('Sai mật khẩu!'); window.location.href='../login.html';</script>";
        }
    } else {
        echo "<script>alert('Email không tồn tại!'); window.location.href='../login.html';</script>";
    }
    $stmtLogin->close();
}

$conn->close();
?>