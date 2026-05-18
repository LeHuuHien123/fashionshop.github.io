<?php
// Kiểm tra trạng thái session để tránh lỗi Notice/Warning trùng lập khởi chạy
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm ghi nhật ký hệ thống độc lập phiên bản (Không sợ lệch tham số)
if (!function_exists('safe_huno_log')) {
    function safe_huno_log($user_id, $action, $target) {
        global $conn;
        
        $user_id = (int)$user_id;
        $action = mysqli_real_escape_string($conn, $action);
        $target = mysqli_real_escape_string($conn, $target);
        
        // Tự động kiểm tra và tạo bảng nếu MySQL trên máy chưa đồng bộ dữ liệu
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `activity_logs` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `action` VARCHAR(255) NOT NULL,
          `target` TEXT NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $sql = "INSERT INTO activity_logs (user_id, action, target, created_at) 
                VALUES ($user_id, '$action', '$target', CURRENT_TIMESTAMP)";
        return mysqli_query($conn, $sql);
    }
}

// Đồng bộ tên hàm cũ (logActivity) để tương thích ngược nếu các file khác trong hệ thống của bạn có gọi đến
if (!function_exists('logActivity')) {
    function logActivity($conn, $user_id, $action, $target) {
        $user_id = (int)$user_id;
        $action = mysqli_real_escape_string($conn, $action);
        $target = mysqli_real_escape_string($conn, $target);
        
        $sql = "INSERT INTO activity_logs (user_id, action, target, created_at) 
                VALUES ($user_id, '$action', '$target', CURRENT_TIMESTAMP)";
        return mysqli_query($conn, $sql);
    }
}