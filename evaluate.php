<?php 
session_start();
include 'php/datk.php'; 

// Lấy thông số tổng quan
// 1. Thực thi truy vấn
$query = "SELECT 
    AVG(rating) as avg_rating, 
    COUNT(*) as total_reviews,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star5,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star4,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star3,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star2,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star1
    FROM evaluates";

$stats_res = mysqli_query($conn, $query);

// 2. Kiểm tra xem truy vấn có lỗi không trước khi fetch
if (!$stats_res) {
    die("Lỗi truy vấn SQL: " . mysqli_error($conn)); 
}

$stats = mysqli_fetch_assoc($stats_res);
$avg = round($stats['avg_rating'], 1) ?: 0;
$total = $stats['total_reviews'] ?: 0;

// Lấy danh sách checkbox
$products_res = mysqli_query($conn, "SELECT product_name FROM products LIMIT 10");
$categories_res = mysqli_query($conn, "SELECT DISTINCT category FROM products");

// Lấy danh sách đánh giá
$reviews_res = mysqli_query($conn, "SELECT * FROM evaluates ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Trải nghiệm khách hàng | Golden Feast</title>
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --gold: #c5a059; --gold-dark: #a37e3d; --bg: #fdfbf7; --text: #2d2d2d; }
        body { background: var(--bg); color: var(--text); padding-top: 100px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        .eval-wrapper { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        /* --- TỔNG QUAN --- */
        .rating-overview { 
            background: white; border-radius: 20px; padding: 40px; 
            display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 40px;
            align-items: center;
        }
        .avg-box { text-align: center; border-right: 1px solid #eee; }
        .avg-box h1 { font-size: 4rem; color: var(--gold); margin: 0; }
        .total-txt { color: #888; font-size: 0.9rem; }

        .progress-container { display: flex; flex-direction: column; gap: 10px; }
        .progress-line { display: flex; align-items: center; gap: 15px; font-size: 0.85rem; }
        .bar-bg { flex: 1; height: 8px; background: #f0f0f0; border-radius: 10px; overflow: hidden; }
        .bar-fill { height: 100%; background: var(--gold); border-radius: 10px; }

        /* --- CẤU TRÚC CHÍNH --- */
        .eval-grid { display: grid; grid-template-columns: 380px 1fr; gap: 40px; align-items: start; }

        /* --- FORM --- */
        .form-sticky { position: sticky; top: 120px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #f1f1f1; }
        .form-sticky h3 { margin-top: 0; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 5px; margin-bottom: 20px; }

        .tag-selection { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .tag-item { cursor: pointer; padding: 8px 15px; border-radius: 25px; border: 1px solid #eee; font-size: 0.85rem; transition: 0.3s; background: #fff; }
        .tag-item input { display: none; }
        .tag-item:has(input:checked) { background: var(--gold); color: white; border-color: var(--gold); }

        .star-rating-v2 { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin: 20px 0; }
        .star-rating-v2 input { display: none; }
        .star-rating-v2 label { font-size: 30px; color: #e0e0e0; cursor: pointer; transition: 0.2s; }
        .star-rating-v2 input:checked ~ label, .star-rating-v2 label:hover, .star-rating-v2 label:hover ~ label { color: var(--gold); }

        .textarea-v2 { width: 100%; border: 1px solid #eee; border-radius: 12px; padding: 15px; background: #fafafa; transition: 0.3s; font-family: inherit; }
        .textarea-v2:focus { outline: none; border-color: var(--gold); background: #fff; }

        .btn-submit-v2 { width: 100%; padding: 15px; background: #1a1a1a; color: white; border: none; border-radius: 12px; font-weight: 600; margin-top: 20px; cursor: pointer; transition: 0.3s; letter-spacing: 1px; }
        .btn-submit-v2:hover { background: var(--gold); transform: translateY(-3px); }

        /* --- LIST --- */
        .review-card-v2 { background: white; padding: 30px; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); transition: 0.3s; position: relative; }
        .review-card-v2:hover { transform: scale(1.01); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .card-top { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; }
        .avatar-circle { width: 45px; height: 45px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--gold); font-size: 1.2rem; }
        
        .badge-items { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 15px; }
        .badge { font-size: 0.7rem; background: #fdf7e8; color: var(--gold-dark); padding: 4px 10px; border-radius: 5px; font-weight: 600; }
        
        .quote-icon { position: absolute; right: 30px; top: 30px; font-size: 2rem; color: #f5f5f5; z-index: 0; }
        .content-v2 { position: relative; z-index: 1; font-style: italic; color: #444; line-height: 1.7; border-left: 3px solid #eee; padding-left: 15px; }

        /* Responsive */
        @media (max-width: 992px) {
            .rating-overview { grid-template-columns: 1fr; text-align: center; }
            .avg-box { border-right: none; border-bottom: 1px solid #eee; padding-bottom: 20px; }
            .eval-grid { grid-template-columns: 1fr; }
            .form-sticky { position: static; }
        }
    </style>
    <link rel="stylesheet" href="css/navbar.css">
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
                <li><a href="exchange.php">Tích Điểm</a></li>
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

    <div class="eval-wrapper">
        <div class="rating-overview">
            <div class="avg-box">
                <h1><?= $avg ?></h1>
                <div class="stars-active" style="color:var(--gold); margin: 10px 0;">
                    <?php for($i=1;$i<=5;$i++) echo ($i <= $avg) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                </div>
                <span class="total-txt"><?= $total ?> đánh giá xác thực</span>
            </div>

            <div class="progress-container">
                <?php 
                for($i=5; $i>=1; $i--) {
                    $count = $stats['star'.$i] ?: 0;
                    $percent = $total > 0 ? ($count / $total) * 100 : 0;
                    echo "
                    <div class='progress-line'>
                        <span style='width:40px'>$i sao</span>
                        <div class='bar-bg'><div class='bar-fill' style='width: $percent%'></div></div>
                        <span style='width:30px; text-align:right'>$count</span>
                    </div>";
                }
                ?>
            </div>

            <div style="text-align: center;">
                <button class="btn-main" onclick="document.getElementById('content-focus').focus()">Viết đánh giá</button>
            </div>
        </div>

        <div class="eval-grid">
            <aside>
                <div class="form-sticky">
                    <h3>Gửi phản hồi</h3>
                    <form id="evalForm">
                        <p style="font-size:0.85rem; margin-bottom:10px; font-weight:600">Bạn đánh giá cho sản phẩm/danh mục nào?</p>
                        <div class="tag-selection">
                            <?php 
                            // Hiển thị các danh mục quần áo
                            while($cat = mysqli_fetch_assoc($categories_res)): ?>
                                <label class="tag-item">
                                    <input type="checkbox" name="targets[]" value="<?= $cat['category'] ?>"> 
                                    # <?= $cat['category'] ?>
                                </label>
                            <?php endwhile; ?>

                            <?php 
                            // Hiển thị một số sản phẩm tiêu biểu
                            while($p = mysqli_fetch_assoc($products_res)): ?>
                                <label class="tag-item">
                                    <input type="checkbox" name="targets[]" value="<?= $p['product_name'] ?>"> 
                                    <?= $p['product_name'] ?>
                                </label>
                            <?php endwhile; ?>
                        </div>

                        <p style="font-size:0.85rem; margin-bottom:5px; font-weight:600">Mức độ hài lòng?</p>
                        <div class="star-rating-v2">
                            <input type="radio" name="rating" id="v5" value="5" required><label for="v5" class="fas fa-star"></label>
                            <input type="radio" name="rating" id="v4" value="4"><label for="v4" class="fas fa-star"></label>
                            <input type="radio" name="rating" id="v3" value="3"><label for="v3" class="fas fa-star"></label>
                            <input type="radio" name="rating" id="v2" value="2"><label for="v2" class="fas fa-star"></label>
                            <input type="radio" name="rating" id="v1" value="1"><label for="v1" class="fas fa-star"></label>
                        </div>

                        <textarea id="content-focus" name="content" class="textarea-v2" rows="5" placeholder="Chia sẻ trải nghiệm của bạn tại Golden Feast..." required></textarea>
                        
                        <button type="submit" class="btn-submit-v2">XÁC NHẬN GỬI <i class="fas fa-paper-plane" style="margin-left:8px"></i></button>
                    </form>
                </div>
            </aside>

            <main class="reviews-list">
                <?php if($total > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($reviews_res)): ?>
                        <div class="review-card-v2">
                            <i class="fas fa-quote-right quote-icon"></i>
                            <div class="card-top">
                                <div class="avatar-circle"><i class="fas fa-user"></i></div>
                                <div>
                                    <div style="font-weight:700; color:var(--text)"><?= htmlspecialchars($row['username']) ?></div>
                                    <div style="color:var(--gold); font-size:0.8rem">
                                        <?php for($i=1;$i<=5;$i++) echo ($i <= $row['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                                    </div>
                                </div>
                                <div style="margin-left:auto; font-size:0.75rem; color:#bbb"><?= date('d/m/Y', strtotime($row['created_at'])) ?></div>
                            </div>

                            <div class="badge-items">
                                <?php 
                                    $tags = explode(',', $row['target_items']);
                                    foreach($tags as $t) if(!empty($t)) echo "<span class='badge'># ".trim($t)."</span>";
                                ?>
                            </div>

                            <div class="content-v2">"<?= htmlspecialchars($row['content']) ?>"</div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:50px; background:white; border-radius:20px">
                        <i class="fas fa-comment-slash" style="font-size:3rem; color:#eee; margin-bottom:20px"></i>
                        <p>Chưa có phản hồi nào từ khách hàng.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        document.getElementById('evalForm').onsubmit = function(e) {
            e.preventDefault();
            // Lấy từ session PHP (đã xử lý ở navbar) hoặc localStorage
            const user = "<?= $_SESSION['fullname'] ?? '' ?>";
            if(!user) { 
                alert("Vui lòng đăng nhập để gửi đánh giá!"); 
                return; 
            }

            const fd = new FormData(this);
            fd.append('username', user);

            fetch('php/process_evaluate.php', { method: 'POST', body: fd })
            .then(r => r.text())
            .then(txt => {
                if(txt.includes("thành công")) {
                    alert("Cảm ơn bạn đã gửi phản hồi!");
                    location.reload();
                } else {
                    alert(txt);
                }
            });
        };
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