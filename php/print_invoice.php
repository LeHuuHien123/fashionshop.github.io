<?php
session_start();
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

// 1. LẤY THÔNG TIN CHUNG (Từ bảng orders gốc có sẵn của bạn)
$sql_info = "SELECT address, phone, created_at FROM orders WHERE id IN ($ids) LIMIT 1";
$res_info = mysqli_query($conn, $sql_info);
$info = mysqli_fetch_assoc($res_info);

if (!$info) {
    die("Đơn hàng không tồn tại.");
}

// 2. TÍNH TỔNG TIỀN (Từ bảng orders gốc)
$sql_total = "SELECT SUM(total_price) as total_goods_price, SUM(discount_amount) as total_discount 
              FROM orders 
              WHERE id IN ($ids)";
$res_total = mysqli_query($conn, $sql_total);
$total_data = mysqli_fetch_assoc($res_total);

$total_goods_price = $total_data['total_goods_price'] ?? 0;
$total_discount = $total_data['total_discount'] ?? 0;
$grand_total = $total_goods_price - $total_discount;

// Tạo mã đơn hàng đồng bộ theo thời gian lưu trữ
$first_id = explode(',', $ids)[0];
$order_code = "DH-" . date("dmy-Hi", strtotime($info['created_at'])) . "-" . $first_id;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn bán hàng - HUNO</title>
    <style>
        @page { size: 80mm auto; margin: 0mm; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; padding: 10mm 5mm; line-height: 1.4; background: #fff; margin: 0; }
        .invoice-box { width: 100%; margin: auto; }
        .header { border-bottom: 1px dashed #333; padding-bottom: 8px; margin-bottom: 15px; }
        .company-info h2 { margin: 0 0 5px 0; font-size: 16px; color: #000; letter-spacing: 1px; }
        .company-info p, .invoice-details p { margin: 3px 0; font-size: 12px; }
        .invoice-details { margin-top: 10px; border-top: 1px dotted #ccc; padding-top: 5px; text-align: right; }
        .invoice-details h3 { margin: 5px 0; font-size: 15px; }
        .table-products { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; }
        .table-products th, .table-products td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; }
        .table-products th { background-color: #f5f5f5; }
        .total-section { text-align: right; margin-top: 15px; font-size: 13px; line-height: 1.5; }
        .total-section strong { font-size: 14px; color: #e67e22; }
        @media print { .no-print { display: none; } body { padding: 5mm 2mm; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="no-print" style="margin-bottom: 15px; text-align: right;">
            <button onclick="window.print();" style="padding: 8px 15px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ In Hóa Đơn</button>
            <button onclick="window.close();" style="padding: 8px 15px; background: #7f8c8d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 5px;">Đóng</button>
        </div>

        <div class="header">
            <div class="company-info">
                <h2>HUNO FASHION</h2>
                <p>Địa chỉ: Đường 3/2, Tp. Cà Mau</p>
                <p>Hotline: 09xx xxx xxx</p>
            </div>
            <div class="invoice-details">
                <h3>HÓA ĐƠN BÁN HÀNG</h3>
                <p><strong>Mã đơn:</strong> <?= $order_code ?></p>
                <p>Ngày in: <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($info['phone']) ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($info['address']) ?></p>

        <table class="table-products">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align: center;">Size</th>
                    <th style="text-align: center;">SL</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // TỐI ƯU MỚI: Lấy danh sách sản phẩm từ bảng chi tiết order_details liên kết với bảng products
                $sql_items = "SELECT p.product_name, od.quantity, (od.price * od.quantity) as item_total, od.size 
                              FROM order_details od 
                              JOIN products p ON od.product_id = p.id 
                              WHERE od.order_id IN ($ids)";
                $res_items = mysqli_query($conn, $sql_items);
                
                while ($item = mysqli_fetch_assoc($res_items)) {
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td style="text-align: center;"><strong><?= htmlspecialchars($item['size']) ?></strong></td>
                        <td style="text-align: center;">x<?= $item['quantity'] ?></td>
                        <td style="text-align: right;"><?= number_format($item['item_total']) ?>đ</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div>Tiền hàng: <?= number_format($total_goods_price) ?>đ</div>
            <div style="color: #c0392b;">Voucher giảm: -<?= number_format($total_discount) ?>đ</div>
            <div style="margin-top: 5px; border-top: 1px dashed #333; padding-top: 5px;">
                Thực thu: <strong><?= number_format($grand_total) ?>đ</strong>
            </div>
        </div>
    </div>
    <script>window.onload = function() { setTimeout(function() { window.print(); }, 300); }</script>
</body>
</html>