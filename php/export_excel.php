<?php
session_start();
include 'datk.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra quyền truy cập an toàn
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../index.php");
    exit();
}

// 1. Lấy ngày được truyền sang từ bộ lọc URL (nếu có)
$selected_date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';
$fileNameSuffix = !empty($selected_date) ? '_' . $selected_date : '_Tat_Ca';

// 2. Cấu hình Header gửi về trình duyệt ép định dạng mở bằng Microsoft Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Bao_Cao_Doanh_Thu_HUNO" . $fileNameSuffix . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Lấy dữ liệu từ Database theo bộ lọc ngày
$sql = "SELECT 
            o.id, 
            o.created_at, 
            u.fullname, 
            o.phone, 
            o.address, 
            o.payment_method, 
            o.coupon_code, 
            o.discount_amount, 
            o.total_price, 
            o.status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.status = 'delivered'"; //

if (!empty($selected_date)) {
    $sql .= " AND DATE(o.created_at) = '$selected_date'"; //
}
$sql .= " ORDER BY o.created_at DESC"; //

$result = mysqli_query($conn, $sql);
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" 
      xmlns:x="urn:schemas-microsoft-com:office:excel" 
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        /* Định dạng phong cách thiết kế bảng tính chuẩn phòng kế toán */
        body { font-family: 'Times New Roman', Times, serif; }
        .table-excel { border-collapse: collapse; width: 100%; }
        .table-excel th { 
            background-color: #1e3d59; /* Màu xanh Navy đậm cực sang trọng */
            color: #ffffff; 
            font-weight: bold; 
            text-align: center; 
            vertical-align: middle;
            border: 1px solid #000000;
            height: 35px;
            font-size: 13px;
        }
        .table-excel td { 
            border: 1px solid #d3d3d3; /* Viền xám mảnh bao quanh từng ô */
            padding: 6px; 
            vertical-align: middle;
            font-size: 12px;
        }
        /* Định dạng căn lề theo đặc thù dữ liệu */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        /* Ép kiểu dữ liệu chuỗi (String) để Excel không tự ý cắt mất số 0 ở đầu SĐT */
        .string-format { mso-number-format:"\@"; }
        /* Ép kiểu định dạng hiển thị tiền tệ VNĐ phân tách hàng nghìn */
        .currency-format { mso-number-format:"\#\,\#\#0\ \₫"; }
    </style>
</head>
<body>

    <div style="text-align: center; margin-bottom: 15px;">
        <h2>BÁO CÁO THỐNG KÊ DOANH THU CỬA HÀNG HUNO SHOP</h2>
        <?php if(!empty($selected_date)): ?>
            <p><i>Ngày áp dụng báo cáo: <?php echo date('d/m/Y', strtotime($selected_date)); ?></i></p>
        <?php else: ?>
            <p><i>Phạm vi báo cáo: Toàn bộ dữ liệu hệ thống</i></p>
        <?php endif; ?>
        <p style="font-size: 11px; color: #555;">Thời gian xuất file: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <table class="table-excel">
        <thead>
            <tr>
                <th style="width: 150px;">Mã Đơn Hàng</th>
                <th style="width: 130px;">Ngày Đặt</th>
                <th style="width: 160px;">Tên Khách Hàng</th>
                <th style="width: 110px;">Số Điện Thoại</th>
                <th style="width: 250px;">Địa Chỉ Giao Hàng</th>
                <th style="width: 110px;">PT Thanh Toán</th>
                <th style="width: 100px;">Mã Giảm Giá</th>
                <th style="width: 90px;">Số Tiền Giảm</th>
                <th style="width: 110px;">Tổng Tiền Hàng</th>
                <th style="width: 110px;">Doanh Thu Thực Thu</th>
                <th style="width: 95px;">Trạng Thái Đơn</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_all_goods = 0;
            $total_all_discount = 0;
            $total_all_revenue = 0;

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Logic tính toán đồng bộ
                    $totalGoodsPrice = (int)$row['total_price'];
                    $discountAmount = (int)$row['discount_amount'];
                    $actualRevenue = $totalGoodsPrice - $discountAmount;
                    if ($actualRevenue < 0) $actualRevenue = 0;

                    // Cộng dồn để tính tổng kết cuối dòng file
                    $total_all_goods += $totalGoodsPrice;
                    $total_all_discount += $discountAmount;
                    $total_all_revenue += $actualRevenue;

                    $statusText = ($row['status'] == 'delivered') ? 'Hoàn tất' : $row['status']; //
                    $order_code = "DH-" . date("dmy-Hi", strtotime($row['created_at'])) . '-' . $row['id']; //
                    ?>
                    <tr>
                        <td class="text-center"><b><?php echo $order_code; ?></b></td>
                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        <td class="text-left"><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td class="text-center string-format"><?php echo $row['phone']; ?></td>
                        <td class="text-left"><?php echo htmlspecialchars($row['address']); ?></td>
                        <td class="text-center"><?php echo strtoupper($row['payment_method']); ?></td>
                        <td class="text-center">
                            <?php echo !empty($row['coupon_code']) ? $row['coupon_code'] : '---'; ?>
                        </td>
                        <td class="text-right currency-format" x:num="<?php echo $discountAmount; ?>"><?php echo $discountAmount; ?></td>
                        <td class="text-right currency-format" x:num="<?php echo $totalGoodsPrice; ?>"><?php echo $totalGoodsPrice; ?></td>
                        <td class="text-right currency-format" x:num="<?php echo $actualRevenue; ?>" style="font-weight: bold; color: #27ae60; background-color: #f4faf6;">
                            <?php echo $actualRevenue; ?>
                        </td>
                        <td class="text-center" style="color: #27ae60; font-weight: 500;"><?php echo $statusText; ?></td>
                    </tr>
                    <?php
                }
                
                // HÀNG TỔNG KẾT CUỐI FILE (Tổng cộng doanh thu)
                ?>
                <tr style="background-color: #eaeeef; font-weight: bold;">
                    <td colspan="7" class="text-right" style="height: 28px; font-size: 13px;">TỔNG CỘNG HỆ THỐNG:</td>
                    <td class="text-right currency-format" x:num="<?php echo $total_all_discount; ?>"><?php echo $total_all_discount; ?></td>
                    <td class="text-right currency-format" x:num="<?php echo $total_all_goods; ?>"><?php echo $total_all_goods; ?></td>
                    <td class="text-right currency-format" x:num="<?php echo $total_all_revenue; ?>" style="color: #c0392b; font-size: 13px;">
                        <?php echo $total_all_revenue; ?>
                    </td>
                    <td></td>
                </tr>
                <?php
            } else {
                echo "<tr><td colspan='11' class='text-center' style='color:red; padding:20px;'>Không tìm thấy dữ liệu đơn hàng nào thỏa mãn điều kiện lọc!</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>
<?php
exit();
?>