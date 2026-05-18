<?php
session_start();
include 'datk.php'; 

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Quyền truy cập bị từ chối!']);
    exit();
}

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

// 1. XỬ LÝ THÊM HOẶC SỬ SỬA COUPON
if ($action === 'save') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $code = strtoupper(trim($_POST['code']));
    $discount_value = intval($_POST['discount_value']);
    $min_order = intval($_POST['min_order']);
    $usage_limit = isset($_POST['usage_limit']) ? intval($_POST['usage_limit']) : 100; // Nhận thêm giới hạn lượt dùng
    $expiry_date = $_POST['expiry_date'];
    $status = intval($_POST['status']);

    if (empty($code) || $discount_value <= 0 || empty($expiry_date) || $usage_limit <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ dữ liệu hợp lệ!']);
        exit();
    }

    if ($id > 0) {
        // CẬP NHẬT COUPON (SỬA)
        $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_value = ?, min_order = ?, usage_limit = ?, expiry_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("siiisii", $code, $discount_value, $min_order, $usage_limit, $expiry_date, $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật mã giảm giá thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật hoặc trùng mã voucher!']);
        }
        $stmt->close();
    } else {
        // TẠO MỚI COUPON (THÊM) - Mặc định used_count ban đầu bằng 0
        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_value, min_order, usage_limit, used_count, expiry_date, status) VALUES (?, ?, ?, ?, 0, ?, ?)");
        $stmt->bind_param("siiisi", $code, $discount_value, $min_order, $usage_limit, $expiry_date, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm mã giảm giá mới thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã voucher này đã tồn tại trong hệ thống!']);
        }
        $stmt->close();
    }
}

// 2. XỬ LÝ XÓA COUPON
if ($action === 'delete') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa mã giảm giá thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa mã giảm giá này!']);
        }
        $stmt->close();
    }
}
$conn->close();
?>