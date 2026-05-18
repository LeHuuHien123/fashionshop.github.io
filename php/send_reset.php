<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
include 'datk.php'; // Kết nối CSDL

// 1. Nhúng trực tiếp từ thư mục PHPMailer-master (Đã đúng cấu trúc của bạn)

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// XÓA DÒNG require 'vendor/autoload.php' VÌ BẠN KHÔNG DÙNG COMPOSER

if (isset($_POST['request_reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Kiểm tra email có tồn tại không
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($check) > 0) {
        
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Lưu vào DB
        $update = mysqli_query($conn, "UPDATE users SET reset_token='$token', token_expiry='$expiry' WHERE email='$email'");

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        if ($update) {
           // Tự động lấy domain (localhost hoặc link ngrok)
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST']; 
            $resetLink = "$protocol://$domain/wbqa/reset_password.php?email=$email&token=$token";
            
            $mail = new PHPMailer(true);

            try {
                // Cấu hình Server
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'lehuuhien678@gmail.com'; // Email gửi
                $mail->Password   = 'gldx cudt dsjm odlu';      // Mật khẩu ứng dụng 16 ký tự
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // Người gửi & Người nhận
                $mail->setFrom('lehuuhien678@gmail.com', 'Hệ thống hỗ trợ DATK');
                $mail->addAddress($email); 

                // Nội dung email
                $mail->isHTML(true);
                $mail->Subject = 'Đặt lại mật khẩu của bạn';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; border: 1px solid #c5a059; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #c5a059;'>Yêu cầu đặt lại mật khẩu - HUNO SHOP</h2>
                        <p>Chào bạn,</p>
                        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản liên kết với email này.</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='$resetLink' style='background: #1a1a1a; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;'>ĐẶT LẠI MẬT KHẨU</a>
                        </p>
                        <p>Nếu link trên không hoạt động, bạn có thể copy link này: <br> $resetLink</p>
                        <hr>
                        <p style='font-size: 0.8rem; color: #888;'>Nếu bạn không yêu cầu thay đổi này, vui lòng bỏ qua email này.</p>
                    </div>
                ";

                $mail->send();
                echo "<script>alert('Link đặt lại mật khẩu đã được gửi vào email của bạn!'); window.location.href='../login.html';</script>";
            } catch (Exception $e) {
                echo "Lỗi khi gửi mail: {$mail->ErrorInfo}";
            }

        } else {
            echo "<script>alert('Lỗi hệ thống khi lưu token!'); history.back();</script>";
        }
    } else {
        echo "<script>alert('Email này chưa được đăng ký!'); history.back();</script>";
    }
}
?>