<?php
session_start();
include 'php/datk.php';

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$products = [];

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $available_sizes = !empty($row['size']) ? explode(',', $row['size']) : [];
        $row['first_size'] = 'Hết hàng'; // Mặc định là hết hàng

        if (!empty($available_sizes)) {
            foreach($available_sizes as $item) {
                $parts = explode(':', trim($item));
                $s_name = trim($parts[0]);
                $s_qty = isset($parts[1]) ? intval($parts[1]) : 0;

                // Chỉ lấy size đầu tiên mà còn hàng (qty > 0)
                if($s_qty > 0) {
                    $row['first_size'] = $s_name;
                    break; 
                }
            }
        }
        // PHẢI CÓ DÒNG NÀY ĐỂ LƯU DỮ LIỆU VÀO MẢNG
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Fashion Shop - Cửa Hàng</title>
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="css/booking.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    /* --- TOAST THÔNG BÁO (EARTH TONE STYLE) --- */
    .cart-toast {
        position: fixed; 
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: #5d4037; /* Nâu đậm trầm mặc */
        color: #fff; /* Chữ trắng tinh khiết */
        border: 1px solid #8d6e63;
        padding: 12px 30px;
        border-radius: 4px; /* Bo nhẹ đồng bộ với nút Mua Ngay */
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 10px 30px rgba(78, 52, 46, 0.2);
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: 0.5px;
        animation: fadeInDown 0.5s cubic-bezier(0.23, 1, 0.32, 1) forwards;
    }

    /* Icon check mộc mạc */
    .cart-toast::before {
        content: "\f00c"; /* FontAwesome check icon nếu bạn có dùng FA */
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        color: #d7ccc8; /* Màu nâu nhạt cực nhẹ */
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translate(-50%, -30px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }

    /* --- NÚT THÊM GIỎ HÀNG (MINIMALIST BROWN) --- */
    .btn-add-cart {
        background: #8d6e63; /* Màu Nâu chủ đạo */
        color: #fff;
        border: none;
        padding: 12px 25px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        border-radius: 4px; /* Bo nhẹ sang trọng */
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-block;
    }

    .btn-add-cart:hover {
        background: #5d4037; /* Nâu đậm hơn khi hover */
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(93, 64, 55, 0.25);
    }

    .btn-add-cart:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(93, 64, 55, 0.2);
    }

    /* --- GIÁ TIỀN CLASSIC --- */
    .price {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: #5d4037; /* Nâu đậm cho giá tiền rõ ràng */
        letter-spacing: 0.5px;
    }

    .price::after {
        content: "đ";
        font-size: 1rem;
        margin-left: 3px;
        font-weight: 400;
        color: #8d6e63; /* Đơn vị tiền tệ màu nhạt hơn chút */
    }
    .wishlist-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 10;
    transition: 0.3s;
}

.wishlist-btn i {
    color: #ccc;
    font-size: 18px;
    transition: 0.3s;
}

