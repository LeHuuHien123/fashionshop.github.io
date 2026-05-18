<?php
// Hàm ghi lịch sử hoạt động ngầm vào Database
function logActivity($conn, $user_id, $action, $target) {
    $user_id = (int)$user_id;
    $action = mysqli_real_escape_string($conn, $action);
    $target = mysqli_real_escape_string($conn, $target);
    
    $query = "INSERT INTO activity_logs (user_id, action, target) VALUES ($user_id, '$action', '$target')";
    mysqli_query($conn, $query);
}
?>