<?php
session_start();
include 'php/datk.php'; 
include 'php/functions.php'; // Đảm bảo gọi hàm ghi log hoạt động
// 1. KIỂM TRA BẢO MẬT: Nếu không phải admin VÀ cũng không phải staff thì đá ra trang chủ
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị Shop Quần Áo</title>
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <a href="index.php" class="logo">
                <img src="img/logo/1.png" alt="Fashion Shop" style="height: 100px; width: 100px; object-fit: contain;">
            </a>
            <ul class="menu">
                <li class="menu-item active" onclick="showTab(event, 'dashboard')">
                    <i class="fa-solid fa-chart-line"></i> Báo Cáo
                </li>
                <li class="menu-item" onclick="showTab(event, 'users')">
                    <i class="fa-solid fa-users"></i> Khách Hàng
                </li>
                <li class="menu-item" onclick="showTab(event, 'products')">
                    <i class="fa-solid fa-shirt"></i> Sản Phẩm
                </li>
                <li class="menu-item" onclick="showTab(event, 'orders')">
                    <i class="fa-solid fa-cart-shopping"></i> Đơn Hàng 
                    <?php
                        $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'waiting_confirm'");
                        $count_data = mysqli_fetch_assoc($count_res);
                        if($count_data['total'] > 0) echo "<span class='nav-counter' style='margin:10px; color: red;'>{$count_data['total']}</span>";
                    ?>
                </li>
                <li class="menu-item" onclick="showTab(event, 'coupons')">
                    <i class="fa-solid fa-ticket"></i> Mã Giảm Giá
                </li>
                <li class="menu-item" onclick="showTab(event, 'logs-tab')">
                    <i class="fa-solid fa-clipboard-list"></i> Nhật Ký Hoạt Động
                </li>
                <li class="nav-item logout-item">
                    <a href="php/logout.php" class="nav-link logout-link">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <section id="dashboard" class="tab-content active">
                <div class="admin-header">
                    <h3>Hệ Thống Báo Cáo Doanh Thu</h3>

                    <div class="filter-box" style="display: flex; align-items: center; gap: 12px;margin: 20px;">
                        
                        <button onclick="Excel()" class="btn-main" style="display: inline-flex; align-items: center; gap: 8px; background: #27ae60; color: #fff; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; box-shadow: 0 4px 12px rgba(39, 174, 96, 0.15); border: none; transition: 0.2s; cursor: pointer; white-space: nowrap;">
                            <i class="fa-solid fa-file-excel" style="font-size: 16px;"></i> Xuất Báo Cáo Doanh Thu
                        </button>

                        <input type="date" id="filterDate" class="status-select" style="margin: 0;">
                        <button class="btn-main" onclick="updateCharts()"><i class="fa-solid fa-filter"></i> Lọc</button>
                    </div>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <i class="fa-solid fa-sack-dollar" style="color: #e67e22;"></i>
                        <div class="stat-title">Doanh thu thống kê</div>
                        <div class="stat-number" id="stat-revenue">0đ</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa-solid fa-box-open" style="color: #3498db;"></i>
                        <div class="stat-title">Sản phẩm đã bán</div>
                        <div class="stat-number" id="stat-sold">0</div>
                    </div>
                </div> <div class="chart-container-box" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 30px rgba(78, 52, 46, 0.05); border: 1px solid #efebe9; margin-top: 20px;">
                    <h3 style="color: #4e342e; margin-top: 0; margin-bottom: 20px; font-size: 1.1rem; font-weight: 600;">
                        <i class="fa-solid fa-chart-line" style="color: #8d6e63; margin-right: 8px;"></i> Xu Hướng Biến Động Doanh Thu Thực Tế (7 Ngày)
                    </h3>
                    <div style="position: relative; width: 100%; height: 320px;">
                        <canvas id="hunoRevenueChart"></canvas>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
                    <div class="table-container" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #efebe9; box-shadow: 0 8px 30px rgba(78, 52, 46, 0.05);">
                        <h4 style="color: #4e342e; margin-top: 0; margin-bottom: 15px;"><i class="fa-solid fa-chart-bar" style="color: #e67e22;"></i> Chi Tiết Doanh Thu Các Ngày</h4>
                        <div style="position: relative; width: 100%; height: 280px;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                    <div class="table-container" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #efebe9; box-shadow: 0 8px 30px rgba(78, 52, 46, 0.05);">
                        <h4 style="color: #4e342e; margin-top: 0; margin-bottom: 15px;"><i class="fa-solid fa-chart-pie" style="color: #3498db;"></i> Top 5 Sản Phẩm Bán Chạy</h4>
                        <div style="position: relative; width: 100%; height: 280px;">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

           <section id="users" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Quản Lý Khách Hàng</h3>
                    <input type="text" id="searchUser" class="status-select" placeholder="🔍 Tìm tên hoặc email..." onkeyup="filterTable('users', 'searchUser')">
                    <button class="btn-main" onclick="openUserModal()"><i class="fa-solid fa-plus"></i> Thêm Khách Hàng</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Lấy danh sách người dùng, sắp xếp ID mới nhất lên đầu
                        $userRes = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                        while ($row = mysqli_fetch_assoc($userRes)) {
                            $userJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                            
                            // Xác định màu sắc Badge
                            $roleBadge = 'badge-user';
                            if ($row['role'] == 'admin') $roleBadge = 'badge-admin';
                            else if ($row['role'] == 'staff') $roleBadge = 'badge-staff';

                            // QUAN TRỌNG: Phải có data-user-id='{$row['id']}'
                            echo "<tr data-user-id='{$row['id']}'>
                                    <td>#{$row['id']}</td>
                                    <td class='u-name'>" . (!empty($row['fullname']) ? htmlspecialchars($row['fullname']) : "<i style='color:gray'>(Trống)</i>") . "</td>
                                    <td class='u-email'>" . (!empty($row['email']) ? htmlspecialchars($row['email']) : "---") . "</td>
                                    <td><span class='badge {$roleBadge}'>" . strtoupper($row['role']) . "</span></td>
                                    <td>
                                        <button class='btn-edit' title='Sửa' onclick='editUser({$userJson})'>
                                            <i class='fa-solid fa-pen'></i>
                                        </button>";

                            // Chỉ hiện nút xóa nếu tài khoản không phải Admin
                            if ($row['role'] !== 'admin') {
                                echo " <button class='btn-danger' 
                                            style='background: #ff4757; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-left: 5px;' 
                                            onclick='deleteUser({$row['id']})'>
                                            <i class='fa-solid fa-trash'></i> Xóa
                                        </button>";
                            }

                            echo "  </td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="products" class="tab-content">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px;">
                    <h3>Quản Lý Sản Phẩm</h3>
                    <div style="display: flex; gap: 10px; flex: 1; justify-content: flex-end;">
                        <input type="text" id="searchProduct" class="status-select" placeholder="🔍 Tìm tên sản phẩm..." onkeyup="filterTable('products', 'searchProduct')">
                        <button class="btn-main" onclick="openProductModal()"><i class="fa-solid fa-plus"></i> Thêm</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr><th>Ảnh</th><th>Tên</th><th>Danh Mục</th><th>Size</th><th>Giá</th><th>Tồn</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $prodRes = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                        while ($row = mysqli_fetch_assoc($prodRes)) {
                            $imgArr = explode(',', $row['image']);
                            $prodJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                            echo "<tr data-id='{$row['id']}'>
                                <td><img src='{$imgArr[0]}' style='width:40px; height:50px; object-fit:cover; border-radius:4px;'></td>
                                <td class='p-name'>{$row['product_name']}</td>
                                <td class='p-cat'>{$row['category']}</td>
                                <td class='p-size' style='font-size:11px;'>{$row['size']}</td>
                                <td class='p-price'>".number_format($row['price'])."đ</td>
                                <td class='p-stock'>{$row['stock']}</td>
                                <td>
                                    <button class='btn-edit' onclick='editProduct({$prodJson})'><i class='fa-solid fa-pen'></i></button>
                                    <button class='btn-delete' onclick='deleteProduct({$row['id']})'><i class='fa-solid fa-trash'></i></button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section><section id="products" class="tab-content">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px;">
                    <h3>Quản Lý Sản Phẩm</h3>
                    <div style="display: flex; gap: 10px; flex: 1; justify-content: flex-end;">
                        <input type="text" id="searchProduct" class="status-select" placeholder="🔍 Tìm tên sản phẩm..." onkeyup="filterTable('products', 'searchProduct')">
                        <button class="btn-main" onclick="openProductModal()"><i class="fa-solid fa-plus"></i> Thêm</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr><th>Ảnh</th><th>Tên</th><th>Danh Mục</th><th>Size</th><th>Giá</th><th>Tồn</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $prodRes = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                        while ($row = mysqli_fetch_assoc($prodRes)) {
                            $imgArr = explode(',', $row['image']);
                            $first_image = trim($imgArr[0]);

                            // XỬ LÝ ĐƯỜNG DẪN ẢNH THÔNG MINH
                            if (empty($first_image)) {
                                $src_image = 'img/default.png';
                            } elseif (strpos($first_image, 'img/') === 0 || strpos($first_image, 'http') === 0) {
                                // Nếu đường dẫn đã bắt đầu bằng 'img/' hoặc link mạng thì giữ nguyên
                                $src_image = $first_image;
                            } else {
                                // Nếu chỉ là tên file thô (ví dụ: 'tui_canvas.jpg'), tự bổ sung thư mục 'img/' vào trước
                                $src_image = 'img/' . $first_image;
                            }

                            $prodJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                            echo "<tr data-id='{$row['id']}'>
                                <td><img src='{$src_image}' style='width:40px; height:50px; object-fit:cover; border-radius:4px;'></td>
                                <td class='p-name'>{$row['product_name']}</td>
                                <td class='p-cat'>{$row['category']}</td>
                                <td class='p-size' style='font-size:11px;'>{$row['size']}</td>
                                <td class='p-price'>".number_format($row['price'])."đ</td>
                                <td class='p-stock'>{$row['stock']}</td>
                                <td>
                                    <button class='btn-edit' onclick='editProduct({$prodJson})'><i class='fa-solid fa-pen'></i></button>
                                    <button class='btn-delete' onclick='deleteProduct({$row['id']})'><i class='fa-solid fa-trash'></i></button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="orders" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px;">
                    <h3>Quản Lý Đơn Hàng</h3>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="filterOrderDate" class="status-select" onchange="filterOrdersByDate()">
                        <input type="text" id="searchOrder" class="status-select" placeholder="🔍 Tìm mã đơn, khách hàng..." onkeyup="filterTable('orders', 'searchOrder')">
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Ngày Đặt</th>
                            <th>Khách Hàng</th>
                            <th>Mã Voucher</th> <th>Tổng Tiền</th>
                            <th>PTTT</th>
                            <th>Trạng Thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // CẬP NHẬT LOGIC SQL: Lấy thêm coupon_code và số tiền giảm lớn nhất MAX(o.discount_amount) của nhóm đơn hàng đó
                        $sql_grouped = "SELECT u.fullname, o.phone, o.address, o.status, o.payment_method, 
                                            o.created_at, o.coupon_code,
                                            SUM(o.total_price) as total_goods_price, 
                                            MAX(o.discount_amount) as total_discount,
                                            GROUP_CONCAT(o.id) as list_ids 
                                        FROM orders o 
                                        JOIN users u ON o.user_id = u.id 
                                        WHERE o.status != 'pending' 
                                        GROUP BY o.user_id, o.created_at, o.status, o.payment_method, o.coupon_code
                                        ORDER BY o.created_at DESC";
                        
                        $orderRes = mysqli_query($conn, $sql_grouped);

                        while ($o = mysqli_fetch_assoc($orderRes)) {
                            $list_ids = $o['list_ids'];
                            $first_id = explode(',', $list_ids)[0];
                            // Tạo mã đơn dựa trên thời gian để dễ phân biệt
                            $order_code = "DH-" . date("dmy-Hi", strtotime($o['created_at'])) . "-" . $first_id;

                            // TÍNH TOÁN LẠI TỔNG TIỀN THỰC TẾ: Tiền hàng trừ đi tiền giảm giá của Voucher
                            $grand_total = $o['total_goods_price'] - $o['total_discount'];
                            if ($grand_total < 0) $grand_total = 0; // Chặn tiền âm bảo vệ hệ thống

                            // Xác định màu sắc trạng thái
                            $status_style = "";
                            if ($o['status'] == 'delivered') $status_style = "background-color: #d4edda;";
                            else if ($o['status'] == 'cancelled') $status_style = "background-color: #f8d7da;";
                            else if ($o['status'] == 'waiting_confirm') $status_style = "background-color: #fff3cd;";

                            // Định dạng hiển thị nhãn Mã Giảm Giá trên bảng
                            $voucher_display = !empty($o['coupon_code']) 
                                ? "<span style='background:#fff3cd; color:#856404; padding:3px 6px; border-radius:4px; font-size:12px; font-weight:600;' title='Đã giảm ".number_format($o['total_discount'])."đ'>🎟️ {$o['coupon_code']}</span>" 
                                : "<span style='color:#aaa; font-size:13px;'>Không dùng</span>";
                        ?>
                            <tr>
                                <td><strong><?= $order_code ?></strong></td>
                                <td style="font-size: 12px;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                                <td><?= htmlspecialchars($o['fullname']) ?></td>
                                <td><?= $voucher_display ?></td> <td style="color: #e67e22; font-weight:bold;"><?= number_format($grand_total) ?>đ</td>
                                <td><?= strtoupper($o['payment_method']) ?></td>
                                <td>
                                    <select class="status-select" 
                                            style="<?= $status_style ?> padding: 5px; border-radius: 4px;"
                                            onchange="updateGroupStatus('<?= $list_ids ?>', this.value, this)">
                                        <option value="waiting_confirm" <?= $o['status'] == 'waiting_confirm' ? 'selected' : '' ?>>⏳ Chờ duyệt</option>
                                        <option value="confirmed" <?= $o['status'] == 'confirmed' ? 'selected' : '' ?>>✅ Xác nhận</option>
                                        <option value="in_transit" <?= $o['status'] == 'in_transit' ? 'selected' : '' ?>>🚚 Đang giao</option>
                                        <option value="delivered" <?= $o['status'] == 'delivered' ? 'selected' : '' ?>>🏁 Hoàn tất</option>
                                        <option value="cancelled" <?= $o['status'] == 'cancelled' ? 'selected' : '' ?>>❌ Hủy</option>
                                    </select>
                                </td>
                                <td>
                                    <button onclick="toggleDetails('details-<?= $first_id ?>')" class="btn-edit" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn-delete" onclick="deleteOrderGroup('<?= $list_ids ?>')" title="Xóa đơn">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr id="details-<?= $first_id ?>" style="display: none; background: #f9f9f9;">
                                <td colspan="8"> <div style="padding: 15px; border-left: 4px solid #e67e22; margin: 10px;">
                                        <h4 style="margin-bottom: 10px;">Sản phẩm trong đơn:</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <?php
                                            $sql_items = "SELECT p.product_name, o.quantity, o.total_price, o.size 
                                                        FROM orders o 
                                                        JOIN products p ON o.product_id = p.id 
                                                        WHERE o.id IN ($list_ids)";
                                            $res_items = mysqli_query($conn, $sql_items);
                                            while ($item = mysqli_fetch_assoc($res_items)) {
                                            ?>
                                                <tr style="border-bottom: 1px solid #ddd;">
                                                    <td style="padding: 8px;">• <?= htmlspecialchars($item['product_name']) ?></td>
                                                    <td style="padding: 8px;">Size: <strong><?= $item['size'] ?></strong></td>
                                                    <td style="padding: 8px;">x<?= $item['quantity'] ?></td>
                                                    <td style="padding: 8px; text-align: right;"><?= number_format($item['total_price']) ?>đ</td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ddd; text-align: right; font-size: 14px;">
                                            <div>Tiền hàng: <strong><?= number_format($o['total_goods_price']) ?>đ</strong></div>
                                            <div style="color: #c0392b;">Voucher giảm: <strong>-<?= number_format($o['total_discount']) ?>đ</strong></div>
                                            <div style="font-size: 16px; color: #e67e22; margin-top: 5px;">Thực thu: <strong><?= number_format($grand_total) ?>đ</strong></div>
                                        </div>
                                        <div style="margin-top: 10px; font-size: 13px; background: #fff; padding: 8px; border-radius: 4px; border: 1px solid #eee;">
                                            <strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($o['address']) ?> | <strong>SĐT:</strong> <?= $o['phone'] ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <section id="coupons" class="tab-content">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px;">
                    <h3>Quản Lý Mã Giảm Giá (Coupons)</h3>
                    <div style="display: flex; gap: 10px; flex: 1; justify-content: flex-end;">
                        <input type="text" id="searchCoupon" class="status-select" placeholder="🔍 Tìm mã voucher..." onkeyup="filterTable('coupons', 'searchCoupon')">
                        <button class="btn-main" onclick="openCouponModal()"><i class="fa-solid fa-plus"></i> Thêm Mã</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã Voucher</th>
                            <th>Mức Giảm</th>
                            <th>Đơn Tối Thiểu</th>
                            <th>Ngày Hết Hạn</th>
                            <th>Số lượng (Đã dùng/Tổng)</th> <th>Trạng Thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $couponRes = mysqli_query($conn, "SELECT * FROM coupons ORDER BY id DESC");
                        while ($row = mysqli_fetch_assoc($couponRes)) {
                            // Mã hóa dữ liệu dạng JSON để truyền vào hàm sửa dễ dàng
                            $couponJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                            
                            // Định dạng hiển thị trạng thái
                            $statusText = $row['status'] == 1 ? '🟢 Hoạt động' : '🔴 Khóa';
                            $statusStyle = $row['status'] == 1 ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;';
                            
                            // Kiểm tra nếu hết hạn thì đổi màu cảnh báo ngày
                            $isExpired = (strtotime($row['expiry_date']) < strtotime(date('Y-m-d')));
                            $dateStyle = $isExpired ? "color: red; font-weight: bold;" : "";
                            
                            // Đọc dữ liệu số lượng từ cơ sở dữ liệu để hiển thị trực quan
                            $usedCount = isset($row['used_count']) ? intval($row['used_count']) : 0;
                            $usageLimit = isset($row['usage_limit']) ? intval($row['usage_limit']) : 0;
                            
                            echo "<tr data-id='{$row['id']}'>
                                <td>#{$row['id']}</td>
                                <td><strong style='color:#e67e22;'>{$row['code']}</strong></td>
                                <td><strong>".number_format($row['discount_value'])."đ</strong></td>
                                <td>".number_format($row['min_order'])."đ</td>
                                <td style='{$dateStyle}'>".date('d/m/Y', strtotime($row['expiry_date']))." ".($isExpired ? '(Hết hạn)' : '')."</td>
                                
                                <td>
                                    <span style='font-weight: 600; color: #2c3e50;'>{$usedCount}</span> / <span style='color: #7f8c8d;'>{$usageLimit}</span>
                                    ".($usedCount >= $usageLimit ? " <small style='color: #e74c3c; font-weight:bold;'>(Hết lượt)</small>" : "")."
                                </td>
                                
                                <td><span style='padding: 5px 10px; border-radius: 4px; font-size: 12px; {$statusStyle}'>{$statusText}</span></td>
                                <td>
                                    <button class='btn-edit' onclick='editCoupon({$couponJson})'><i class='fa-solid fa-pen'></i></button>
                                    <button class='btn-delete' onclick='deleteCoupon({$row['id']})'><i class='fa-solid fa-trash'></i></button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        
            <div id="logs-tab" class="tab-content">
                <div class="card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; font-size: 18px; color: #2c3e50;">
                            <i class="fa-solid fa-timeline" style="color: #e67e22;"></i> Nhật Ký Hoạt Động Hệ Thống
                        </h3>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #edf2f7;">
                                    <th style="padding: 12px; color: #718096; font-size: 13px;">Thời Gian</th>
                                    <th style="padding: 12px; color: #718096; font-size: 13px;">Người Thực Hiện</th>
                                    <th style="padding: 12px; color: #718096; font-size: 13px;">Vai Trò</th>
                                    <th style="padding: 12px; color: #718096; font-size: 13px;">Hành Động</th>
                                    <th style="padding: 12px; color: #718096; font-size: 13px;">Nội Dung Chi Tiết</th>
                                </tr>
                            </thead>
                            
                            <tbody id="system-logs-body">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #718096;">
                                        <i class="fa-solid fa-arrows-rotate fa-spin"></i> Đang kết nối dữ liệu hệ thống hoạt động...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="productModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Thông Tin Sản Phẩm</h3>
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="prodId" name="id">
                
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" id="prodName" name="product_name" required>
                </div>

                <div class="form-group">
                    <label>Danh mục</label>
                    <select id="prodCategory" name="category">
                        <option value="Áo">Áo</option>
                        <option value="Quần">Quần</option>
                        <option value="Áo Khoác">Áo Khoác</option>
                        <option value="Phụ Kiện">Phụ Kiện</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex:1">
                        <label>Giá bán</label>
                        <input type="number" id="prodPrice" name="price" required>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label>Size (VD: S:10,M:5)</label>
                        <input type="text" id="prodSize" name="size" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Hình ảnh sản phẩm</label>
                    <div id="imagePreviewContainer" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;"></div>
                    
                    <input type="file" id="prodImages" name="product_images[]" accept="image/*" multiple onchange="previewNewImages(this)">
                    <input type="hidden" id="deletedImages" name="deleted_images" value="">
                </div>

                <div class="form-group">
                    <label>Mô tả sản phẩm</label>
                    <textarea id="prodDesc" name="description" rows="4"></textarea> 
                </div>

                <div class="btn-group">
                    <button type="button" class="btn-secondary" onclick="closeProductModal()" style="background: #6c757d; color:white; padding:10px; border-radius:5px; border:none; cursor:pointer;">Hủy</button>
                    <button type="submit" class="btn-main">Lưu dữ liệu</button>
                </div>
            </form>
        </div>
    </div>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <h3>Thông Tin Khách Hàng</h3>
            <form id="userForm">
                <input type="hidden" id="userId" name="id">
                
                <div class="form-group">
                    <label>Họ Tên</label>
                    <input type="text" id="userName" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="userEmail" name="email" required>
                </div>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="form-group">
                    <label>Vai trò hệ thống</label>
                    <select id="userRole" name="role" class="status-select" style="width: 100%; padding: 10px; border-radius: 8px;">
                        <option value="user">USER (Khách hàng)</option>
                        <option value="staff">STAFF (Nhân viên)</option>
                        <option value="admin">ADMIN (Quản trị viên)</option>
                    </select>
                </div>
                <?php else: ?>
                    <input type="hidden" id="userRole" name="role" value="user">
                <?php endif; ?>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" id="userPass" name="password" placeholder="Bỏ trống nếu không đổi">
                </div>

                <div class="btn-group">
                    <button type="button" class="btn-secondary" onclick="closeUserModal()" 
                        style="color: white; font-weight: 600; cursor: pointer; background: #6c757d; padding: 12px 28px; border-radius: 10px; border: none;">
                        Đóng
                    </button>
                    <button type="submit" class="btn-main">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <!-- modal giảm giá -->
    <div id="couponModal" class="modal">
        <div class="modal-content">
            <h3 id="couponModalTitle">Thông Tin Chiến Dịch Giảm Giá</h3>
            <form id="couponForm">
                <input type="hidden" id="couponId" name="id">
                
                <div class="form-group">
                    <label>Mã Voucher (Viết liền không dấu, VD: HUNO50)</label>
                    <input type="text" id="couponCode" name="code" placeholder="Ví dụ: CHAOSUMMER" required style="text-transform: uppercase;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex:1">
                        <label>Số tiền giảm (đ)</label>
                        <input type="number" id="couponDiscount" name="discount_value" placeholder="Ví dụ: 50000" required>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label>Giá trị đơn tối thiểu (đ)</label>
                        <input type="number" id="couponMinOrder" name="min_order" placeholder="Ví dụ: 200000" value="0" required>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex:1">
                        <label>Ngày hết hạn</label>
                        <input type="date" id="couponExpiry" name="expiry_date" required>
                    </div>
                    <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label style="font-weight: 600; color: #5d4037; display: block; margin-bottom: 5px;">Số lượng giới hạn (Lượt dùng)</label>
                            <input type="number" id="couponLimit" name="usage_limit" class="status-select" placeholder="Ví dụ: 50" min="1" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label style="font-weight: 600; color: #5d4037; display: block; margin-bottom: 5px;">Trạng thái mã</label>
                            <select id="couponStatus" name="status" class="status-select" style="width: 100%; padding: 10px; border-radius: 8px;">
                                <option value="1">Kích hoạt sử dụng</option>
                                <option value="0">Khóa / Tạm ngưng</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="btn-group" style="margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeCouponModal()" style="background: #6c757d; color:white; padding:12px 28px; border-radius:10px; border:none; cursor:pointer;">Hủy</button>
                    <button type="submit" class="btn-main">Lưu dữ liệu</button>
                </div>
            </form>
        </div>
    </div>




    <script>
        // Ép đăng ký showTab trực tiếp vào bộ nhớ gốc toàn cục (window)
        // Kỹ thuật này giúp bảo vệ hàm, dù HTML phía trên có lỗi cấu trúc thì hàm VẪN CHẠY ĐƯỢC
        window.showTab = function(event, tabId) {
            if (event) {
                event.preventDefault(); // Ngăn chặn hành vi giật màn hình
            }
            
            console.log("HUNO System - Đang chuyển sang tab: " + tabId);

            // 1. Ẩn tất cả các nội dung tab đang hiển thị
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.remove('active');
            });
            
            // 2. Gỡ bỏ trạng thái active trên tất cả các nút menu sidebar
            document.querySelectorAll('.sidebar .menu li').forEach(l => {
                l.classList.remove('active');
            });
            
            // 3. Hiển thị khối nội dung được chọn
            const targetTab = document.getElementById(tabId);
            if (targetTab) {
                targetTab.classList.add('active');
            } else {
                console.error("Lỗi: Không tìm thấy tab nào có ID là: " + tabId);
            }
            
            // 4. Đổi màu nổi bật cho nút menu vừa click vào
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active');
            }
        };

        // Hàm ẩn/hiện chi tiết đơn hàng
        window.toggleDetails = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = (el.style.display === 'none') ? 'table-row' : 'none';
            }
        };

        // Hàm xuất Excel
        window.Excel = function() {
            const dateInput = document.getElementById('filterDate');
            const dateValue = dateInput ? dateInput.value : '';
            window.location.href = `php/export_excel.php?date=${dateValue}`;
        };
        window.xuatExcel = window.Excel;

        // Đảm bảo khi tải trang xong, tab đầu tiên (dashboard) sẽ hiển thị chuẩn
        document.addEventListener("DOMContentLoaded", function() {
            const defaultTab = document.getElementById('dashboard');
            if (defaultTab && !document.querySelector('.tab-content.active')) {
                defaultTab.classList.add('active');
            }
        });
    </script>
    <script src="js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>