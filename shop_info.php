<?php 
session_start();
include 'php/datk.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM products WHERE id = $id";
$res = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($res);

if (!$product) die("Sản phẩm không tồn tại!");

// BÓC TÁCH SIZE: Chuỗi dạng "S:10,M:5" -> Mảng
$size_data = [];
if (!empty($product['size'])) {
    $parts = explode(',', $product['size']);
    foreach ($parts as $p) {
        $item = explode(':', $p);
        if (count($item) == 2) {
            $size_data[trim($item[0])] = intval($item[1]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $product['product_name'] ?> - Fashion Shop</title>
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
/* =========================================
   CHI TIẾT SẢN PHẨM - EARTH TONE LUXURY
========================================= */

/* 1. Container chính - Tông màu thanh lịch */
.container {
    max-width: 1200px;
    margin: 140px auto 60px;
    display: flex;
    gap: 60px;
    background: #fff;
    padding: 50px;
    border-radius: 12px; /* Bo góc nhẹ nhàng hơn kiểu Luxury cũ */
    box-shadow: 0 10px 40px rgba(141, 110, 99, 0.08); /* Đổ bóng tông nâu cực nhạt */
    border: 1px solid #efebe9;
}

/* 2. Bên trái: Hình ảnh sản phẩm */
.image-side {
    width: 550px;
    flex-shrink: 0;
}

.image-side img {
    width: 100%;
    height: 650px; /* Tăng chiều cao để ảnh tôn dáng hơn */
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(78, 52, 46, 0.1);
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.image-side img:hover {
    transform: scale(1.01);
}

/* 3. Bên phải: Thông tin sản phẩm */
.info-side {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.info-side h1 {
    font-family: 'Playfair Display', serif !important;
    font-size: 36px;
    color: #4e342e; /* Nâu đen trầm */
    margin-bottom: 15px;
    line-height: 1.3;
}

.price-tag {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: #8d6e63; /* Màu Nâu chủ đạo */
    font-weight: 700;
    margin-bottom: 30px;
}

/* 4. Chọn Size - Tinh tế và Mộc mạc */
.size-box h3 {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #a1887f;
    margin-bottom: 20px;
}

.size-item { 
    padding: 12px 25px; 
    border: 1px solid #d7ccc8; /* Viền nâu nhạt */
    border-radius: 4px; /* Bo góc đồng bộ với nút bấm */
    font-size: 14px;
    font-weight: 600;
    color: #5d4037;
    transition: all 0.3s ease;
}

.size-item:hover:not(.disabled) { 
    border-color: #8d6e63; 
    color: #8d6e63; 
}

.size-item.active { 
    background: #8d6e63; /* Nền nâu sáng nổi bật */
    color: #fff; 
    border-color: #8d6e63;
    box-shadow: 0 4px 12px rgba(141, 110, 99, 0.3);
}

/* 5. Nút Thêm Vào Giỏ (Chủ đạo tông Nâu) */
.btn-add-cart { 
    width: 100%; 
    padding: 18px; 
    background: #5d4037; /* Nâu trầm sang trọng */
    color: #fff; 
    border-radius: 4px; 
    font-size: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 20px rgba(93, 64, 55, 0.2);
    transition: all 0.3s ease;
}

.btn-add-cart:hover { 
    background: #4e342e; 
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(78, 52, 46, 0.3);
}

/* 6. Danh sách ảnh nhỏ (Thumbnails) */
.thumbnail-list {
    margin-top: 25px;
    display: flex;
    gap: 15px;
    overflow-x: auto;
}

.thumb-item {
    width: 85px;
    height: 85px;
    border-radius: 4px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    object-fit: cover;
    opacity: 0.7;
}

.thumb-item:hover, .thumb-item.active-thumb {
    opacity: 1;
    border-color: #8d6e63;
    transform: translateY(-3px);
}

/* Responsive cho điện thoại */
@media (max-width: 992px) {
    .container {
        flex-direction: column;
        margin-top: 100px;
        padding: 20px;
        gap: 30px;
    }
    .image-side {
        width: 100%;
    }
    .image-side img {
        height: 450px;
    }
}
/* Toast Notification Style */
.toast-notification {
    position: fixed;
    top: 100px; /* Hiển thị dưới Navbar một chút */
    right: 20px;
    background-color: #5d4037; /* Tông nâu đồng bộ với trang chi tiết */
    color: white;
    padding: 16px 28px;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    z-index: 10001;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    font-size: 15px;
    animation: slideInToast 0.5s ease-out, fadeOutToast 0.5s ease-in 2.5s forwards;
}

@keyframes slideInToast {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOutToast {
    from { opacity: 1; }
    to { opacity: 0; visibility: hidden; }
}

/* Nút đi tới giỏ hàng nhanh trên bong bóng */
.toast-link {
    color: #fff;
    text-decoration: underline;
    margin-left: 10px;
    font-weight: 700;
}
/* Khung chứa bao quanh toàn bộ phần gợi ý */
.container-box {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Khung riêng cho danh sách sản phẩm để dùng Flexbox */
.suggested-list-flex {
    display: flex;
    flex-direction: row; /* Ép nằm ngang */
    flex-wrap: wrap;     /* Tự xuống hàng nếu màn hình nhỏ */
    gap: 25px;           /* Khoảng cách giữa các card */
    justify-content: center; /* Căn giữa các card */
    width: 100%;
}

/* Style cho từng thẻ sản phẩm gợi ý */
.suggest-item {
    flex: 0 1 240px; /* Độ rộng cố định khoảng 240px */
    background: #fff;
    border: 1px solid #eee;
    padding: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    box-sizing: border-box;
}

.suggest-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
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
        <div class="image-side">
            <?php 
                $all_images = explode(',', $product['image']); 
                $main_image = trim($all_images[0]);
            ?>
            <img id="mainProductImage" src="<?= $main_image ?>" alt="Product Image">

            <div class="thumbnail-list" style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto;">
                <?php foreach($all_images as $img): $img = trim($img); ?>
                    <img src="<?= $img ?>" 
                        onclick="changeImage('<?= $img ?>', this)" 
                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent;" 
                        class="thumb-item">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-side">
            <span style="color: #95a5a6; text-transform: uppercase; letter-spacing: 1px;"><?= $product['category'] ?></span>
            <h1><?= htmlspecialchars($product['product_name']) ?></h1>
            <div class="price-tag"><?= number_format($product['price']) ?>đ</div>

            <div class="size-box">
                <strong>CHỌN KÍCH CỠ:</strong>
                <div class="size-list">
                    <?php foreach($size_data as $name => $stock): ?>
                        <button class="size-item <?= ($stock <= 0) ? 'disabled' : '' ?>" 
                                onclick="setSelection('<?= $name ?>', <?= $stock ?>, this)"
                                <?= ($stock <= 0) ? 'disabled' : '' ?>>
                            <?= $name ?>
                            <div style="font-size: 10px; font-weight: 400; opacity: 0.8;">Kho: <?= $stock ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <strong>MÔ TẢ SẢN PHẨM:</strong>
                <p style="color: #666; margin-top: 10px; line-height: 1.8;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <input type="hidden" id="finalSize" value="">
            <button class="btn-add-cart" onclick="submitOrder(<?= $product['id'] ?>)">
                <i class="fa-solid fa-cart-shopping"></i> THÊM VÀO GIỎ HÀNG
            </button>
        </div>
        
    </div>
    <section class="suggested-section" style="background-color: #faf9f8; padding: 50px 0; border-top: 1px solid #efebe9;">
    <div class="container-box">
        
        <div class="section-header" style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-family: 'Playfair Display', serif; color: #3e2723; font-size: 28px; margin: 0;">Có Thể Bạn Sẽ Thích</h2>
            <div style="width: 50px; height: 2px; background: #8d6e63; margin: 12px auto;"></div>
        </div>
        
        <?php
        // Lấy dữ liệu an toàn từ mảng $product
        $c_price = isset($product['price']) ? intval($product['price']) : 0;
        $c_cat   = isset($product['category']) ? mysqli_real_escape_string($conn, $product['category']) : '';
        $c_id    = isset($product['id']) ? intval($product['id']) : 0;

        $sql_suggest = "SELECT * FROM products 
                        WHERE category = '$c_cat' 
                        AND id != $c_id 
                        ORDER BY ABS(price - $c_price) ASC 
                        LIMIT 4";
        $res_suggest = mysqli_query($conn, $sql_suggest);

        if ($res_suggest && mysqli_num_rows($res_suggest) > 0): ?>
            
            <div class="suggested-list-flex">
                
                <?php while ($s = mysqli_fetch_assoc($res_suggest)): 
                    $img_list = explode(',', $s['image']);
                    $first_img = trim($img_list[0]);
                    $img_path = (filter_var($first_img, FILTER_VALIDATE_URL)) ? $first_img : 'img/products/' . $first_img;
                ?>
                    <div class="suggest-item">
                        <div style="width: 100%; height: 260px; overflow: hidden; margin-bottom: 15px; background: #fdfaf9;">
                            <img src="<?= $img_path ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 onerror="this.src='img/default-product.png'">
                        </div>
                        <h4 style="font-size: 15px; color: #3e2723; height: 40px; overflow: hidden; margin-bottom: 10px; font-weight: 600;">
                            <?= htmlspecialchars($s['product_name']) ?>
                        </h4>
                        <p style="color: #e67e22; font-weight: bold; font-size: 17px; margin-bottom: 15px;">
                            <?= number_format($s['price'], 0, ',', '.') ?>đ
                        </p>
                        <a href="shop_info.php?id=<?= $s['id'] ?>" 
                           style="display: block; padding: 10px; border: 1px solid #3e2723; color: #3e2723; text-decoration: none; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                           Xem chi tiết
                        </a>
                    </div>
                <?php endwhile; ?>
                
            </div>

        <?php else: ?>
            <p style="text-align: center; color: #999;">Đang cập nhật thêm sản phẩm...</p>
        <?php endif; ?>

    </div>
</section>
    <script>
        function setSelection(size, stock, btn) {
            document.querySelectorAll('.size-item').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('finalSize').value = size;
        }

        function submitOrder(prodId) {
    const size = document.getElementById('finalSize').value;
    
    // 1. Kiểm tra đã chọn size chưa
    if(!size) {
        alert("Vui lòng chọn kích cỡ (Size) trước khi thêm vào giỏ hàng!");
        return;
    }

    // 2. Kiểm tra đăng nhập (Lấy từ Session PHP)
    const userId = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
    if(!userId) {
        alert("Bạn cần đăng nhập để mua hàng!");
        window.location.href = "login.html";
        return;
    }

    // 3. Đóng gói dữ liệu gửi đi
    const fd = new FormData();
    fd.append('product_id', prodId);
    fd.append('size_selected', size); // Gửi thêm size khách chọn
    fd.append('quantity', 1); // Mặc định mỗi lần bấm là 1 cái

    // 4. Gửi yêu cầu đến file xử lý
    fetch('php/add_to_cart_detailed.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            if(confirm("🎉 Đã thêm vào giỏ hàng thành công! Bạn có muốn đi đến giỏ hàng ngay không?")) {
                window.location.href = "cart.php";
            }
        } else {
            alert("Lỗi: " + data);
        }
    })
    .catch(err => alert("Lỗi kết nối: " + err));
}
function changeImage(src, thumb) {
    // Đổi nguồn ảnh chính
    document.getElementById('mainProductImage').src = src;

    // Xóa viền cam ở các ảnh nhỏ khác và thêm vào ảnh đang chọn
    document.querySelectorAll('.thumb-item').forEach(el => el.classList.remove('active-thumb'));
    thumb.classList.add('active-thumb');
}
// Hàm hiển thị bong bóng thông báo
function showCartToast(message, isSuccess = true) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    if (!isSuccess) toast.style.backgroundColor = '#e74c3c'; // Màu đỏ nếu lỗi

    toast.innerHTML = `
        <i class="fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i>
        <span>${message}</span>
        ${isSuccess ? '<a href="cart.php" class="toast-link">Xem giỏ hàng</a>' : ''}
    `;

    document.body.appendChild(toast);

    // Tự động xóa sau 3 giây
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function submitOrder(prodId) {
    const size = document.getElementById('finalSize').value;
    
    // 1. Kiểm tra đã chọn size chưa
    if(!size) {
        showCartToast("Vui lòng chọn kích cỡ trước!", false);
        return;
    }

    // 2. Kiểm tra đăng nhập
    const userId = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
    if(!userId) {
        showCartToast("Bạn cần đăng nhập để mua hàng!", false);
        setTimeout(() => window.location.href = "login.html", 1500);
        return;
    }

    // 3. Đóng gói dữ liệu
    const fd = new FormData();
    fd.append('product_id', prodId);
    fd.append('size_selected', size);
    fd.append('quantity', 1);

    // 4. Gửi yêu cầu
    fetch('php/add_to_cart_detailed.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            // HIỂN THỊ BONG BÓNG THAY VÌ ALERT/CONFIRM
            showCartToast("Đã thêm sản phẩm vào giỏ hàng!");
        } else {
            showCartToast("Lỗi: " + data, false);
        }
    })
    .catch(err => showCartToast("Lỗi kết nối máy chủ!", false));
}
// Mặc định chọn ảnh đầu tiên làm ảnh đang hoạt động
document.querySelector('.thumb-item').classList.add('active-thumb');
    </script>
    <footer id="footer" class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-column">
                <h3 class="footer-title">HUNO SHOP</h3>
                <p class="footer-desc">
                    Định hình phong cách thời trang của bạn với những thiết kế dẫn đầu xu hướng và chất lượng cao cấp.
                </p>
                <ul class="footer-contact">
                    <li><i class="fa-solid fa-location-dot"></i> 456 Lê Lợi, Phường Bến Thành, Quận 1, Thành phố Hồ Chí Minh, Việt Nam.</li>
                    <li><i class="fa-solid fa-phone"></i> Chăm sóc khách hàng: 0246.2591551</li>
                    <li><i class="fa-solid fa-envelope"></i> Email: support@fashionshop.vn</li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="footer-title">VỀ CHÚNG TÔI</h3>
                <ul class="footer-links">
                    <li><a href="about.html">Giới thiệu</a></li>
                    <li><a href="#">Triết lý kinh doanh</a></li>
                    <li><a href="#">Tin tức Fashion Blog</a></li>
                    <li><a href="#">Hệ thống cửa hàng</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="footer-title">CHÍNH SÁCH</h3>
                <ul class="footer-links">
                    <li><a href="#">Chính sách vận chuyển</a></li>
                    <li><a href="#">Hướng dẫn thanh toán</a></li>
                    <li><a href="#">Quy định đổi trả</a></li>
                    <li><a href="#">Bảo hành & Sửa chữa</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="footer-title">THANH TOÁN</h3>
                <div class="payment-methods">
                    <div class="payment-item" data-title="Tiền mặt">
                        <img src="img/logo/2.png" alt="Tiền mặt">
                    </div>

                    <div class="payment-item" data-title="Chuyển khoản">
                        <img src="img/logo/3.png" alt="Banking">
                    </div>
                </div>
                <div class="bct-logo" style="margin-top: 15px;">
                    <a href="#"><img src="https://theme.hstatic.net/200000182297/1000887316/14/bct.png?v=3066" width="120" alt="Đã thông báo Bộ Công Thương"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; 2026 Huno Shop. Bản quyền thuộc về đội ngũ phát triển.</p>
        </div>
    </div>
</footer>
</body>
</html>