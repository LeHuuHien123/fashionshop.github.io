<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
include 'datk.php'; 

$response = ['success' => false];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Vui lòng đăng nhập để tham gia săn deal Flash Sale!']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'apply_combo') {
    $sql_campaign = "SELECT id FROM flash_sales WHERE status = 1 ORDER BY id DESC LIMIT 1";
    $res_campaign = mysqli_query($conn, $sql_campaign);
    $campaign = mysqli_fetch_assoc($res_campaign);

    if (!$campaign) {
        echo json_encode(['success' => false, 'error' => 'Không tìm thấy chiến dịch nào!']);
        exit();
    }

    $flash_sale_id = $campaign['id'];

    $sql_get_products = "SELECT fsp.product_id, p.size 
                         FROM flash_sale_products fsp
                         JOIN products p ON fsp.product_id = p.id
                         WHERE fsp.flash_sale_id = $flash_sale_id";
    
    $res_products = mysqli_query($conn, $sql_get_products);
    $products_data = [];

    if ($res_products && mysqli_num_rows($res_products) > 0) {
        while ($product = mysqli_fetch_assoc($res_products)) {
            $p_size = 'M'; 
            if (!empty($product['size'])) {
                $sizes = explode(',', $product['size']);
                if (count($sizes) > 0) {
                    $parts = explode(':', trim($sizes[0]));
                    $p_size = trim($parts[0]);
                }
            }
            $products_data[] = [
                'product_id' => $product['product_id'],
                'size' => $p_size
            ];
        }
    }

    if (count($products_data) > 0) {
        $response['success'] = true;
        $response['products'] = $products_data;
    } else {
        $response['error'] = 'Chiến dịch trống sản phẩm!';
    }
}

echo json_encode($response);
exit();