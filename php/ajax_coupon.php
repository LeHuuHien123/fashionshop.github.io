<?php
session_start();
include 'datk.php'; 
include 'functions.php'; // Chắc chắn phải có file này để gọi được hàm logActivity

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Quyền truy cập bị từ chối!']);
    exit();
}

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$session_user_id = (int)($_SESSION['user_id'] ?? 0); // Lấy ID của người đang thực hiện thao tác

// ==========================================
// 1. XỬ LÝ THÊM HOẶC SỬA COUPON
// ==========================================
if ($action === 'save') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discount_value = intval($_POST['discount_value'] ?? 0);
    $min_order = intval($_POST['min_order'] ?? 0);
    $usage_limit = isset($_POST['usage_limit']) ? intval($_POST['usage_limit']) : 100;
    $expiry_date = $_POST['expiry_date'] ?? '';
    $status = intval($_POST['status'] ?? 0);

    if (empty($code) || $discount_value <= 0 || empty($expiry_date) || $usage_limit <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ dữ liệu hợp lệ!']);
        exit();
    }

    if ($id > 0) {
        // CẬP NHẬT COUPON (SỬA)
        $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_value = ?, min_order = ?, usage_limit = ?, expiry_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("siiisii", $code, $discount_value, $min_order, $usage_limit, $expiry_date, $status, $id);
        
        if ($stmt->execute()) {
            // ĐÃ THÊM: Ghi nhật ký khi SỬA voucher thành công
            if (function_exists('logActivity') && $session_user_id > 0) {
                $log_action = 'Cập nhật mã giảm giá';
                $log_target = "Đã sửa mã giảm giá [$code]: Giảm " . number_format($discount_value) . "%, Đơn tối thiểu: " . number_format($min_order) . "đ, Hạn dùng: $expiry_date";
                logActivity($conn, $session_user_id, $log_action, $log_target);
            }

            echo json_encode(['success' => true, 'message' => 'Cập nhật mã giảm giá thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật hoặc trùng mã voucher!']);
        }
        $stmt->close();
    } else {
        // TẠO MỚI COUPON (THÊM)
        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_value, min_order, usage_limit, used_count, expiry_date, status) VALUES (?, ?, ?, ?, 0, ?, ?)");
        $stmt->bind_param("siiisi", $code, $discount_value, $min_order, $usage_limit, $expiry_date, $status);
        
        if ($stmt->execute()) {
            // ĐÃ THÊM: Ghi nhật ký khi THÊM voucher thành công
            if (function_exists('logActivity') && $session_user_id > 0) {
                $log_action = 'Thêm mã giảm giá';
                $log_target = "Đã tạo mã giảm giá mới [$code]: Giảm " . number_format($discount_value) . "%, Lượt dùng: $usage_limit, Hạn dùng: $expiry_date";
                logActivity($conn, $session_user_id, $log_action, $log_target);
            }

            echo json_encode(['success' => true, 'message' => 'Thêm mã giảm giá mới thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã voucher này đã tồn tại trong hệ thống!']);
        }
        $stmt->close();
    }
}

// ==========================================
// 2. XỬ LÝ XÓA COUPON
// ==========================================
if ($action === 'delete') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {
        // ĐÃ THÊM: Truy vấn lấy mã 'code' trước khi xóa hẳn khỏi database
        $code_deleted = "ID: " . $id;
        $check_stmt = $conn->prepare("SELECT code FROM coupons WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        if ($check_stmt->execute()) {
            $res = $check_stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $code_deleted = $row['code'];
            }
        }
        $check_stmt->close();

        // Tiến hành xóa dữ liệu
        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // ĐÃ THÊM: Ghi nhật ký khi XÓA voucher thành công
            if (function_exists('logActivity') && $session_user_id > 0) {
                logActivity($conn, $session_user_id, 'Xóa mã giảm giá', "Đã xóa mã giảm giá hệ thống: [$code_deleted]");
            }

            echo json_encode(['success' => true, 'message' => 'Xóa mã giảm giá thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa mã giảm giá này!']);
        }
        $stmt->close();
    }
}

$conn->close();
?>