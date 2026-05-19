<?php
session_start();
ini_set('display_errors', 0); // Tắt hiển thị lỗi thô ra màn hình để tránh làm hỏng định dạng JSON
error_reporting(E_ALL);

include 'datk.php'; 
include 'functions.php'; // Nhúng file chứa hàm ghi log hoạt động (logActivity)

// Khai báo header trả về định dạng JSON
header('Content-Type: application/json; charset=utf-8');

if (!$conn) {
    echo json_encode([
        'success' => false,
        'error' => "Lỗi kết nối database: " . mysqli_connect_error()
    ]);
    exit;
}

// Kiểm tra quyền hạn truy cập bảo mật đầu tầng backend
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode([
        'success' => false,
        'error' => "Bạn không có quyền thực hiện thao tác này!"
    ]);
    exit;
}

$action = $_POST['action'] ?? '';
$session_user_id = (int)($_SESSION['user_id'] ?? 0); // ID của Admin/Staff đang thao tác

// ==========================================
// THAO TÁC: LƯU (THÊM MỚI HOẶC CẬP NHẬT)
// ==========================================
if ($action === 'save') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'user');
    $password = $_POST['password'] ?? '';
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : '';

    // Kiểm tra dữ liệu đầu vào bắt buộc
    if (empty($fullname) || empty($email)) {
        echo json_encode([
            'success' => false,
            'error' => "Vui lòng nhập đầy đủ Họ tên và Email!"
        ]);
        exit;
    }

    if ($id > 0) {
        // HÀNH ĐỘNG: CẬP NHẬT (SỬA THÔNG TIN)
        $updateFields = "fullname='$fullname', email='$email', role='$role', phone='$phone'";
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $updateFields .= ", password='$hashed_pass'";
        }
        
        $sql = "UPDATE users SET $updateFields WHERE id = $id";
        $log_action = 'Cập nhật tài khoản';
        $log_target = 'Đã sửa tài khoản khách hàng: ' . $email . ' thành nhóm quyền [' . strtoupper($role) . ']';
    } else {
        // HÀNH ĐỘNG: THÊM MỚI TÀI KHOẢN
        if (empty($password)) {
            echo json_encode([
                'success' => false,
                'error' => "Mật khẩu là bắt buộc khi thêm thành viên mới!"
            ]);
            exit;
        }
        
        // Kiểm tra xem trùng Email trong hệ thống chưa trước khi insert
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            echo json_encode([
                'success' => false,
                'error' => "Email này đã được đăng ký bởi một khách hàng khác!"
            ]);
            exit;
        }

        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password, role, phone) VALUES ('$fullname', '$email', '$hashed_pass', '$role', '$phone')";
        $log_action = 'Tạo tài khoản mới';
        $log_target = 'Đã tạo thủ công tài khoản thành viên mới: ' . $email . ' với quyền [' . strtoupper($role) . ']';
    }

    // Thực thi truy vấn dữ liệu vào MySQL
    if (mysqli_query($conn, $sql)) {
        // Ghi lại lịch sử hoạt động vào bảng logs hệ thống
        if (function_exists('logActivity') && $session_user_id > 0) {
            logActivity($conn, $session_user_id, $log_action, $log_target);
        }
        
        // Lấy chính xác ID vừa xử lý (ID tự tăng khi thêm mới hoặc ID truyền vào khi sửa)
        $return_id = ($id > 0) ? $id : mysqli_insert_id($conn);
        
        // Xuất JSON thành công phản hồi chuẩn chỉ cho admin.js nhận diện
        echo json_encode([
            'success' => true,
            'id' => $return_id,
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'role' => $role
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => "Lỗi thực thi dữ liệu hệ thống: " . mysqli_error($conn)
        ]);
    }
    exit; 
}

// ==========================================
// THAO TÁC: XÓA THÀNH VIÊN
// ==========================================
if ($action === 'delete') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => "Mã khách hàng không hợp lệ!"]);
        exit;
    }

    // Không cho phép Admin tự xóa chính tài khoản của mình đang đăng nhập
    if ($id === $session_user_id) {
        echo json_encode(['success' => false, 'error' => "Bạn không thể tự xóa tài khoản của chính mình khi đang phiên làm việc!"]);
        exit;
    }

    // Lấy thông tin Email khách hàng trước khi xóa để phục vụ việc ghi Log chi tiết
    $user_query = mysqli_query($conn, "SELECT email FROM users WHERE id = $id");
    $user_data = mysqli_fetch_assoc($user_query);
    $target_email = $user_data['email'] ?? 'Không rõ';

    $sql = "DELETE FROM users WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        // Ghi lại lịch sử hoạt động xóa
        if (function_exists('logActivity') && $session_user_id > 0) {
            logActivity($conn, $session_user_id, 'Xóa tài khoản', 'Đã xóa tài khoản thành viên: ' . $target_email);
        }
        
        echo json_encode(['success' => true]);
    } else {
        // Xử lý bắt lỗi khóa ngoại (nếu khách hàng này đã có hóa đơn hàng trong hệ thống)
        $error_msg = mysqli_error($conn);
        if (strpos($error_msg, 'foreign key constraint fails') !== false) {
            echo json_encode([
                'success' => false,
                'error' => "Không thể xóa khách hàng này vì tài khoản đang chứa dữ liệu lịch sử mua hàng liên kết!"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => "Lỗi hệ thống: " . $error_msg
            ]);
        }
    }
    exit;
}

// Nếu gửi Request tầm bậy không trúng action save/delete
echo json_encode(['success' => false, 'error' => 'Yêu cầu hành động (Action) không được hỗ trợ']);
exit;