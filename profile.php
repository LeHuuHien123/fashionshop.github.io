<?php 
session_start();
include 'php/datk.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. TRUY VẤN MỚI: Chỉ lấy thông tin từ bảng users, bỏ JOIN với game_scores
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$res = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);

// 2. GÁN GIÁ TRỊ BIẾN (Sửa lỗi Undefined variable bạn gặp lúc trước)
$fullname = $user['fullname'] ?? 'Thành viên';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';
$avatar = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.png';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân</title>
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <style>
    /* =============================================
       PROFILE PAGE - EARTH TONE STYLE
    ============================================= */
    :root {
        --admin-bg: #fdfaf9;
        --primary: #8d6e63;
        --primary-dark: #3e2723;
        --accent: #e67e22;
        --text-main: #3e2723;
        --shadow: 0 8px 30px rgba(78, 52, 46, 0.08);
    }

    body { 
        font-family: 'Inter', sans-serif; 
        background: var(--admin-bg); 
        margin: 0; 
        padding: 0; 
        color: var(--text-main);
    }

    /* Container chính phong cách hiện đại */
    .container { 
        max-width: 900px; 
        margin: 120px auto 60px auto; 
        background: white; 
        padding: 40px; 
        border-radius: 20px; 
        box-shadow: var(--shadow); 
        display: flex; 
        gap: 40px; 
        border: 1px solid #efebe9;
        position: relative;
        overflow: hidden;
    }

    /* Điểm nhấn đường kẻ nâu trên đầu container */
    .container::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 6px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    /* Sidebar (Avatar & Name) */
    .sidebar { 
        flex: 1; 
        text-align: center; 
        border-right: 1px solid #f5f0ee; 
        padding-right: 40px; 
    }

    .sidebar img { 
        width: 160px; 
        height: 160px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 4px solid white; 
        box-shadow: 0 5px 15px rgba(141, 110, 99, 0.2);
        margin-bottom: 20px; 
        transition: 0.3s;
    }
    
    .sidebar img:hover { transform: scale(1.05); }

    .sidebar h3 { 
        font-family: 'Playfair Display', serif; 
        font-size: 1.5rem; 
        color: var(--primary-dark);
        margin-bottom: 5px;
    }

    /* Content (Form chỉnh sửa) */
    .content { flex: 2; }
    .content h2 { 
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        margin-bottom: 30px;
        color: var(--primary-dark);
    }

    /* Cấu trúc Form group đồng bộ Admin */
    .form-group { margin-bottom: 22px; }

    .form-group label { 
        display: block; 
        font-weight: 700; 
        margin-bottom: 8px; 
        color: var(--primary); 
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group input { 
        width: 100%; 
        padding: 14px 16px; 
        border: 1.5px solid #efebe9; 
        border-radius: 12px; 
        background: #fdfaf9;
        font-size: 15px; 
        transition: all 0.2s;
        color: var(--primary-dark);
    }

    .form-group input:focus { 
        border-color: var(--primary); 
        background: white;
        box-shadow: 0 0 0 4px rgba(141, 110, 99, 0.1); 
        outline: none; 
    }

    /* Nút bấm Cam nổi bật */
    .btn-save { 
        background: var(--accent); 
        color: white; 
        border: none; 
        padding: 16px; 
        width: 100%; 
        border-radius: 12px; 
        cursor: pointer; 
        font-weight: 700; 
        font-size: 16px; 
        letter-spacing: 1px;
        transition: 0.3s; 
        box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);
        margin-top: 10px;
    }

    .btn-save:hover { 
        background: #d35400; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(230, 126, 34, 0.3);
    }

    /* Navbar Glassmorphism */
    .navbar { 
        backdrop-filter: blur(10px); 
        background: rgba(255, 255, 255, 0.85) !important; 
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .container { flex-direction: column; margin: 80px 15px 30px 15px; padding: 25px; }
        .sidebar { border-right: none; border-bottom: 1px solid #eee; padding-right: 0; padding-bottom: 25px; margin-bottom: 25px; }
    }
</style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">
                <img src="img/logo/1.png" alt="Fashion Shop" style="height: 50px; width: auto; object-fit: contain;">
            </a>

            <ul class="nav-links">
                <li><a href="index.php">Trang Chủ</a></li>
                <li><a href="shop.php">Cửa Hàng</a></li>
                <li><a href="index.php#new-arrivals">Hàng Mới</a></li>
                <li><a href="evaluate.php">Đánh giá</a></li>
                <li><a href="about.html">Về Chúng Tôi</a></li>
            </ul>

            <div class="nav-btns">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="user-dropdown">
                        <div class="dropdown-toggle">
                            <small class="welcome-text">Chào,</small>
                            <span class="user-name-display"><?php echo $_SESSION['fullname']; ?></span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i>
                        </div>
                        <ul class="dropdown-menu">
                            <li><a href="profile.php"><i class="fa-solid fa-user"></i> Hồ sơ cá nhân</a></li>
                            <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng</a></li>
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <li><a href="admin.php"><i class="fa-solid fa-user-shield"></i> Trang quản trị</a></li>
                            <?php endif; ?>
                            <hr style="border: 0.5px solid #eee; margin: 5px 0;">
                            <li><a href="php/logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.html" class="btn-login">Đăng nhập</a>
                <?php endif; ?>
                <a href="shop.php" class="btn-main"><i class="fa-solid fa-cart-shopping"></i> Mua Ngay</a>
            </div>
        </div>
    </nav> 
    <div class="container">
        <div class="sidebar">
            <div style="position: relative; display: inline-block;">
                <img src="img/avt/<?php echo htmlspecialchars($avatar); ?>" 
                    alt="Avatar" id="currentAvatar" 
                    onerror="this.src='img/default-avatar.png'">
                
                <label for="uploadAvt" style="position: absolute; bottom: 15px; right: 5px; background: var(--accent); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <i class="fa-solid fa-camera"></i>
                </label>
                <input type="file" id="uploadAvt" hidden accept="image/*" onchange="handleAvatarUpload(this)">
            </div>
            <h3><?php echo htmlspecialchars($fullname); ?></h3>
            <p style="color: #888; font-size: 0.8rem;">Đổi ảnh đại diện (21 ngày/lần)</p>
        </div>

        <div class="content">
            <h2 style="margin-top: 0;">Chỉnh sửa thông tin</h2>
            <form id="profileForm">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Chưa cập nhật số điện thoại">
                </div>
                <div class="form-group">
                    <label>Địa chỉ nhận hàng</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" placeholder="Nhập địa chỉ của bạn">
                </div>
                <div class="form-group">
                    <label>Mật khẩu mới (để trống nếu không đổi)</label>
                    <input type="password" name="password" placeholder="********">
                </div>
                <button type="submit" class="btn-save">CẬP NHẬT HỒ SƠ</button>
            </form>

            <hr style="margin: 40px 0; border: 0; border-top: 1px solid #efebe9;">
            <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Sản phẩm yêu thích</h2>

<?php
// Truy vấn lấy danh sách sản phẩm yêu thích
$sql_fav = "SELECT p.* FROM products p 
            JOIN favorites f ON p.id = f.product_id 
            WHERE f.user_id = '$user_id'";
$res_fav = mysqli_query($conn, $sql_fav);

if (mysqli_num_rows($res_fav) > 0): ?>
    <div class="fav-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px;">
        <?php while($f = mysqli_fetch_assoc($res_fav)): 
            // 1. Tách chuỗi ảnh (vì database lưu nhiều ảnh ngăn cách bằng dấu phẩy)
            $img_list = explode(',', $f['image']);
            $first_img = trim($img_list[0]); // Lấy ảnh đầu tiên
            
            // 2. Xử lý đường dẫn ảnh chuẩn xác
            // Nếu là link tuyệt đối (http...) thì giữ nguyên, nếu không thì trỏ vào đúng thư mục
            if (filter_var($first_img, FILTER_VALIDATE_URL)) {
                $image_path = $first_img;
            } else {
                // Kiểm tra xem trong DB đã có sẵn 'img/products/' chưa
                if (strpos($first_img, 'img/products/') !== false) {
                    $image_path = $first_img;
                } else {
                    $image_path = 'img/products/' . $first_img;
                }
            }
        ?>
            <div class="fav-item" style="text-align: center; background: #fff; padding: 12px; border-radius: 12px; border: 1px solid #efebe9; transition: 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="width: 100%; height: 160px; overflow: hidden; border-radius: 8px; margin-bottom: 10px; background: #fdfaf9;">
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($f['product_name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;" 
                         onerror="this.src='img/default-product.png'"> 
                </div>

                <p style="font-size: 14px; font-weight: 600; color: #3e2723; margin: 5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?php echo htmlspecialchars($f['product_name']); ?>
                </p>
                
                <p style="color: #e67e22; font-weight: bold; font-size: 14px; margin-bottom: 10px;">
                    <?php echo number_format($f['price'], 0, ',', '.'); ?>đ
                </p>

                <a href="shop_info.php?id=<?php echo $f['id']; ?>" 
                   style="color: #8d6e63; font-size: 11px; text-decoration: none; font-weight: 700; text-transform: uppercase; display: block; padding: 7px; border: 1px solid #8d6e63; border-radius: 6px; transition: 0.2s;">
                   Xem chi tiết
                </a>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p style="color: #999; font-style: italic; font-size: 14px;">Bạn chưa có sản phẩm yêu thích nào.</p>
<?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('profileForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this); // Gửi trực tiếp FormData

            try {
                const res = await fetch('php/update_user.php', {
                    method: 'POST',
                    body: formData // Bỏ headers Content-Type và JSON.stringify
                });
                const result = await res.json();
                if(result.success) {
                    alert("Cập nhật thành công!");
                    location.reload();
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (err) {
                alert("Lỗi máy chủ! Kiểm tra file php/update_profile.php");
            }
        };
        async function handleAvatarUpload(input) {
            if (!input.files || !input.files[0]) return;

            const formData = new FormData();
            formData.append('avatar', input.files[0]); // Gửi đúng 1 file

            try {
                const response = await fetch('php/update_avatar.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.success) {
                    alert("✨ Cập nhật ảnh đại diện thành công!");
                    location.reload(); // Load lại để thấy ảnh mới
                } else {
                    alert("⚠️ " + res.message); // Hiển thị lỗi hoặc thông báo đợi 21 ngày
                    input.value = ""; // Reset input
                }
            } catch (error) {
                alert("Lỗi kết nối máy chủ!");
            }
        }
    </script>
</body>
</html>