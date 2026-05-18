<?php 
include 'php/datk.php'; 
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="google-site-verification" content="IL_OV6UoTjxKFKQrEwhfxuhaRe9H8PLOS4ewqq0o2vc" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Shop - Nâng Tầm Phong Cách Của Bạn</title>
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    <style>.product-welcome-effect {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    pointer-events: none;
    z-index: 10000;
    overflow: hidden;
}

.flying-shirt {
    position: absolute;
    width: 400px; /* Kích thước áo */
    opacity: 0;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
}

/* Áo bên trái */
.flying-shirt.left {
    bottom: -300px;
    left: -300px;
    animation: shirtFlowLeft 3.5s ease-in-out forwards;
}

/* Áo bên phải */
.flying-shirt.right {
    bottom: -300px;
    right: -300px;
    animation: shirtFlowRight 3.5s ease-in-out forwards;
}

/* Hiệu ứng lắc lư (Wiggle) */
@keyframes wiggle {
    0%, 100% { transform: rotate(-5deg); }
    50% { transform: rotate(5deg); }
}

@keyframes shirtFlowLeft {
    0% {
        bottom: -300px;
        left: -300px;
        opacity: 0;
        transform: scale(0.5) rotate(-20deg);
    }
    25% {
        bottom: 15%;
        left: 10%;
        opacity: 1;
        transform: scale(1.1) rotate(0deg);
    }
    /* Giai đoạn lắc lắc */
    40%, 55%, 70% {
        bottom: 15%;
        left: 10%;
        transform: rotate(-10deg);
    }
    47%, 62% {
        bottom: 15%;
        left: 10%;
        transform: rotate(10deg);
    }
    /* Ẩn vô */
    100% {
        bottom: 20%;
        left: 50%;
        transform: scale(0) rotate(360deg);
        opacity: 0;
    }
}

@keyframes shirtFlowRight {
    0% {
        bottom: -300px;
        right: -300px;
        opacity: 0;
        transform: scale(0.5) rotate(20deg);
    }
    25% {
        bottom: 15%;
        right: 10%;
        opacity: 1;
        transform: scale(1.1) rotate(0deg);
    }
    /* Giai đoạn lắc lắc */
    40%, 55%, 70% {
        bottom: 15%;
        right: 10%;
        transform: rotate(10deg);
    }
    47%, 62% {
        bottom: 15%;
        right: 10%;
        transform: rotate(-10deg);
    }
    /* Ẩn vô */
    100% {
        bottom: 20%;
        right: 50%;
        transform: scale(0) rotate(-360deg);
        opacity: 0;
    }
}</style>
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
                        <span class="user-name-display"><?php echo $_SESSION['user_name'] ?? $_SESSION['fullname'] ?? 'Thành viên'; ?></span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Hồ sơ cá nhân</a></li>
                        <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng</a></li>
                        <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff'): ?>
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
<div class="product-welcome-effect">
    <img src="img/banner/1.png" class="flying-shirt left" alt="Shirt Left">
    <img src="img/banner/1.png" class="flying-shirt right" alt="Shirt Right">
