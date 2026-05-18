<?php
include 'datk.php'; // File kết nối database của bạn
header('Content-Type: application/json; charset=utf-8');

// Tắt báo lỗi hiển thị HTML để tránh làm gãy cấu trúc JSON trả về
error_reporting(0);
ini_set('display_errors', 0);

// Nhận tham số ngày từ giao diện (nếu có)
$filterDate = isset($_GET['date']) ? trim($_GET['date']) : null;

$response = [
    'stats' => ['revenue' => 0, 'sold' => 0],
    'bar' => ['labels' => [], 'data' => []],
    'pie' => ['labels' => [], 'data' => []],
    'line'=> ['labels' => [], 'data' => []] // Thêm mảng chứa dữ liệu biểu đồ đường
];

// =========================================================================
// 1. Lấy Thống kê tổng quát (Doanh thu thực tế & Tổng sản phẩm đã bán)
// Chỉ tính những đơn hàng đã giao thành công (delivered)
// =========================================================================
$statQuery = "SELECT SUM(total_price) as raw_revenue, SUM(discount_amount) as total_discount, SUM(quantity) as sold 
              FROM orders 
              WHERE status = 'delivered'";

if ($filterDate) {
    $safeDate = mysqli_real_escape_string($conn, $filterDate);
    $statQuery .= " AND DATE(created_at) = '$safeDate'";
}

$statRes = mysqli_query($conn, $statQuery);
if ($statRes && mysqli_num_rows($statRes) > 0) {
    $statRow = mysqli_fetch_assoc($statRes);
    
    // Doanh thu thực tế = Tổng tiền gốc - Tổng số tiền giảm giá
    $realRevenue = (int)$statRow['raw_revenue'] - (int)$statRow['total_discount'];
    $response['stats']['revenue'] = $realRevenue > 0 ? $realRevenue : 0;
    $response['stats']['sold'] = (int)$statRow['sold'];
}

// =========================================================================
// 2. Lấy dữ liệu cho BIỂU ĐỒ CỘT (Doanh thu 7 ngày gần đây bao gồm cả ngày lọc)
// =========================================================================
$baseDate = $filterDate ? $filterDate : date('Y-m-d');
$barLabels = [];
$barData = [];

for ($i = 0; $i < 7; $i++) {
    $dateStr = date('Y-m-d', strtotime("$baseDate -$i days"));
    $barLabels[] = date('d/m', strtotime($dateStr));
    
    $safeDateStr = mysqli_real_escape_string($conn, $dateStr);
    $barQuery = "SELECT SUM(total_price) as raw_rev, SUM(discount_amount) as total_disc 
                 FROM orders 
                 WHERE status = 'delivered' AND DATE(created_at) = '$safeDateStr'";
    
    $barRes = mysqli_query($conn, $barQuery);
    if ($barRes && $r = mysqli_fetch_assoc($barRes)) {
        $dailyRev = (int)$r['raw_rev'] - (int)$r['total_disc'];
        $barData[] = $dailyRev > 0 ? $dailyRev : 0;
    } else {
        $barData[] = 0;
    }
}

$response['bar']['labels'] = array_reverse($barLabels);
$response['bar']['data'] = array_reverse($barData);

// =========================================================================
// 3. Lấy dữ liệu cho BIỂU ĐỒ TRÒN (Top 5 sản phẩm mua nhiều nhất)
// =========================================================================
$pieQuery = "SELECT p.product_name, SUM(o.quantity) as total_qty 
             FROM orders o 
             JOIN products p ON o.product_id = p.id \r\n             WHERE o.status = 'delivered' ";

if ($filterDate) {
    $safeDate = mysqli_real_escape_string($conn, $filterDate);
    $pieQuery .= " AND DATE(o.created_at) = '$safeDate' ";
}

$pieQuery .= " GROUP BY o.product_id \r\n               ORDER BY total_qty DESC LIMIT 5";

$pieRes = mysqli_query($conn, $pieQuery);
if ($pieRes) {
    while ($pRow = mysqli_fetch_assoc($pieRes)) {
        $response['pie']['labels'][] = $pRow['product_name'];
        $response['pie']['data'][] = (int)$pRow['total_qty'];
    }
}

// =========================================================================
// 4. ĐỒNG BỘ MẢNG CHO BIỂU ĐỒ ĐƯỜNG (LINE CHART) XU HƯỚNG 7 NGÀY
// =========================================================================
$response['line']['labels'] = $response['bar']['labels'];
$response['line']['data']   = $response['bar']['data'];

// Xuất chuỗi dữ liệu JSON sạch
echo json_encode($response);
exit();
?>