<?php
include 'datk.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $rating = intval($_POST['rating']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    // Xử lý mảng targets (checkbox) thành chuỗi
    $targets = isset($_POST['targets']) ? implode(', ', $_POST['targets']) : '';
    $targets = mysqli_real_escape_string($conn, $targets);

    $sql = "INSERT INTO evaluates (username, rating, target_items, content) 
            VALUES ('$username', '$rating', '$targets', '$content')";

    if (mysqli_query($conn, $sql)) {
        echo "Gửi đánh giá thành công!";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>