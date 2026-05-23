<?php
session_start();
// 1. Chỉ gọi file kết nối 1 lần duy nhất
include 'php/datk.php'; 

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];

// 3. Thực hiện truy vấn sau khi đã đảm bảo $conn tồn tại từ datk.php
$sql_user = "SELECT phone, address FROM users WHERE id = $user_id";
$res_user = mysqli_query($conn, $sql_user); // Dùng biến $conn từ file datk.php

$user_phone = '';
$user_address = '';

if ($res_user && mysqli_num_rows($res_user) > 0) {
    $user_info = mysqli_fetch_assoc($res_user);
    $user_phone = $user_info['phone'] ?? '';
    $user_address = $user_info['address'] ?? '';
}

    // 1. ĐỊNH NGHĨA TRẠNG THÁI (Sửa lỗi "Không xác định")
    $status_map = [
    // Chuyển màu Chờ xử lý thành màu đỏ (#e74c3c)
    'waiting_confirm' => ['label' => '⏳ Chờ xử lý', 'color' => '#e74c3c'], 
    'confirmed'       => ['label' => '✅ Đã xác nhận', 'color' => '#27ae60'],
    'shipped_to_hub'  => ['label' => '📦 Đã gửi bưu cục', 'color' => '#3498db'],
    'in_transit'      => ['label' => '🚚 Đang giao hàng', 'color' => '#9b59b6'],
    'delivered'       => ['label' => '🏁 Hoàn tất', 'color' => '#2c3e50'],
    'cancelled'       => ['label' => '❌ Đã hủy', 'color' => '#7f8c8d']
];
    ?>
    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        alert("Thao tác thành công!");
    </script>
<?php endif; ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Giỏ Hàng & Lịch Sử - Fashion Shop</title>
        <link rel="stylesheet" href="css/dungchung.css">
        <link rel="stylesheet" href="css/navbar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
       <style>
    /* --- TỔNG THỂ --- */
    body { background-color: #fdfaf9; } /* Nền kem cực nhạt */
    .cart-container { padding: 140px 8% 50px; min-height: 80vh; max-width: 1300px; margin: 0 auto; }

    /* --- TABS (CHUYỂN ĐỔI) - PHONG CÁCH LUXURY --- */
    .tab-btn-container { 
        display: flex; 
        gap: 20px; 
        margin-bottom: 30px; 
        border-bottom: 1px solid #efebe9; 
    }
    .tab-btn { 
        padding: 15px 10px; 
        cursor: pointer; 
        font-weight: 600; 
        color: #a1887f; /* Nâu nhạt */
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-size: 13px;
        transition: 0.3s; 
        border-bottom: 3px solid transparent;
        background: transparent;
        border-top: none;
        border-left: none;
        border-right: none;
    }
    .tab-btn.active { 
        color: #5d4037; /* Nâu đậm */
        border-bottom-color: #8d6e63; /* Màu Nâu chủ đạo */
    }

    /* --- BẢNG GIỎ HÀNG (TRẮNG NÂU THANH LỊCH) --- */
    .cart-table { 
        width: 100%; 
        border-collapse: separate; 
        border-spacing: 0 12px; 
        background: transparent;
    }
    .cart-table th { 
        background: #f5f0ee; /* Nền header màu kem đậm */
        color: #5d4037; 
        padding: 18px; 
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1.5px;
        font-weight: 700;
        border: none;
        border-radius: 4px;
    }
    .cart-table tr.cart-row, .cart-table tbody tr { 
        background: #fff; 
        box-shadow: 0 4px 15px rgba(141, 110, 99, 0.08); /* Đổ bóng màu nâu cực nhẹ */
        transition: 0.3s;
    }
    .cart-table tr.cart-row:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(141, 110, 99, 0.12);
    }
    .cart-table td { 
        padding: 20px; 
        vertical-align: middle;
        border: none;
        color: #4e342e;
    }
    .cart-table tr td:first-child { border-radius: 12px 0 0 12px; }
    .cart-table tr td:last-child { border-radius: 0 12px 12px 0; }

    /* --- NÚT TĂNG GIẢM SỐ LƯỢNG --- */
    /* Style cho ô chọn Size */
.cart-size-wrapper select:hover {
    border-color: #e67e22; /* Màu cam cam của Huno Shop */
    background-color: #fff;
}

.cart-size-wrapper select:focus {
    border-color: #e67e22;
    box-shadow: 0 0 0 0.2rem rgba(230, 126, 34, 0.25);
}

/* Style cho nút +/- cho đồng bộ */
.qty-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    border-radius: 4px;
    transition: 0.3s;
}

