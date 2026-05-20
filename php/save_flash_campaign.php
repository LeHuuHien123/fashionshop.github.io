<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh'); 
include 'datk.php'; // Đảm bảo file này cung cấp biến $conn

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    die("Quyền truy cập bị từ chối!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 0;
    $campaign_voucher = strtoupper(trim($_POST['campaign_voucher'] ?? ''));
    $product_ids = $_POST['product_ids'] ?? [];

    if ($duration_minutes <= 0 || empty($campaign_voucher) || empty($product_ids)) {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin!'); window.history.back();</script>";
        exit();
    }

    $start_timestamp = time();
    $end_timestamp = $start_timestamp + ($duration_minutes * 60);

    // Hủy kích hoạt tất cả các chiến dịch Flash Sale cũ trước đó
    mysqli_query($conn, "UPDATE flash_sales SET status = 0");

    // 1. Thêm thông tin chiến dịch mới (Đã sửa truyền thêm biến $conn)
    $sql_insert_campaign = "INSERT INTO flash_sales (start_time, end_time, voucher_code, status) VALUES ($start_timestamp, $end_timestamp, '$campaign_voucher', 1)";
    
    if (mysqli_query($conn, $sql_insert_campaign)) {
        $flash_sale_id = mysqli_insert_id($conn);

        // 2. Duyệt qua mảng Checkbox sản phẩm để lưu
        foreach ($product_ids as $p_id) {
            $p_id = intval($p_id);
            $sql_insert_item = "INSERT INTO flash_sale_products (flash_sale_id, product_id) VALUES ($flash_sale_id, $p_id)";
            mysqli_query($conn, $sql_insert_item); // Đoạn này đã có $conn sẵn rồi
        }

        echo "<script>alert('Kích hoạt chiến dịch Flash Sale thành công!'); window.location.href='../admin.php';</script>";
    } else {
        echo "Lỗi hệ thống: " . mysqli_error($conn);
    }
}
?>