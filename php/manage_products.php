<?php
session_start();
include 'datk.php'; 
include 'functions.php'; // 1. NHÚNG FILE HÀM LOGS

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
    exit;
}

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$session_user_id = (int)$_SESSION['user_id']; // Lấy ID người dùng

// --- LOGIC XÓA SẢN PHẨM ---
if ($action === 'delete') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM orders WHERE product_id = $id");
        
        if (mysqli_query($conn, "DELETE FROM products WHERE id = $id")) {
            // 2. GHI LOG HÀNH ĐỘNG XÓA
            logActivity($conn, $session_user_id, 'Xóa sản phẩm', 'Đã xóa hoàn toàn sản phẩm có ID #' . $id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . mysqli_error($conn)]);
        }
    }
    exit;
}

// --- LOGIC LƯU (THÊM MỚI / CẬP NHẬT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Xử lý chuỗi size và tính tổng kho
    $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];
    $stocks = isset($_POST['stocks']) ? $_POST['stocks'] : [];
    $size_arr = [];
    foreach ($sizes as $index => $sz) {
        if (!empty($sz)) {
            $stk = isset($stocks[$index]) ? (int)$stocks[$index] : 0;
            $size_arr[] = trim($sz) . ":" . $stk;
        }
    }
    $size_standard = implode(',', $size_arr);
    
    // Xử lý ảnh
    $old_images = isset($_POST['old_images']) ? $_POST['old_images'] : [];
    $final_images = $old_images; 
    if (isset($_FILES['images'])) {
        $files = $_FILES['images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . $i . '.' . $ext;
                $target_path = '../img/' . $new_filename;
                if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                    $final_images[] = 'img/' . $new_filename;
                }
            }
        }
    }
    $image_string = implode(',', $final_images);

    $total_stock = 0;
    $size_parts = explode(',', $size_standard); 
    foreach ($size_parts as $part) {
        $item = explode(':', $part);
        if (count($item) == 2) {
            $total_stock += (int)trim($item[1]); 
        }
    }

    if ($id > 0) {
        // CẬP NHẬT SẢN PHẨM
        $sql = "UPDATE products SET \r
                product_name='$name', category='$category', size='$size_standard', \r
                price='$price', stock='$total_stock', image='$image_string', description='$description' \r
                WHERE id=$id";
        $log_action = 'Sửa sản phẩm';
        $log_target = 'Đã cập nhật thông tin sản phẩm: "' . $name . '" (ID: #' . $id . ')';
    } else {
        // THÊM MỚI SẢN PHẨM
        $sql = "INSERT INTO products (product_name, category, size, price, stock, image, description) \r
                VALUES ('$name', '$category', '$size_standard', '$price', '$total_stock', '$image_string', '$description')";
        $log_action = 'Thêm sản phẩm';
        $log_target = 'Đã thêm sản phẩm mới vào cửa hàng: "' . $name . '"';
    }

    if (mysqli_query($conn, $sql)) {
        // 3. GHI LOG HÀNH ĐỘNG THÊM HOẶC SỬA SẢN PHẨM THÀNH CÔNG
        logActivity($conn, $session_user_id, $log_action, $log_target);

        $new_id = ($id > 0) ? $id : mysqli_insert_id($conn);
        $first_image = !empty($final_images) ? reset($final_images) : 'img/default.png';
        
        echo json_encode([
            'success' => true,
            'product' => [
                'id' => $new_id,
                'name' => $name,
                'category' => $category,
                'price' => number_format($price) . 'đ',
                'stock' => $total_stock,
                'image' => $first_image
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . mysqli_error($conn)]);
    }
    exit;
}
?>