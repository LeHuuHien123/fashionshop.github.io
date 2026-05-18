<?php
session_start();
include 'datk.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập lại!']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Kiểm tra thời gian (21 ngày = 1.814.400 giây)
$res = mysqli_query($conn, "SELECT last_avatar_update FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($res);

if ($user['last_avatar_update']) {
    $last_update = strtotime($user['last_avatar_update']);
    $diff = time() - $last_update;
    $seconds_in_21_days = 21 * 24 * 60 * 60;

    if ($diff < $seconds_in_21_days) {
        $days_left = ceil(($seconds_in_21_days - $diff) / (24 * 60 * 60));
        echo json_encode(['success' => false, 'message' => "Bạn cần đợi $days_left ngày nữa để tiếp tục đổi ảnh."]);
        exit;
    }
}

// 2. Xử lý upload ảnh duy nhất
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $target_dir = "../img/avt/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

    $file_ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($file_ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Chỉ hỗ trợ ảnh định dạng JPG, PNG, WEBP.']);
        exit;
    }

    // Tên file duy nhất: avt_123_171588.png
    $new_name = "avt_" . $user_id . "_" . time() . "." . $file_ext;
    $path = $target_dir . $new_name;

    if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $path)) {
        // Cập nhật database: lưu tên file và thời gian hiện tại
        $sql = "UPDATE users SET avatar = '$new_name', last_avatar_update = NOW() WHERE id = '$user_id'";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật dữ liệu.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể lưu file vào thư mục img/avt/']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn 1 file ảnh.']);
}