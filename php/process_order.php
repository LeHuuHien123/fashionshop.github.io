<?php
include 'datk.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $order_ids       = $_POST['order_ids'] ?? '';
    $phone           = $_POST['phone'] ?? '';
    $address         = $_POST['address'] ?? '';
    $payment_method  = $_POST['payment_method'] ?? '';
    $note            = $_POST['note'] ?? '';
    
    $coupon_code     = !empty($_POST['coupon_code']) ? strtoupper(trim($_POST['coupon_code'])) : null;
    $discount_amount = isset($_POST['discount_amount']) ? intval($_POST['discount_amount']) : 0;

    if (!preg_match('/^[0-9,]+$/', $order_ids)) {
        echo "Lỗi: Danh sách ID đơn hàng không hợp lệ!";
        exit();
    }

    // Sử dụng PREPARED STATEMENT để lưu thông tin đơn hàng
    $sql = "UPDATE orders SET \r\n            status = 'waiting_confirm', \r\n            phone = ?, \r\n            address = ?, \r\n            note = ?, \r\n            payment_method = ?, \r\n            coupon_code = ?, \r\n            discount_amount = ?, \r\n            created_at = NOW() \r\n            WHERE id IN ($order_ids) AND user_id = ? AND id > 0";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param(
            "sssssii", 
            $phone, 
            $address, 
            $note, 
            $payment_method, 
            $coupon_code, 
            $discount_amount, 
            $user_id
        );
        
        if ($stmt->execute()) {
            
            // THỰC HIỆN XỬ LÝ TRỪ LƯỢT DÙNG VÀ LƯU LỊCH SỬ NẾU CÓ DÙNG VOUCHER
            if (!empty($coupon_code) && $discount_amount > 0) {
                
                // 1. Tăng số lần đã sử dụng của coupon lên 1
                $update_coupon = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
                $update_coupon->bind_param("s", $coupon_code);
                $update_coupon->execute();
                $update_coupon->close();
                
                // 2. Lưu vết vào bảng lịch sử coupon_history để chặn dùng lần sau
                $insert_history = $conn->prepare("INSERT INTO coupon_history (user_id, coupon_code) VALUES (?, ?)");
                $insert_history->bind_param("is", $user_id, $coupon_code);
                $insert_history->execute();
                $insert_history->close();
            }

            echo "success";
        } else {
            echo "Lỗi khi cập nhật trạng thái đơn hàng: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
    }
}
$conn->close();
?>