<?php
session_start();
include 'datk.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) die("Chưa đăng nhập");

    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    // Nhận biến size_selected từ FormData của JS
    $size_selected = mysqli_real_escape_string($conn, $_POST['size_selected']);
    $qty = (int)$_POST['quantity'];

    // 1. Lấy thông tin sản phẩm
    $res = mysqli_query($conn, "SELECT product_name, price, size, stock FROM products WHERE id = $product_id");
    $product = mysqli_fetch_assoc($res);

    if (!$product) die("Sản phẩm không tồn tại");

    // 2. Xử lý trừ kho theo Size
    $size_arr = explode(',', $product['size']);
    $new_size_arr = [];
    $check_stock = false;

    foreach ($size_arr as $item) {
        $parts = explode(':', trim($item));
        if (count($parts) == 2) {
            $s_name = trim($parts[0]);
            $s_qty = (int)$parts[1];

            if ($s_name == $size_selected) {
                if ($s_qty < $qty) die("Size $s_name hiện chỉ còn $s_qty sản phẩm.");
                $s_qty -= $qty; 
                $check_stock = true;
            }
            $new_size_arr[] = "$s_name:$s_qty";
        }
    }

    if (!$check_stock) die("Không tìm thấy size đã chọn.");

    // 3. Cập nhật lại kho sản phẩm
    $new_size_str = implode(',', $new_size_arr);
    $new_total_stock = $product['stock'] - $qty;
    mysqli_query($conn, "UPDATE products SET size = '$new_size_str', stock = $new_total_stock WHERE id = $product_id");

    // 4. Lưu vào bảng orders - THÊM CỘT size VÀO ĐÂY
    $total_price = $product['price'] * $qty;
    
    // Lưu size_selected vào cột 'size' thay vì dồn vào 'note'
    $sql_order = "INSERT INTO orders (user_id, product_id, size, quantity, total_price, status) 
                  VALUES ($user_id, $product_id, '$size_selected', $qty, $total_price, 'pending')";

    if (mysqli_query($conn, $sql_order)) {
        echo "success";
    } else {
        echo "Lỗi SQL: " . mysqli_error($conn);
    }
}
?>