.wishlist-btn.active i {
    color: #e74c3c; /* Màu đỏ khi đã tim */
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
<?php include 'flash_sale_popup.php'; ?>

    <div class="booking-page">
        <aside class="filter-sidebar">
            <h3>Lọc Sản Phẩm</h3>
            <div class="filter-group">
                <label>Tên sản phẩm:</label>
                <input type="text" id="searchName" placeholder="Tìm tên..." style="width:100%; padding:8px; margin-bottom:15px;">
            </div>
            <div class="filter-group">
                <label>Danh mục:</label>
                <select id="filterCategory" style="width:100%; padding:8px; margin-bottom:15px;">
                    <option value="all">Tất cả</option>
                    <option value="Áo">Áo</option>
                    <option value="Quần">Quần</option>
                    <option value="Áo Khoác">Áo Khoác</option>
                    <option value="Phụ Kiện">Phụ Kiện</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Giá tối đa: <b id="priceVal">5.000.000đ</b></label>
                <input type="range" id="filterPrice" min="0" max="5000000" step="100000" value="5000000" style="width:100%;">
            </div>
            <button onclick="resetFilter()" style="width:100%; padding:10px; margin-top:15px; background:#95a5a6; color:white; border:none; cursor:pointer;">Xóa bộ lọc</button>
        </aside>

        <main class="booking-content">
            <div class="grid" id="productContainer">
                <?php foreach($products as $p): 
                    $imgs = explode(',', $p['image']);
                ?>
                <div class="card product-card" 
                    data-name="<?= strtolower($p['product_name']) ?>" 
                    data-category="<?= $p['category'] ?>" 
                    data-price="<?= $p['price'] ?>">
                    
                    <a href="shop_info.php?id=<?= $p['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="image">
                            <img src="<?= trim($imgs[0]) ?>" alt="<?= $p['product_name'] ?>">
                        </div>
                        <div class="info" style="padding: 15px 15px 0 15px;">
                            <h3><?= $p['product_name'] ?></h3>
                            <p>Kho: <?= $p['stock'] ?></p>
                        </div>
                    </a>

                    <div class="card-footer">
                        <span class="price"><?= number_format($p['price']) ?>đ</span>
                        
                        <?php if($p['first_size'] !== 'Hết hàng'): ?>
                            <button class="wishlist-btn" onclick="toggleWishlist(<?= $p['id'] ?>, this)">
                                <i class="fa-solid fa-heart"></i>
                            </button>
                            <button class="btn-add-cart" 
                                    onclick="quickAddToCart(<?= $p['id'] ?>, '<?= $p['first_size'] ?>', this)">
                                <i class="fa-solid fa-cart-plus"></i> THÊM GIỎ HÀNG (<?= $p['first_size'] ?>)
                            </button>
                        <?php else: ?>
                            <button class="btn-add-cart" style="background: #ccc;" disabled>
                                TẠM HẾT HÀNG
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        // --- LOGIC BỘ LỌC ---
        const sName = document.getElementById('searchName');
        const sCat = document.getElementById('filterCategory');
        const sPrice = document.getElementById('filterPrice');
        const pVal = document.getElementById('priceVal');
        const cards = document.querySelectorAll('.product-card');

        function applyFilter() {
            const nameV = sName.value.toLowerCase();
            const catV = sCat.value;
            const priceV = parseInt(sPrice.value);
            pVal.innerText = new Intl.NumberFormat('vi-VN').format(priceV) + 'đ';

            cards.forEach(c => {
                const name = c.getAttribute('data-name');
                const cat = c.getAttribute('data-category');
                const price = parseInt(c.getAttribute('data-price'));

                const match = name.includes(nameV) && (catV === 'all' || cat === catV) && price <= priceV;
                c.style.display = match ? 'block' : 'none';
            });
        }

        sName.addEventListener('input', applyFilter);
        sCat.addEventListener('change', applyFilter);
        sPrice.addEventListener('input', applyFilter);

        function resetFilter() {
            sName.value = ''; sCat.value = 'all'; sPrice.value = 5000000;
            applyFilter();
        }

        // --- LOGIC GIỎ HÀNG & THÔNG BÁO ---
        function quickAddToCart(prodId, size, btn) {
            const fd = new FormData();
            fd.append('product_id', prodId);
            fd.append('size_selected', size);
            fd.append('quantity', 1);

            fetch('php/add_to_cart_detailed.php', { method: 'POST', body: fd })
            .then(r => r.text())
            .then(res => {
                if(res.trim() === 'success') {
                    const card = btn.closest('.product-card');
                    // Xóa toast cũ nếu đang hiện
                    const old = card.querySelector('.cart-toast');
                    if(old) old.remove();

                    // Tạo toast mới
                    const toast = document.createElement('div');
                    toast.className = 'cart-toast';
                    toast.innerHTML = `<i class="fa-solid fa-check"></i> Đã thêm Size ${size}`;
                    card.appendChild(toast);

                    setTimeout(() => toast.remove(), 2500);
                } else {
                    alert("Lỗi: " + res);
                }
            })
            .catch(e => alert("Lỗi kết nối server!"));
        }

        function toggleWishlist(prodId, btn) {
            fetch('php/toggle_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${prodId}`
            })
            .then(res => {
                // Nếu server trả về lỗi 500 hoặc 404, in ra text để debug
                if (!res.ok) return res.text().then(text => { throw new Error(text) });
                return res.json();
            })
            .then(data => {
                if (data.status === 'added') {
                    btn.classList.add('active');
                } else if (data.status === 'removed') {
                    btn.classList.remove('active');
                } else if (data.status === 'not_logged_in') {
                    alert("Vui lòng đăng nhập để yêu thích sản phẩm!");
                }
            })
            .catch(err => {
                console.error("Lỗi server trả về:", err);
                alert("Có lỗi xảy ra, kiểm tra Console log!");
            });
        }
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