.qty-btn:hover {
    background: #e67e22;
    color: white;
    border-color: #e67e22;
}

    /* --- BADGE TRẠNG THÁI --- */
    .status-badge { 
        padding: 6px 16px; 
        border-radius: 4px; 
        font-size: 10px; 
        color: #fff; 
        font-weight: 600; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    /* Các màu trạng thái theo tông Nâu/Đất */
    .status-waiting_confirm { background: #a1887f; }
    .status-shipping { background: #8d6e63; }
    .status-delivered { background: #5d4037; }

    /* --- MODAL THANH TOÁN --- */
/* --- CSS SỬA LỖI & NÂNG CẤP CHECKOUT MODAL 2 CỘT --- */
.modal {
    display: none; 
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    overflow-y: auto;          /* Lăn cuộn chuột dọc toàn màn hình nếu modal dài */
    padding: 30px 10px;
    box-sizing: border-box;
}

.modal-content {
    background: #fff;
    width: 90%;
    max-width: 850px !important; /* Tăng độ rộng lên 850px để đủ khoảng trống chia 2 cột */
    margin: 30px auto !important;
    padding: 30px;
    position: relative;
    border-radius: 12px !important;
    box-shadow: 0 25px 60px rgba(78, 52, 46, 0.2) !important;
    border-top: 6px solid #8d6e63;
    box-sizing: border-box;
}

.modal-flex-container {
    display: flex;
    gap: 25px;
    align-items: flex-start;
}

.modal-col-left {
    flex: 1.2;
    background: #fdfcfb;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #f5eee9;
    box-sizing: border-box;
}

.modal-col-right {
    flex: 0.8;
    background: #fff;
    box-sizing: border-box;
}

/* Tự động chuyển về dọc khi xem bằng điện thoại smartphone */
@media screen and (max-width: 768px) {
    .modal-flex-container {
        flex-direction: column;
        gap: 15px;
    }
    .modal-content {
        width: 95%;
        padding: 20px 15px;
    }
    .modal-col-left, .modal-col-right {
        width: 100%;
        flex: none;
    }
}
/* Header */
.modal-content h3 {
    margin-top: 0;
    color: #4e342e; /* Nâu đậm cho chữ tiêu đề */
    font-size: 1.4rem;
    border-bottom: 2px solid #f4f4f4;
    padding-bottom: 15px;
}

/* Form groups */
.form-group {
    margin-bottom: 15px;
}

.form-group input, 
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
    transition: all 0.3s;
}

/* Khi người dùng click vào ô nhập liệu */
.form-group input:focus, 
.form-group textarea:focus {
    outline: none;
    border-color: #8d6e63;
    box-shadow: 0 0 0 3px rgba(141, 110, 99, 0.1);
}

/* Khu vực chọn thanh toán */
.payment-methods {
    background: #fdfaf9; /* Nền hơi hướng tông ấm */
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #eee;
    margin: 10px 0 20px;
}

.payment-methods label {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 8px 0;
    transition: color 0.2s;
    color: #5d4037;
}

.payment-methods label:hover {
    color: #8d6e63;
}

.payment-methods input[type="radio"] {
    margin-right: 10px;
    accent-color: #8d6e63; /* Màu nâu cho nút radio */
}

/* Buttons */
.btn-confirm {
    width: 100%;
    padding: 14px;
    background: #8d6e63;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s, transform 0.1s;
}

.btn-confirm:hover {
    background: #795548; /* Nâu đậm hơn một chút khi hover */
}

.btn-confirm:active {
    transform: scale(0.98); /* Hiệu ứng nhấn nút */
}

.btn-cancel {
    width: 100%;
    margin-top: 15px;
    background: transparent;
    border: none;
    color: #8d6e63; /* Chuyển sang màu nâu để đồng bộ */
    cursor: pointer;
    font-size: 14px;
    text-decoration: underline;
}
    
    /* --- NÚT BẤM CHUNG --- */
    .btn-main {
        background: #8d6e63;
        color: #fff;
        border: none;
        padding: 12px 25px;
        border-radius: 4px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 13px;
        transition: 0.3s;
        cursor: pointer;
    }
    .btn-main:hover {
        background: #5d4037;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(93, 64, 55, 0.2);
    }

    /* --- TAB CONTROL --- */
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.5s ease; }
    .detail-row { display: none; background: #fdfaf9; }

    @keyframes fadeIn {
        from { opacity: 0; } to { opacity: 1; }
    }
    /* Chỉnh kích thước ảnh sản phẩm trong bảng giỏ hàng */
.cart-img {
    width: 70px;          /* Độ rộng nhỏ gọn */
    height: 90px;         /* Chiều cao lớn hơn chút để giữ form quần áo */
    object-fit: cover;    /* Giữ ảnh không bị méo */
    border-radius: 8px;   /* Bo góc đồng bộ với style chung */
    border: 1px solid #efebe9;
    box-shadow: 0 2px 8px rgba(141, 110, 99, 0.1);
}

/* Căn chỉnh lại cột sản phẩm để ảnh và chữ đẹp hơn */
.cart-table td:nth-child(2) {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 250px; /* Đảm bảo tên sản phẩm không bị đẩy xuống dòng quá sớm */
}

.cart-table td strong {
    width:100px;
    font-size: 14px;
    color: #5d4037;
}
/* Style cho bong bóng thông báo */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #2ecc71; /* Màu xanh lá */
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    animation: slideIn 0.5s ease-out, fadeOut 0.5s ease-in 1.5s forwards;
}
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 15px 25px;
    background: #e67e22; /* Màu cam cam cho giống theme Huno Shop */
    color: #fff;
    border-radius: 8px;
    z-index: 9999;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    animation: fadeIn 0.5s;
}/* Spinner quay tròn */
.spinner {
    display: inline-block;
    width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.8s linear infinite;
    margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Class chặn click khi đang loading */
.btn-loading {
    opacity: 0.6;
    pointer-events: none;
    cursor: wait !important;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; visibility: hidden; }
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

        <div class="cart-container">
    <div class="tab-btn-container">
        <div class="tab-btn active" onclick="switchTab('cart-tab', this)">Giỏ Hàng</div>
        <div class="tab-btn" onclick="switchTab('history-tab', this)">Lịch Sử Đơn Hàng</div>
    </div>

    <div id="cart-tab" class="tab-content active">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
            <div>
                <input type="checkbox" id="selectAllTop" onclick="toggleAll(this)" style="margin-right: 10px;">
                <label for="selectAllTop">Chọn tất cả</label>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-secondary" onclick="removeSelectedItems()" style="background:#f39c12; color:white; padding:8px 15px; border:none; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-eraser"></i> Xóa mục đã chọn
                </button>
                <button class="btn-danger" onclick="removeAllCart()" style="background:#e74c3c; color:white; padding:8px 15px; border:none; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-trash-can"></i> Xóa tất cả
                </button>
            </div>
        </div>

        <table class="cart-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_cart = "SELECT o.*, p.product_name, p.image, p.price FROM orders o 
                             JOIN products p ON o.product_id = p.id 
                             WHERE o.user_id = $user_id AND o.status = 'pending' ORDER BY o.id DESC";
                $res_cart = mysqli_query($conn, $sql_cart);
                if(mysqli_num_rows($res_cart) > 0) {
                    while($row = mysqli_fetch_assoc($res_cart)){
                        $img = explode(',', $row['image'])[0];
                ?>
                    <tr class="cart-row" data-id="<?= $row['id'] ?>">
                        <td><input type="checkbox" class="item-checkbox" data-price="<?= $row['total_price'] ?>" onclick="calculateTotal()"></td>
                        <td style="display:flex; align-items:center; gap:15px;">
                            <img src="<?= $img ?>" class="cart-img">
                            <strong><?= $row['product_name'] ?></strong>
                        </td>
                        <td><?= number_format($row['price']) ?>đ</td>
                        <td>
                            <button class="qty-btn" onclick="updateQty(<?= $row['id'] ?>, -1)">-</button>
                            <span id="qty-<?= $row['id'] ?>" style="margin: 0 10px; font-weight: bold;"><?= $row['quantity'] ?></span>
                            <button class="qty-btn" onclick="updateQty(<?= $row['id'] ?>, 1)">+</button>
                        </td>
                        <td>
                            <div class="cart-size-wrapper" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <span style="font-size: 13px; color: #555; font-weight: 500;">Size:</span>
                                <select onchange="updateCartSize(<?= $row['id'] ?>, this.value)" 
                                        style="padding: 5px 10px; border-radius: 6px; border: 1px solid #ced4da; 
                                            background-color: #f8f9fa; font-size: 13px; cursor: pointer; 
                                            transition: all 0.3s ease; outline: none;">
                                    <?php 
                                    $p_id = $row['product_id'];
                                    $sql_size = "SELECT size FROM products WHERE id = $p_id";
                                    $res_size = mysqli_query($conn, $sql_size);
                                    $p_data = mysqli_fetch_assoc($res_size);
                                    
                                    if ($p_data && !empty($p_data['size'])) {
                                        $available = explode(',', $p_data['size']);
                                        foreach ($available as $sz) {
                                            $sz_name = explode(':', trim($sz))[0];
                                            $selected = ($sz_name == $row['size']) ? 'selected' : '';
                                            echo "<option value='$sz_name' $selected>$sz_name</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td><strong style="color: #e67e22;"><?= number_format($row['total_price']) ?>đ</strong></td>
                        <td>
                            <button onclick="removeCartItem(<?= $row['id'] ?>)" style="background:none; border:none; color:#e74c3c; cursor:pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php } } else { ?>
                    <tr><td colspan="6" style="text-align:center; padding:50px;">Giỏ hàng trống. <a href="shop.php" style="color:#e67e22;">Mua sắm ngay!</a></td></tr>
                <?php } ?>
            </tbody>
        </table>

        <div style="text-align:right; margin-top:20px; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <h3>Tổng cộng: <span id="grandTotal" style="color:#e67e22; font-size: 1.5rem;">0đ</span></h3>
            <button type="button" class="btn-main" onclick="openCheckoutModal()" style="margin-top: 15px; width: 100%; max-width: 250px; padding: 14px;">
                Tiến hành đặt hàng
            </button>
        </div>
    </div>

    <div id="history-tab" class="tab-content">
    <table class="cart-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql_history_grouped = "
            SELECT 
                o.phone, o.address, o.status, o.note, 
                SUM(o.total_price) as grand_total, 
                GROUP_CONCAT(o.id) as list_ids, 
                MAX(o.id) as group_display_id, 
                MAX(o.created_at) as order_date 
            FROM orders o 
            WHERE o.user_id = $user_id AND o.status != 'pending' 
            GROUP BY o.created_at, o.phone, o.address 
            ORDER BY created_at DESC"; // Đảm bảo đơn mới nhất lên đầu dựa trên thời gian đặt

        $res_history = mysqli_query($conn, $sql_history_grouped);

        if(mysqli_num_rows($res_history) > 0) {
            while($h = mysqli_fetch_assoc($res_history)) {
                // SỬA LỖI TẠI ĐÂY: Gán giá trị cho $group_id
                $group_id = $h['group_display_id'];
                
                // 1. Làm sạch dữ liệu trạng thái
                $current_status = strtolower(trim($h['status'])); 
    
                // Nếu không khớp key nào thì ép về màu đỏ của 'waiting_confirm' cho an toàn
                $st = $status_map[$current_status] ?? ['label' => '⏳ Đang xử lý', 'color' => '#e74c3c'];

                // Điều kiện để hiện nút Hủy màu đỏ: Chờ xác nhận HOẶC Đã xác nhận
                $can_cancel = ($current_status == 'waiting_confirm' || $current_status == 'confirmed' || $current_status == '');
        ?>
            <tr style="background:#fdfdfd">
                <td>
                    <?php 
                    // Tạo mã đơn: DH + NgàyThángNăm + GiờPhút + ID
                    $order_code = "DH-" . date("dmy-Hi", strtotime($h['order_date'])) . "-" . $group_id;
                    echo "<b>" . $order_code . "</b>";
                    ?>
                </td>
                <td><?= date("d/m/Y H:i", strtotime($h['order_date'])) ?></td>
                <td style="color:#e67e22; font-weight:bold"><?= number_format($h['grand_total']) ?>đ</td>
                <td>
                    <span class="status-badge" style="background:<?= $st['color'] ?>; padding: 4px 10px; border-radius: 20px; color: #fff; font-size: 11px;">
                        <?= $st['label'] ?>
                    </span>
                </td>
                <td style="display: flex; gap: 10px; align-items: center;">
                    <button onclick="toggleOrderDetails(<?= $group_id ?>)" style="background:none; border:none; cursor:pointer; color:#3498db; font-weight: 600;">
                        Xem <i class="fa-solid fa-chevron-down" id="icon-<?= $group_id ?>"></i>
                    </button>

                    <?php if ($can_cancel): ?>
                        <button onclick="handleCancelOrder('<?= $h['list_ids'] ?>', this)" 
                                style="background:#e74c3c; color:white; border:none; padding:6px 15px; border-radius:4px; cursor:pointer; font-size:12px; font-weight:bold;">
                            <i class="fa-solid fa-trash-can"></i> Hủy đơn
                        </button>
                    <?php else: ?>
                        <button disabled style="background:#dcdde1; color:#7f8c8d; border:none; padding:6px 15px; border-radius:4px; cursor:not-allowed; font-size:12px; font-weight:bold;">
                            <i class="fa-solid fa-ban"></i> Hủy
                        </button>
                    <?php endif; ?>
                </td>
            </tr>

            <tr id="details-<?= $group_id ?>" class="detail-row" style="display: none;">
                <td colspan="5">
                    <div class="detail-box" style="padding: 15px; background: #f9f9f9; border-left: 3px solid #e67e22; margin: 5px;">
                        <p style="font-size:13px; margin-bottom:10px">
                            <b>Địa chỉ:</b> <?= htmlspecialchars($h['address']) ?> | 
                            <b>SĐT:</b> <?= htmlspecialchars($h['phone']) ?> |
                            <b>Ghi chú:</b> <?= htmlspecialchars($h['note'] ?: 'Không có') ?>
                        </p>
                        <table style="width:100%; font-size:14px; border-top: 1px solid #eee;">
                            <?php
                            $sub_ids = $h['list_ids'];
                            // THÊM o.size vào SELECT
                            $sub_sql = "SELECT p.product_name, o.quantity, o.total_price, o.size 
                                        FROM orders o 
                                        JOIN products p ON o.product_id = p.id 
                                        WHERE o.id IN ($sub_ids)";
                            $sub_res = mysqli_query($conn, $sub_sql);
                            while($item = mysqli_fetch_assoc($sub_res)) {
                                echo "<tr>
                                        <td style='padding:8px 0'>
                                            • {$item['product_name']} 
                                            <span style='color:#7f8c8d; font-size:12px'>(Size: {$item['size']})</span>
                                        </td>
                                        <td>x{$item['quantity']}</td>
                                        <td style='text-align:right'>".number_format($item['total_price'])."đ</td>
                                    </tr>";
                            }
                            ?>
                        </table>
                    </div>
                </td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center; padding:40px'>Chưa có đơn hàng nào trong lịch sử</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div id="checkoutModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeCheckoutModal()" style="position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #3e2723; font-weight: bold; line-height: 1;">&times;</span>
        
        <h3 style="text-align: center; margin-top: 0; margin-bottom: 25px; color: #4e342e; font-size: 1.5rem; font-weight: 700; border-bottom: 2px solid #efebe9; padding-bottom: 15px;">
            <i class="fa-solid fa-box-open" style="color: #8d6e63;"></i> TIẾN HÀNH ĐẶT HÀNG
        </h3>
        
        <form id="finalCheckoutForm" method="POST" action="php/process_order.php">
            <input type="hidden" id="hiddenDiscount" name="discount_amount" value="0">
            <input type="hidden" id="hiddenCouponCode" name="coupon_code" value="">

            <div class="modal-flex-container">
                <div class="modal-col-left">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #5d4037; font-size: 13.5px; display: block; margin-bottom: 5px;"><i class="fa-solid fa-phone"></i> Số điện thoại nhận hàng:</label>
                        <input type="text" id="finalPhone" name="phone" placeholder="Nhập số điện thoại..." value="<?= htmlspecialchars($user_phone) ?>" required style="margin-top: 0;">
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 600; color: #5d4037; font-size: 13.5px; display: block; margin-bottom: 5px;"><i class="fa-solid fa-location-dot"></i> Địa chỉ chi tiết:</label>
                        <textarea id="finalAddress" name="address" placeholder="Địa chỉ chi tiết..." required style="height: 70px; resize: none; margin-top: 0;"><?= htmlspecialchars($user_address) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 600; color: #5d4037; font-size: 13.5px; display: block; margin-bottom: 5px;"><i class="fa-solid fa-credit-card"></i> Phương thức thanh toán:</label>
                        <select name="payment_method" id="paymentMethod" onchange="toggleBankInfo(this.value)" style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background: #fff; color: #4e342e; font-weight: 500; cursor: pointer;">
                            <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                            <option value="banking">Chuyển khoản ngân hàng</option>
                        </select>

                        <div id="bankInfo" style="display:none; background:#fffbf9; padding:12px; border-radius:8px; margin-top:10px; border: 1px dashed #8d6e63; color: #5d4037; font-size: 13px; line-height: 1.5;">
                            <p style="margin-top:0; margin-bottom:5px; color: #e67e22;"><strong style="font-weight:600;"><i class="fa-solid fa-building-columns"></i> Thông tin chuyển khoản:</strong></p>
                            <p style="margin: 3px 0;">Ngân hàng: <strong>Vietcombank</strong> - STK: <strong style="font-size:14px; color:#3e2723;">123132</strong></p>
                            <p style="margin: 3px 0;">Chủ tài khoản: <strong>xàm xàm ba láp</strong></p>
                            <p style="margin: 5px 0 0; font-style: italic; color: #8d6e63;">Nội dung CK: DH [Mã đơn hàng]</p>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 600; color: #5d4037; font-size: 13.5px; display: block; margin-bottom: 5px;"><i class="fa-solid fa-pen"></i> Ghi chú thêm đơn hàng:</label>
                        <textarea id="finalNote" name="note" placeholder="Ghi chú thêm (Màu sắc, kích cỡ, thời gian nhận)..." style="height: 60px; resize: none; margin-top: 0;"></textarea>
                    </div>
                </div>

                <div class="modal-col-right">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; color: #3e2723; font-size: 14px;"><i class="fa-solid fa-ticket"></i> Mã giảm giá (Voucher)</label>
                        <div style="display: flex; gap: 8px; margin-top: 6px;">
                            <input type="text" id="checkoutCoupon" name="coupon_code" class="form-control" placeholder="Ví dụ: HUNONEW" style="flex: 1; padding: 10px 12px; border-radius: 8px; border: 1px solid #d7ccc8; text-transform: uppercase; background: #fff; margin:0; font-weight: 600;">
                            <button type="button" onclick="checkVoucher()" style="background: #3e2723; color: white; border: none; padding: 0 18px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13.5px; transition: 0.2s;">Áp dụng</button>
                        </div>
                        <span id="couponMessage" style="font-size: 13px; display: block; margin-top: 6px; font-weight: 600;"></span>
                    </div>

                    <div class="checkout-summary" style="background: #fdfaf9; padding: 18px; border-radius: 10px; margin-top: 0; margin-bottom: 20px; border: 1px dashed #8d6e63;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #5d4037; font-size: 14px;">
                            <span>Tổng tiền hàng:</span>
                            <span id="summarySubtotal" style="font-weight: 600;">0đ</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #c0392b; font-size: 14px;">
                            <span>Giảm giá (Voucher):</span>
                            <span id="summaryDiscount" style="font-weight: 600;">-0đ</span>
                        </div>
                        <hr style="border: 0; border-top: 1px solid #d7ccc8; margin: 12px 0;">
                        <div style="display: flex; justify-content: space-between; font-size: 16px; color: #3e2723; font-weight: bold; align-items: center;">
                            <span>Tổng thanh toán:</span>
                            <span id="summaryTotal" style="font-size: 20px; color: #8d6e63;">0đ</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-confirm" style="margin-top: 0;"><i class="fa-solid fa-circle-check"></i> Xác nhận đặt hàng</button>
                    <button type="button" class="btn-cancel" onclick="closeCheckoutModal()" style="display: block; width: 100%; text-align: center; margin-top: 12px; font-weight: 500;">Quay lại giỏ hàng</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:#fff; width:300px; padding:20px; border-radius:15px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
        <h3 id="modalMsg">Bạn có chắc chắn?</h3>
        <button id="btnConfirm" style="background:#e67e22; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">Đồng ý</button>
        <button onclick="document.getElementById('confirmModal').style.display='none'" style="background:#ccc; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; margin-left:10px;">Hủy</button>
    </div>
</div>
<script>
function showToast(message) {
    let toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerText = message;
    toast.style.cssText = "position:fixed; bottom:20px; right:20px; padding:15px 25px; background:#e67e22; color:#fff; border-radius:8px; z-index:9999; box-shadow:0 4px 10px rgba(0,0,0,0.2); animation: fadeIn 0.5s;";
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}
// Biến toàn cục để lưu số tiền gốc của các sản phẩm được chọn thanh toán
let originalTotalCart = 0; 

// 1. Hàm chuyển đổi qua lại giữa tab Giỏ Hàng và Lịch Sử Đơn Hàng
function switchTab(tabId, element) {
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));

    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));

    document.getElementById(tabId).classList.add('active');
    element.classList.add('active');
}

// 2. Hàm ẩn/hiện chi tiết sản phẩm của một đơn hàng cũ trong Lịch Sử
function toggleOrderDetails(id) {
    const el = document.getElementById('details-' + id);
    const icon = document.getElementById('icon-' + id);
    
    if (el.style.display === 'table-row') {
        el.style.display = 'none';
        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
    } else {
        el.style.display = 'table-row';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
    }
}

// 3. Hàm ẩn/hiện thông tin số tài khoản khi chọn phương thức Chuyển Khoản Ngân Hàng
function toggleBankInfo(value) {
    const bankInfo = document.getElementById('bankInfo');
    if (bankInfo) {
        bankInfo.style.display = (value === 'banking') ? 'block' : 'none';
    }
}

// 4. Hàm bổ trợ tự động tính tổng tiền giỏ hàng dựa trên các mục được tích chọn (Checkboxes)
function calculateTotal() {
    let total = 0;
    const selected = document.querySelectorAll('.item-checkbox:checked');
    selected.forEach(cb => {
        total += parseInt(cb.dataset.price) || 0;
    });
    document.getElementById('grandTotal').innerText = new Intl.NumberFormat('vi-VN').format(total) + "đ";
    return total;
}

// 5. Hàm Tích chọn / Bỏ tích chọn toàn bộ sản phẩm trong giỏ hàng
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
    calculateTotal();
}

// 6. Hàm mở Modal Giao Hàng và truyền số tổng tiền từ các checkbox đang tích chọn vào biên lai
function openCheckoutModal() {
    // Lấy tổng tiền thực tế từ các sản phẩm được tích chọn
    let currentTotal = calculateTotal();
    
    if (currentTotal <= 0) {
        alert("Vui lòng tích chọn ít nhất một sản phẩm trong giỏ hàng để tiến hành đặt hàng!");
        return;
    }
    
    originalTotalCart = currentTotal;
    
    // Khởi tạo/Reset lại trạng thái mã giảm giá khi mở modal
    document.getElementById('checkoutCoupon').value = "";
    document.getElementById('couponMessage').innerText = "";
    document.getElementById('summaryDiscount').innerText = "-0đ";
    document.getElementById('hiddenDiscount').value = 0;

    // Đổ số tiền tính toán ra bảng biên lai nhỏ đúng định dạng vi-VN
    document.getElementById('summarySubtotal').innerText = originalTotalCart.toLocaleString('vi-VN') + 'đ';
    document.getElementById('summaryTotal').innerText = originalTotalCart.toLocaleString('vi-VN') + 'đ';

    // Hiển thị modal lên
    document.getElementById('checkoutModal').style.display = 'block';
}

// 7. Hàm tự động tính tiền khi người dùng chọn/đổi mã giảm giá trong danh sách xổ xuống
function checkVoucher() {
    const codeInput = document.getElementById('checkoutCoupon');
    const code = codeInput.value.trim();
    const msg = document.getElementById('couponMessage');
    
    if(!code) {
        alert("Vui lòng gõ mã giảm giá!");
        return;
    }

    const fd = new FormData();
    fd.append('code', code);
    fd.append('total_cart', originalTotalCart); // Biến tổng tiền gốc giỏ hàng

    fetch('php/check_coupon.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            msg.style.color = "#27ae60";
            msg.innerText = data.message;

            let discountValue = parseInt(data.discount_value) || 0;
            let finalTotal = originalTotalCart - discountValue;
            if (finalTotal < 0) finalTotal = 0;

            // 1. Cập nhật hiển thị trực quan cho khách hàng thấy
            document.getElementById('summaryDiscount').innerText = "-" + discountValue.toLocaleString('vi-VN') + 'đ';
            document.getElementById('summaryTotal').innerText = finalTotal.toLocaleString('vi-VN') + 'đ';
            
            // 2. QUAN TRỌNG: Gán giá trị vào input ẩn để gửi lên file process_order.php
            document.getElementById('hiddenDiscount').value = discountValue;
            document.getElementById('hiddenCouponCode').value = code; // Lưu luôn cả chữ mã voucher vào
        } else {
            msg.style.color = "#c0392b";
            msg.innerText = data.message;
            
            // Nếu áp dụng thất bại, trả các giá trị ẩn và hiển thị về mặc định ban đầu
            document.getElementById('summaryDiscount').innerText = "-0đ";
            document.getElementById('summaryTotal').innerText = originalTotalCart.toLocaleString('vi-VN') + 'đ';
            document.getElementById('hiddenDiscount').value = 0;
            document.getElementById('hiddenCouponCode').value = "";
        }
    })
    .catch(err => console.error("Lỗi:", err));
}
// 8. Đóng modal khi người dùng bấm click ra vùng ngoài Modal
window.onclick = function(event) {
    let modal = document.getElementById('checkoutModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// 9. Hàm tạo bong bóng thông báo (Toast Notification) và reload lại trang
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
    
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
        location.reload(); 
    }, 2000);
}

