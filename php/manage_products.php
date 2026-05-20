<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'datk.php'; 
include 'functions.php'; 

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
    exit;
}

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$session_user_id = (int)($_SESSION['user_id'] ?? 0);

// --- LOGIC XÓA SẢN PHẨM ---
if ($action === 'delete') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM orders WHERE product_id = $id");
        
        if (mysqli_query($conn, "DELETE FROM products WHERE id = $id")) {
            safe_huno_log($session_user_id, 'Xóa sản phẩm', 'Đã xóa hoàn toàn sản phẩm có ID #' . $id);
            echo "success"; 
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }
    } else {
        echo "ID sản phẩm không hợp lệ!";
    }
    exit;
}

// --- LOGIC THÊM HOẶC SỬA SẢN PHẨM ---
$name = '';
if (isset($_POST['product_name'])) {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
} elseif (isset($_POST['name'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
}

$category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';

$price_raw = isset($_POST['price']) ? $_POST['price'] : '0';
$price = (int)preg_replace('/[^0-9]/', '', $price_raw);

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$size_standard = '';
$total_stock = 0;
if (isset($_POST['size'])) {
    $size_standard = mysqli_real_escape_string($conn, $_POST['size']);
    $pairs = explode(',', $size_standard);
    foreach ($pairs as $pair) {
        $parts = explode(':', $pair);
        if (count($parts) == 2) {
            $total_stock += (int)$parts[1];
        }
    }
}

$current_images = [];
if ($id > 0) {
    $res = mysqli_query($conn, "SELECT image FROM products WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['image'])) {
            $current_images = array_map('trim', explode(',', $row['image']));
        }
    }
}

$deleted_images_str = $_POST['deleted_images'] ?? '';
if (!empty($deleted_images_str)) {
    $deleted_array = array_map('trim', explode(',', $deleted_images_str));
    $current_images = array_filter($current_images, function($img) use ($deleted_array) {
        return !in_array($img, $deleted_array);
    });
}

$uploaded_files = [];
if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
    $file_count = count($_FILES['product_images']['name']);
    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['product_images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['product_images']['tmp_name'][$i];
            $orig_name = basename($_FILES['product_images']['name'][$i]);
            
            $ext = pathinfo($orig_name, PATHINFO_EXTENSION);
            $new_name = time() . '_' . uniqid() . '.' . $ext;
            
            $target_dir = "../img/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . $new_name;
            if (move_uploaded_file($tmp_name, $target_file)) {
                // ĐÃ SỬA: Thêm trực tiếp tiền tố 'img/' vào tên file để lưu đúng cấu trúc database của bạn
                $uploaded_files[] = 'img/' . $new_name;
            }
        }
    }
}

$final_images = array_merge($current_images, $uploaded_files);
$image_string = implode(',', $final_images);

if (empty($image_string)) {
    $image_string = 'img/default.png';
}

if ($id > 0) {
    $sql = "UPDATE products SET product_name='$name', category='$category', size='$size_standard', price='$price', stock='$total_stock', image='$image_string', description='$description' WHERE id=$id";
    $log_action = 'Sửa sản phẩm';
    $log_target = 'Đã cập nhật thông tin sản phẩm: "' . $name . '" (ID: #' . $id . ')';
} else {
    $sql = "INSERT INTO products (product_name, category, size, price, stock, image, description) VALUES ('$name', '$category', '$size_standard', '$price', '$total_stock', '$image_string', '$description')";
    $log_action = 'Thêm sản phẩm';
    $log_target = 'Đã thêm sản phẩm mới vào cửa hàng: "' . $name . '"';
}

if (mysqli_query($conn, $sql)) {
    safe_huno_log($session_user_id, $log_action, $log_target);

    // Lấy lại thông tin sản phẩm chuẩn vừa lưu từ DB để phản hồi chuẩn xác cho JS nhận
    $new_id = ($id > 0) ? $id : mysqli_insert_id($conn);
    
    // Đồng bộ lại chuỗi dữ liệu kích cỡ và tồn kho thực tế
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $new_id,
            'name' => $name,
            'category' => $category,
            'size' => $size_standard,
            'price' => number_format($price) . 'đ',
            'stock' => $total_stock,
            'image' => $image_string // Trả về chuỗi chứa đầy đủ tiền tố 'img/' phân tách bằng dấu phẩy
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi thực thi SQL: ' . mysqli_error($conn)]);
}
exit;
?>/ll