</div>
    <header class="hero">
        <div class="hero-content">
            <h1>Định Hình Phong Cách Thời Trang Của Bạn</h1>
            <p>Khám phá các bộ sưu tập quần áo mới nhất với thiết kế dẫn đầu xu hướng và chất lượng cao cấp.</p>
            <div class="hero-btns">
                <a href="shop.php" class="btn-primary">Xem Sản Phẩm</a>
                <a href="#new-arrivals" class="btn-outline">Bộ Sưu Tập Mới</a>
            </div>
        </div>
    </header>

    <section id="best-sellers" class="products-main">
        <div class="container">
            <div class="title-section" style="text-align: center; margin-bottom: 50px;">
                <span class="subtitle" style="color: #8d6e63; letter-spacing: 2px; text-transform: uppercase; font-size: 0.8rem; font-weight: 600;">Xu hướng</span>
                <h2 style="font-family: 'Playfair Display', serif; color: #5d4037; font-size: 2.5rem; margin-top: 10px;">Sản Phẩm Bán Chạy Nhất</h2>
            </div>

            <div class="menu-grid">
                <?php
                // JOIN bảng products và orders để đếm số lượt mua thực tế
                $sql_prods = "SELECT p.*, COUNT(o.id) as total_orders 
                            FROM products p 
                            LEFT JOIN orders o ON p.id = o.product_id 
                            GROUP BY p.id 
                            ORDER BY total_orders DESC 
                            LIMIT 3";
                $res_prods = mysqli_query($conn, $sql_prods);

                if($res_prods && mysqli_num_rows($res_prods) > 0) {
                    while($prod = mysqli_fetch_assoc($res_prods)) {
                        // Lấy ảnh đầu tiên nếu có nhiều link ảnh
                        $first_img = explode(',', $prod['image'])[0];
                ?>
                <div class="menu-card">
                    <div class="menu-gallery">
                        <div class="gallery-slider">
                            <img src="<?php echo trim($first_img); ?>" class="slide active" alt="Product">
                        </div>
                        <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(93, 64, 55, 0.8); color: white; padding: 4px 12px; border-radius: 4px; font-size: 11px; z-index: 3;">
                            Đã bán: <?php echo $prod['total_orders']; ?>
                        </div>
                    </div>
                    
                    <div class="menu-info">
                        <h3><?php echo $prod['product_name']; ?></h3>
                        <p style="color: #a1887f; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">
                            <?php echo $prod['category']; ?> | Size: <?php echo $prod['size']; ?>
                        </p>
                        <p class="menu-desc">
                            <?php echo (mb_strlen($prod['description']) > 80) ? mb_substr($prod['description'], 0, 80) . '...' : $prod['description']; ?>
                        </p>
                        
                        <div class="menu-price-wrap">
                            <span class="menu-price"><?php echo number_format($prod['price'], 0, ',', '.'); ?>đ</span>
                            <a href="shop.php?id=<?php echo $prod['id']; ?>" class="btn-outline-gold">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<p style='text-align:center; grid-column: 1 / -1; color: #a1887f; padding: 40px;'>Chưa có dữ liệu sản phẩm.</p>";
                }
                ?>
            </div>
        </div>
    </section>
  
    <section id="new-arrivals" class="products-main" style="background-color: #fcfaf5; padding: 60px 0;">
                <div class="container"> <div class="title-section" style="text-align: center; margin-bottom: 50px;">
                        <span class="subtitle" style="color: #d4ac0d; text-transform: uppercase; letter-spacing: 2px;">Khám phá</span>
                        <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Bộ Sưu Tập Mới Về</h2>
                        <p style="color: #666; max-width: 600px; margin: 10px auto;">
                            Lướt để xem chi tiết các góc chụp của những mẫu thiết kế độc quyền mới nhất.
                        </p>
                    </div>
                    
                    <div class="menu-grid">
                <?php
                // THUẬT TOÁN PHÂN TRANG (PAGINATION)
                $limit = 3; 
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit; 

                // Đếm tổng sản phẩm
                $sql_count = "SELECT COUNT(id) as total FROM products";
                $res_count = mysqli_query($conn, $sql_count);
                $total_rows = mysqli_fetch_assoc($res_count)['total'] ?? 0;
                $total_pages = ceil($total_rows / $limit); 

                // Lấy sản phẩm mới nhất
                $sql_new = "SELECT * FROM products ORDER BY id DESC LIMIT $limit OFFSET $offset";
                $res_new = mysqli_query($conn, $sql_new);

                // CHỈ DÙNG 1 VÒNG LẶP DUY NHẤT
                if($res_new && mysqli_num_rows($res_new) > 0) {
                    while($item = mysqli_fetch_assoc($res_new)) {
                        $images = explode(',', $item['image']);
                ?>
                <div class="menu-card">
                    <div class="menu-gallery">
                        <div class="gallery-slider">
                            <?php 
                            foreach($images as $index => $img) {
                                $imgUrl = trim($img);
                                if(!empty($imgUrl)) {
                                    $activeClass = ($index == 0) ? 'active' : '';
                                    echo "<img src='{$imgUrl}' class='slide {$activeClass}' alt='".htmlspecialchars($item['product_name'])."'>";
                                }
                            }
                            ?>
                        </div>
                        <?php if(count($images) > 1): ?>
                        <div class="gallery-nav">
                            <button class="nav-btn" onclick="flipPage(this, -1)"><i class="fa-solid fa-chevron-left"></i></button>
                            <button class="nav-btn" onclick="flipPage(this, 1)"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-info">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <p class="menu-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="menu-price-wrap">
                            <span class="menu-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span>
                            <a href="shop.php" class="btn-outline-gold"><i class="fa-solid fa-bag-shopping"></i> Mua Ngay</a>
                        </div>
                    </div>
                </div>
                <?php 
                    } // Đóng vòng lặp while
                } else {
                    echo "<p style='text-align:center; width: 100%; color: #888;'>Hiện chưa có sản phẩm nào.</p>";
                }
                ?>
            </div> 
        </div>
</section>

    <section id="popular-services" class="products-main" style="background: #fff;">
        <div class="title-section">
            <span class="subtitle">Cam kết</span>
            <h2>Dịch Vụ Khách Hàng</h2>
        </div>
        <div class="grid">
            <div class="card" style="text-align: center; padding: 40px 20px; box-shadow: none; border: 1px solid #eee;">
                <i class="fa-solid fa-truck-fast" style="font-size: 3rem; color: #3498db; margin-bottom: 20px;"></i>
                <h3>Giao Hàng Hỏa Tốc</h3>
                <p style="color: #666; font-size: 14px;">Miễn phí vận chuyển cho đơn hàng từ 500.000đ trở lên trên toàn quốc.</p>
            </div>
            <div class="card" style="text-align: center; padding: 40px 20px; box-shadow: none; border: 1px solid #eee;">
                <i class="fa-solid fa-rotate-left" style="font-size: 3rem; color: #e67e22; margin-bottom: 20px;"></i>
                <h3>Đổi Trả 7 Ngày</h3>
                <p style="color: #666; font-size: 14px;">Hỗ trợ đổi size, đổi mẫu linh hoạt trong vòng 7 ngày kể từ khi nhận hàng.</p>
            </div>
            <div class="card" style="text-align: center; padding: 40px 20px; box-shadow: none; border: 1px solid #eee;">
                <i class="fa-solid fa-shield-halved" style="font-size: 3rem; color: #2ecc71; margin-bottom: 20px;"></i>
                <h3>Thanh Toán An Toàn</h3>
                <p style="color: #666; font-size: 14px;">Hỗ trợ thanh toán khi nhận hàng (COD) hoặc chuyển khoản ngân hàng bảo mật 100%.</p>
            </div>
        </div>
    </section>

    <section id="testimonials" class="products-main">
        <div class="title-section">
            <span class="subtitle">Phản hồi</span>
            <h2>Khách Hàng Nói Gì Về Chúng Tôi</h2>
        </div>
        <div class="testimonial-wrapper">
            <div class="testimonial-container">
                <?php
                    $sql_eval = "SELECT * FROM evaluates ORDER BY id DESC LIMIT 8";
                    $res_eval = mysqli_query($conn, $sql_eval);

                    if ($res_eval && mysqli_num_rows($res_eval) > 0) {
                        while($eval = mysqli_fetch_assoc($res_eval)) {
                    ?>
                        <div class="testimonial-card">
                            <div class="stars"><?php echo str_repeat('⭐', $eval['rating']); ?></div>
                            <p>"<?php echo htmlspecialchars($eval['content']); ?>"</p>
                            <div class="user-meta">
                                <strong><?php echo htmlspecialchars($eval['username']); ?></strong>
                                <span>Đã mua: <?php echo htmlspecialchars($eval['target_items']); ?></span>
                            </div>
                        </div>
                    <?php 
                        }
                    } else {
                        echo "<p style='text-align:center; width:100%'>Chưa có đánh giá nào.</p>";
                    }
                    ?>
            </div>
        </div>
    </section>

    <footer id="footer" class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-column">
                <h3 class="footer-title">HUNO SHOP</h3>
                <p class="footer-desc">
                    Định hình phong cách thời trang của bạn với những thiết kế dẫn đầu xu hướng và chất lượng cao cấp.
                </p>
                <ul class="footer-contact">
                    <li><i class="fa-solid fa-location-dot"></i> 126 Lê Lợi, Phường Bến Thành, Quận 13, Thành phố Hồ Chí Minh, Việt Nam.</li>
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
            Đây chỉ là web của 1 học sinh ko có lợi nhuận thật vui lòng liên hệ lehuuhien678@gmail.com để nhắc nhở ẩn web xin cảm ơn
            "Đây là WEBSITE THỰC TẬP - Không có giá trị giao dịch thực tế. Mọi thông tin sản phẩm và đơn hàng chỉ phục vụ mục đích học tập."
        </div>
    </div>
</footer>

<script>
    // Lưu session name vào localStorage
    <?php if(isset($_SESSION['fullname'])): ?>
        localStorage.setItem('username', '<?php echo $_SESSION['fullname']; ?>');
    <?php endif; ?>

    // --- HÀM XỬ LÝ LẬT TRANG ẢNH SẢN PHẨM ---
    let isFlipping = false;

    function performFlip(gallery, direction) {
        if(isFlipping) return;

        const slides = gallery.querySelectorAll('.slide');
        let currentIndex = 0;

        slides.forEach((slide, index) => {
            if(slide.classList.contains('active')){
                currentIndex = index;
            }
        });

        let nextIndex = currentIndex + direction;

        if(nextIndex >= slides.length) nextIndex = 0;
        if(nextIndex < 0) nextIndex = slides.length - 1;

        const currentSlide = slides[currentIndex];
        const nextSlide = slides[nextIndex];

        isFlipping = true;

        if (direction === 1) {
            nextSlide.style.transition = "none";
            nextSlide.style.transform = "rotateY(0deg)";
            nextSlide.style.zIndex = "4";

            currentSlide.style.transition = "none";
            currentSlide.style.transform = "rotateY(0deg)";
            currentSlide.style.zIndex = "5";

            void gallery.offsetWidth; 

            currentSlide.style.transition = "transform 0.8s ease";
            currentSlide.style.transform = "rotateY(-180deg)";
        } else {
            nextSlide.style.transition = "none";
            nextSlide.style.transform = "rotateY(-180deg)";
            nextSlide.style.zIndex = "5"; 

            currentSlide.style.transition = "none";
            currentSlide.style.transform = "rotateY(0deg)";
            currentSlide.style.zIndex = "4";

            void gallery.offsetWidth;

            nextSlide.style.transition = "transform 0.8s ease";
            nextSlide.style.transform = "rotateY(0deg)";
        }

        setTimeout(() => {
            currentSlide.classList.remove("active");
            nextSlide.classList.add("active");
            
            currentSlide.style = "";
            nextSlide.style = "";
            
            isFlipping = false;
        }, 800);
    }

    function flipPage(btn, direction) {
    const card = btn.closest('.menu-card');
    const slides = card.querySelectorAll('.slide');
    let activeIndex = -1;

    // Tìm ảnh đang active
    slides.forEach((slide, index) => {
        if (slide.classList.contains('active')) {
            activeIndex = index;
        }
    });

    // Tính toán index tiếp theo
    let nextIndex = activeIndex + direction;
    if (nextIndex >= slides.length) nextIndex = 0;
    if (nextIndex < 0) nextIndex = slides.length - 1;

    // Hiệu ứng chuyển slide
    slides[activeIndex].classList.remove('active');
    
    // Nếu chuyển tới, ảnh cũ sẽ lật sang trái
    if (direction > 0) {
        slides[activeIndex].classList.add('prev');
    } else {
        slides.forEach(s => s.classList.remove('prev'));
    }

    slides[nextIndex].classList.add('active');
    slides[nextIndex].classList.remove('prev');
}

    document.addEventListener('DOMContentLoaded', () => {
        const galleries = document.querySelectorAll('.menu-gallery');

        galleries.forEach(gallery => {
            let startX = 0;
            let endX = 0;
            let isDragging = false; 

            gallery.addEventListener('touchstart', (e) => {
                startX = e.changedTouches[0].clientX;
            }, {passive: true});

            gallery.addEventListener('touchend', (e) => {
                endX = e.changedTouches[0].clientX;
                handleSwipe(gallery, startX, endX);
            }, {passive: true});

            gallery.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.clientX;
                gallery.style.cursor = 'grabbing';
                e.preventDefault(); 
            });

            gallery.addEventListener('mouseup', (e) => {
                if (!isDragging) return; 
                isDragging = false;
                endX = e.clientX;
                gallery.style.cursor = 'default';
                handleSwipe(gallery, startX, endX);
            });

            gallery.addEventListener('mouseleave', (e) => {
                if (!isDragging) return;
                isDragging = false;
                endX = e.clientX;
                gallery.style.cursor = 'default';
                handleSwipe(gallery, startX, endX);
            });
        });
    });

    function handleSwipe(gallery, startX, endX) {
        const threshold = 40; 
        if (startX - endX > threshold) {
            performFlip(gallery, 1);
        } else if (endX - startX > threshold) {
            performFlip(gallery, -1);
        }
    }
</script>

    <script src="js/dungchung.js"></script>
</body>
</html>