// 10. Sự kiện gửi dữ liệu Form xác nhận đặt hàng cuối cùng bằng AJAX Fetch
document.getElementById('finalCheckoutForm').onsubmit = function(e) {
    e.preventDefault();
    
    const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert("Vui lòng chọn sản phẩm để thanh toán!");
        return;
    }

    // 1. Tối ưu UI: Bật trạng thái Loading
    const btnSubmit = this.querySelector('button[type="submit"]');
    const originalText = btnSubmit.innerText;
    btnSubmit.classList.add('btn-loading');
    btnSubmit.innerHTML = `<span class="spinner"></span> Đang đặt hàng...`;

    const orderIds = Array.from(selectedCheckboxes).map(cb => cb.closest('.cart-row').dataset.id).join(',');
    
    const fd = new FormData(this);
    fd.append('order_ids', orderIds);

    fetch('php/process_order.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        // Tắt loading
        btnSubmit.classList.remove('btn-loading');
        btnSubmit.innerText = originalText;

        if (data.trim() === 'success') {
            showToast("Đặt hàng thành công!");
            setTimeout(() => location.reload(), 1500); // Reload sau 1.5s để khách thấy thông báo
        } else {
            console.error("Lỗi server trả về:", data);
            alert("Có lỗi xảy ra: " + data);
        }
    })
    .catch(err => {
        btnSubmit.classList.remove('btn-loading');
        btnSubmit.innerText = originalText;
        console.error("Lỗi kết nối:", err);
    });
};
// 11. Hàm cập nhật số lượng (+ / -) của sản phẩm trong giỏ hàng
function updateQty(orderId, change) {
    const qtyElement = document.getElementById('qty-' + orderId);
    let newQty = parseInt(qtyElement.innerText) + change;

    if (newQty < 1) return; 

    const fd = new FormData();
    fd.append('order_id', orderId);
    fd.append('new_qty', newQty);

    fetch('php/update_cart_qty.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            qtyElement.innerText = newQty;
            
            const row = document.querySelector(`.cart-row[data-id="${orderId}"]`);
            const priceCell = row.querySelectorAll('td')[5].querySelector('strong'); // Chỉnh đúng index cột thành tiền
            priceCell.innerText = new Intl.NumberFormat('vi-VN').format(data.new_total) + "đ";
            
            row.querySelector('.item-checkbox').dataset.price = data.new_total;
            calculateTotal();
        }
    });
}

