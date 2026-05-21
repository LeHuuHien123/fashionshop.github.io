<?php
error_reporting(0);
include 'datk.php';
header('Content-Type: application/json');
// SQL kiểm tra: status trống (NULL) hoặc là waiting_confirm
// SQL trong php/get_new_orders.php
$query = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status IS NULL OR status = '' OR status = 'waiting_confirm'");
$row = mysqli_fetch_assoc($query);
echo json_encode(['count' => (int)$row['count']]);
?>