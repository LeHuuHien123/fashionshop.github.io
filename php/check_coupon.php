<?php
include 'datk.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

error_reporting(0);
ini_set('display_errors', 0);

$code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
$total_cart = isset($_POST['total_cart']) ? intval($_POST['total_cart']) : 0;
$today = date('Y-m-d');
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng mã!']);
    exit();
}

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá!']);
    exit();
}

// 1. KIỂM TRA XEM USER ĐÃ TỪNG SỬ DỤNG MÃ NÀY CHƯA (Chặn dùng nhiều lần)
$check_history = $conn->prepare("SELECT id FROM coupon_history WHERE user_id = ? AND coupon_code = ?");
$check_history->bind_param("is", $user_id, $code);
$check_history->execute();
if ($check_history->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => '❌ Bạn đã sử dụng mã giảm giá này cho đơn hàng khác rồi!']);
    $check_history->close();
    $conn->close();
    exit();
}
$check_history->close();

// 2. KIỂM TRA TÍNH HỢP LỆ VÀ SỐ LƯỢT CÒN LẠI CỦA MÃ
$stmt = $conn->prepare("SELECT discount_value, min_order, usage_limit, used_count FROM coupons WHERE code = ? AND status = 1 AND expiry_date >= ?");
if ($stmt) {
    $stmt->bind_param("ss", $code, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $coupon = $result->fetch_assoc();
        
        // Kiểm tra xem mã đã bị dùng hết lượt trên hệ thống chưa
        if ($coupon['used_count'] >= $coupon['usage_limit']) {
            echo json_encode(['success' => false, 'message' => '❌ Mã giảm giá này đã hết lượt sử dụng trên hệ thống!']);
            exit();
        }
        
        // Kiểm tra điều kiện đơn hàng tối thiểu
        if ($total_cart < $coupon['min_order']) {
            echo json_encode([
                'success' => false, 
                'message' => '❌ Đơn hàng chưa đủ tối thiểu từ ' . number_format($coupon['min_order']) . 'đ!'
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => '✅ Áp dụng mã thành công!',
                'discount_value' => $coupon['discount_value']
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '❌ Mã không hợp lệ hoặc đã hết hạn!']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối hệ thống database!']);
}

$conn->close();
exit();
?>