// 12. Hàm cập nhật Kích cỡ (Size) áo/quần trực tiếp trên giỏ hàng công khai
window.updateCartSize = function(orderId, newSize) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('new_size', newSize);

    fetch('php/update_cart_size.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Mạng không ổn định');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log("Cập nhật size thành công!");
        } else {
            alert("Không thể cập nhật: " + data.message);
            location.reload();
        }
    })
    .catch(error => console.error("Lỗi kết nối:", error));
};
// =======================================================
// BỘ HÀM XÓA CẢI TIẾN (DÙNG MODAL XÁC NHẬN)
// =======================================================
function openConfirmModal(message, actionCallback) {
    document.getElementById('modalMsg').innerText = message;
    const btn = document.getElementById('btnConfirm');
    const modal = document.getElementById('confirmModal');
    
    modal.style.display = 'flex'; // Hiện Modal
    
    // Gán sự kiện cho nút Đồng ý
    btn.onclick = () => {
        modal.style.display = 'none';
        actionCallback(); // Chạy hàm xóa thật sự
    };
}
function confirmAction() {
    if (!itemToDelete) return;
    
    // Đóng Modal lại
    document.getElementById('confirmModal').style.display = 'none';
    
    // Thực hiện fetch xóa như cũ
    const fd = new FormData();
    fd.append('ids', itemToDelete);
    fd.append('action', 'delete_selected');

    fetch('php/remove_cart.php', { method: 'POST', body: fd })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            showToast("✅ Đã xóa sản phẩm!");
            setTimeout(() => location.reload(), 1000);
        } else {
            alert("Lỗi: " + data);
        }
    });
}
// 13. Hàm xóa các mục đã chọn
function removeSelectedItems() {
    const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert("Vui lòng chọn ít nhất một sản phẩm!");
        return;
    }

    openConfirmModal("Bạn có chắc chắn muốn xóa các mục đã chọn?", () => {
        const ids = Array.from(selectedCheckboxes).map(cb => cb.closest('.cart-row').dataset.id).join(',');
        const fd = new FormData();
        fd.append('ids', ids);
        fd.append('action', 'delete_selected');

        fetch('php/manage_cart.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                showToast("🗑️ Đã xóa thành công!");
                setTimeout(() => location.reload(), 1000);
            } else {
                alert("Lỗi: " + data);
            }
        });
    });
}

