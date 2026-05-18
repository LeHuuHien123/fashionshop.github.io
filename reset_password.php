<?php
// 1. Hiển thị lỗi để không bị trang trắng
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// 3. Kết nối CSDL
include 'php/datk.php'; 

$email = '';
$token = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    $currentTime = date("Y-m-d H:i:s");

    // Truy vấn kiểm tra
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND reset_token='$token' AND token_expiry > '$currentTime'");

    if (mysqli_num_rows($query) > 0) {
        // --- HIỂN THỊ FORM NẾU HỢP LỆ ---
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>Đặt lại mật khẩu</title>
            <link rel="stylesheet" href="css/login.css">
            <script>
                function validatePassword() {
                    var pass = document.getElementById("new_password").value;
                    var confirmPass = document.getElementById("confirm_password").value;
                    var errorMsg = document.getElementById("error_msg");

                    if (pass !== confirmPass) {
                        errorMsg.innerHTML = "Mật khẩu nhập lại không khớp!";
                        return false; // Ngăn không cho gửi form
                    }
                    return true; // Cho phép gửi form
                }
            </script>
        </head>
        <body>
            <div class="container">
                <form action="" method="POST" onsubmit="return validatePassword()">
                    <h1>Mật khẩu mới</h1>
                    <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                    
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <button type="submit" name="update_password">Cập nhật mật khẩu</button>
                    
                    <p id="error_msg" style="color: red; font-size: 14px; margin-top: 10px;"></p>
                </form>
            </div>
        </body>
        </html>
        <?php
    } else {
        // --- PHẦN DEBUG NẾU KHÔNG HỢP LỆ ---
        $res = mysqli_query($conn, "SELECT reset_token, token_expiry FROM users WHERE email='$email'");
        $row = mysqli_fetch_assoc($res);
        
        echo "<h3>Phân tích lỗi:</h3>";
        echo "1. Token bạn gửi lên: <b>$token</b><br>";
        echo "2. Token trong CSDL: <b>" . ($row['reset_token'] ?? 'Không thấy email này') . "</b><br>";
        echo "3. Hạn dùng trong CSDL: <b>" . ($row['token_expiry'] ?? 'N/A') . "</b><br>";
        echo "4. Giờ hệ thống hiện tại: <b>" . $currentTime . "</b><br>";
        
        if (!$row) {
            echo "<span style='color:red;'>=> LỖI: Email không tồn tại trong hệ thống!</span>";
        } elseif ($token !== $row['reset_token']) {
            echo "<span style='color:red;'>=> LỖI: Token trên link không khớp với mã bí mật trong máy!</span>";
        } elseif (strtotime($row['token_expiry']) <= time()) {
            echo "<span style='color:red;'>=> LỖI: Link này đã hết hạn rồi!</span>";
        }
    }
} else {
    echo "Hành động không hợp lệ. Bạn cần nhấn vào link trong email.";
}

// 4. Xử lý khi nhấn nút cập nhật (POST)
if (isset($_POST['update_password'])) {
    $email_post = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    
    $update = mysqli_query($conn, "UPDATE users SET password='$new_pass', reset_token=NULL, token_expiry=NULL WHERE email='$email_post'");

    if ($update) {
        echo "<script>alert('Đổi mật khẩu thành công!'); window.location.href='login.html';</script>";
    } else {
        echo "Lỗi cập nhật: " . mysqli_error($conn);
    }
}
?>