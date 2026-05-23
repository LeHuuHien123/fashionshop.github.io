<?php
session_start();
ini_set('display_errors', 0); // Ngăn lỗi thô làm hỏng dữ liệu AJAX trả về
error_reporting(E_ALL);

include 'datk.php';

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

    // ĐÃ ĐỔI: Chuyển 'waiting_confirm' thành 'Chờ xử lý' để đồng bộ với bộ lọc của Staff/Admin
    $sql = "UPDATE orders SET 
            status = 'waiting_confirm', 
            phone = ?, 
            address = ?, 
            note = ?, 
            payment_method = ?, 
            coupon_code = ?, 
            discount_amount = ?, 
            created_at = NOW() 
            WHERE id IN ($order_ids) AND user_id = ? AND id > 0";

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
                if ($update_coupon) {
                    $update_coupon->bind_param("s", $coupon_code);
                    $update_coupon->execute();
                    $update_coupon->close();
                }
                
                // 2. Lưu vết vào bảng lịch sử coupon_history để chặn dùng lần sau
                $insert_history = $conn->prepare("INSERT INTO coupon_history (user_id, coupon_code) VALUES (?, ?)");
                if ($insert_history) {
                    $insert_history->bind_param("is", $user_id, $coupon_code);
                    $insert_history->execute();
                    $insert_history->close();
                }
            }

            echo "success";
        } else {
            echo "Lỗi khi cập nhật trạng thái đơn hàng: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
    }
} else {
    echo "Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn!";
}

$conn->close();
?>