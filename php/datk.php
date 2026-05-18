<?php
$servername = "localhost";
$username   = "root";      // XAMPP mặc định là root
$password   = "";          // XAMPP mặc định để trống
$dbname     = "shop"; // Thay bằng tên database bạn đã tạo trong phpMyAdmin

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Thiết lập font tiếng Việt
mysqli_set_charset($conn, "utf8mb4");
?>