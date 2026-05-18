<?php
include 'datk.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $order_id = $_POST['order_id'];
    $new_qty = $_POST['new_qty'];

    // Lấy giá sản phẩm để tính lại tổng tiền
    $sql_price = "SELECT p.price FROM orders o JOIN products p ON o.product_id = p.id WHERE o.id = $order_id";
    $res = mysqli_query($conn, $sql_price);
    $product = mysqli_fetch_assoc($res);
    
    $new_total = $product['price'] * $new_qty;

    $sql_update = "UPDATE orders SET quantity = $new_qty, total_price = $new_total WHERE id = $order_id";
    
    if (mysqli_query($conn, $sql_update)) {
        echo json_encode(['status' => 'success', 'new_total' => $new_total]);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>