<?php
session_start();
include 'datk.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $order_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // 1. Lấy thông tin đơn hàng trước khi xóa để biết số lượng và sản phẩm nào
    $res = mysqli_query($conn, "SELECT product_id, quantity FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'pending'");
    $order = mysqli_fetch_assoc($res);

    if ($order) {
        $prod_id = $order['product_id'];
        $qty = $order['quantity'];

        // 2. Cộng trả lại số lượng vào kho
        mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id = $prod_id");

        // 3. Xóa đơn hàng
        if (mysqli_query($conn, "DELETE FROM orders WHERE id = $order_id")) {
            echo "success";
        } else {
            echo "Lỗi xóa đơn";
        }
    } else {
        echo "Không tìm thấy đơn hàng hoặc đơn đã được xử lý.";
    }
}
?>
