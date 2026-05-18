<?php
session_start();
include 'datk.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_ids'])) {

    $order_ids = json_decode($_POST['order_ids']);
    $user_id = $_SESSION['user_id'];

    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    if (empty($order_ids)) {
        die("Danh sách đơn hàng trống");
    }

    // chuyển mảng id thành chuỗi: 1,2,3
    $ids_string = implode(',', array_map('intval', $order_ids));

    $sql = "UPDATE orders 
            SET status='waiting_confirm',
                phone='$phone',
                address='$address',
                note='$note',
                payment_method='$payment_method',
                created_at = NOW()
            WHERE id IN ($ids_string) AND user_id = $user_id";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Lỗi truy vấn: " . mysqli_error($conn);
    }

} else {
    echo "Yêu cầu không hợp lệ";
}
?>