// 14. Hàm xóa tất cả giỏ hàng
function removeAllCart() {
    openConfirmModal("Bạn có chắc chắn muốn XÓA TẤT CẢ sản phẩm trong giỏ hàng?", () => {
        const fd = new FormData();
        fd.append('action', 'delete_all');

        fetch('php/manage_cart.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                showToast("✨ Giỏ hàng đã được dọn sạch!");
                setTimeout(() => location.reload(), 1000);
            } else {
                alert("Lỗi: " + data);
            }
        });
    });
}

// 15. Hàm xóa 1 hàng sản phẩm (Dùng chung Modal)
function removeCartItem(id) {
    // Lưu ID vào biến toàn cục trước
    window.itemToDelete = id; 
    
    // Gọi Modal xác nhận
    openConfirmModal("Xóa sản phẩm này khỏi giỏ hàng?", () => {
        const fd = new FormData();
        fd.append('ids', window.itemToDelete);
        fd.append('action', 'delete_selected');

        fetch('php/manage_cart.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                showToast("✅ Đã xóa sản phẩm!");
                setTimeout(() => location.reload(), 1000);
            } else {
                alert("Lỗi: " + data);
            }
        });
    });
}

// 16. Hủy đơn hàng (Trong lịch sử - Cũng đổi sang dùng Modal)
function handleCancelOrder(listIds, buttonElement) {
    openConfirmModal("Bạn có chắc chắn muốn hủy đơn hàng này không?", () => {
        const originalContent = buttonElement.innerHTML;
        buttonElement.innerHTML = "⏳ Đang xử lý...";
        buttonElement.disabled = true;

        fetch('php/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ids=${listIds}&status=cancelled`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast("✅ Đã hủy đơn hàng!");
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Lỗi: ' + data.message);
                buttonElement.innerHTML = originalContent;
                buttonElement.disabled = false;
            }
        });
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