<?php
session_start(); // Bắt đầu session để có quyền xóa

// 1. Xóa toàn bộ biến session
$_SESSION = array();

// 2. Nếu muốn xóa sạch cả Cookie của Session (tăng tính bảo mật)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hủy session
session_destroy();

// 4. Chuyển hướng về trang chủ hoặc trang login
header("Location: ../index.php");
exit();
?>