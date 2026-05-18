<?php
session_start();
include 'datk.php'; // Kết nối Database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        die("Bạn cần đăng nhập để thực hiện chức năng này!");
    }

    // 2. Lấy dữ liệu an toàn
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $total_price = (float)$_POST['total_price'];
    
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $note = isset($_POST['note']) ? mysqli_real_escape_string($conn, $_POST['note']) : '';

    // 3. Kiểm tra số lượng tồn kho trước khi đặt
    $checkStockRes = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
    $stockData = mysqli_fetch_assoc($checkStockRes);

    if ($stockData['stock'] < $quantity) {
        die("Rất tiếc, sản phẩm này chỉ còn " . $stockData['stock'] . " cái trong kho.");
    }

    // 4. Lưu đơn hàng vào bảng orders
    $sql_order = "INSERT INTO orders (user_id, product_id, quantity, total_price, phone, address, note, status) 
                  VALUES ('$user_id', '$product_id', '$quantity', '$total_price', '$phone', '$address', '$note', 'pending')";

    if (mysqli_query($conn, $sql_order)) {
        
        // 5. Nếu lưu đơn thành công -> Trừ đi số lượng đã mua trong bảng products
        $new_stock = $stockData['stock'] - $quantity;
        mysqli_query($conn, "UPDATE products SET stock = $new_stock WHERE id = $product_id");
        
        echo "success";
    } else {
        echo "Lỗi khi tạo đơn hàng: " . mysqli_error($conn);
    }
} else {
    echo "Yêu cầu không hợp lệ.";
}
?>