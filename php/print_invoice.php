<?php
session_start();

// Đảm bảo file kết nối database của bạn chính xác
include 'datk.php'; 

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    die("Quyền truy cập bị từ chối!");
}

$ids = $_GET['ids'] ?? '';
if (empty($ids)) {
    die("Không tìm thấy đơn hàng.");
}

// Bảo mật chuỗi ids, chỉ cho phép số và dấu phẩy
$ids = preg_replace('/[^0-9,]/', '', $ids);
if (empty($ids)) {
    die("Mã đơn hàng không hợp lệ.");
}

// 1. Lấy thông tin chung và tính TỔNG TIỀN của toàn bộ đơn hàng trong nhóm, kèm theo thời gian đặt hàng
$sql_order = "SELECT address, phone, created_at,
                     SUM(total_price) as total_goods_price, 
                     SUM(discount_amount) as total_discount 
              FROM orders 
              WHERE id IN ($ids)";

$res_order = mysqli_query($conn, $sql_order);

if (!$res_order) {
    die("<b>Lỗi truy vấn SQL (Thông tin đơn):</b> " . mysqli_error($conn));
}

$order_info = mysqli_fetch_assoc($res_order);

if (!$order_info || empty($order_info['address'])) {
    die("Đơn hàng không tồn tại trên hệ thống.");
}

// Tính số tiền thực thu chuẩn từ tổng tính được
$grand_total = $order_info['total_goods_price'] - $order_info['total_discount'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn bán hàng - HUNO</title>
    <style>
        /* CẤU HÌNH KHÓA CỨNG KHỔ GIẤY IN THEO NỘI DUNG (Khổ K80 hoặc hóa đơn mini) */
        @page {
            size: 80mm auto; /* Chiều rộng cố định 80mm, chiều cao TỰ ĐỘNG kéo giãn theo nội dung */
            margin: 0mm;     /* Triệt tiêu hoàn toàn header/footer mặc định của trình duyệt */
        }

        body { 
            font-family: Arial, sans-serif; 
            font-size: 13px; 
            color: #333; 
            padding: 10mm 5mm; 
            line-height: 1.4; 
            background: #fff;
            margin: 0;
        }
        
        .invoice-box { width: 100%; margin: auto; }
        
        .header { 
            border-bottom: 1px dashed #333; 
            padding-bottom: 8px; 
            margin-bottom: 15px; 
        }
        .company-info h2 { margin: 0 0 5px 0; font-size: 16px; color: #000; letter-spacing: 1px; }
        .company-info p, .invoice-details p { margin: 3px 0; font-size: 12px; }
        .invoice-details { margin-top: 10px; border-top: 1px dotted #ccc; padding-top: 5px; }
        .invoice-details h3 { margin: 5px 0; font-size: 15px; }

        .table-products { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; }
        .table-products th, .table-products td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; }
        .table-products th { background-color: #f5f5f5; }
        
        .total-section { text-align: right; margin-top: 15px; font-size: 13px; line-height: 1.5; }
        .total-section strong { font-size: 14px; color: #e67e22; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 5mm 2mm; } 
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="no-print" style="margin-bottom: 15px; text-align: right;">
            <button onclick="window.print();" style="padding: 8px 15px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ Tiến hành In / Lưu PDF</button>
            <button onclick="window.close();" style="padding: 8px 15px; background: #7f8c8d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 5px;">Đóng cửa sổ</button>
        </div>

        <div class="header">
            <div class="company-info">
                <h2>HUNO FASHION</h2>
                <p>Địa chỉ: Đường 3/2, Tp. Cà Mau</p>
                <p>Hotline: 09xx xxx xxx</p>
            </div>
            <div class="invoice-details" style="text-align: right;">
                <h3>HÓA ĐƠN BÁN HÀNG</h3>
                <?php 
                    // SỬA LỖI WARNING: Khởi tạo chuẩn biến lấy ID đầu tiên và tạo mã dựa trên $order_info
                    $first_id = explode(',', $ids)[0]; 
                    $order_code = "DH-" . date("dmy-Hi", strtotime($order_info['created_at'])) . "-" . $first_id;
                ?>
                <p><strong>Mã đơn:</strong> <?= $order_code ?></p>
                <p>Ngày in: <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <p><strong>Số điện thoại khách hàng:</strong> <?= htmlspecialchars($order_info['phone']) ?></p>
        <p><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($order_info['address']) ?></p>

        <table class="table-products">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align: center;">Size</th>
                    <th style="text-align: center;">Số lượng</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 2. Lấy danh sách sản phẩm thuộc nhóm đơn hàng chính xác từ DB
                $sql_items = "SELECT p.product_name, o.quantity, o.total_price, o.size 
                              FROM orders o 
                              JOIN products p ON o.product_id = p.id 
                              WHERE o.id IN ($ids)";
                $res_items = mysqli_query($conn, $sql_items);
                
                if (!$res_items) {
                    die("<tr><td colspan='4'><b>Lỗi truy vấn SQL (Sản phẩm):</b> " . mysqli_error($conn) . "</td></tr>");
                }
                
                while ($item = mysqli_fetch_assoc($res_items)) {
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td style="text-align: center;"><strong><?= htmlspecialchars($item['size']) ?></strong></td>
                        <td style="text-align: center;">x<?= $item['quantity'] ?></td>
                        <td style="text-align: right;"><?= number_format($item['total_price']) ?>đ</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div>Tiền hàng: <?= number_format($order_info['total_goods_price']) ?>đ</div>
            <div style="color: #c0392b;">Voucher giảm: -<?= number_format($order_info['total_discount']) ?>đ</div>
            <div style="margin-top: 5px; border-top: 1px dashed #333; padding-top: 5px;">
                Thực thu: <strong><?= number_format($grand_total) ?>đ</strong>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 300);
        }
    </script>
</body>
</html>