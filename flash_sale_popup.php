<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. Ép hệ thống PHP chạy đúng giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

include 'php/datk.php'; 

// 2. Lấy chiến dịch đang KÍCH HOẠT (status = 1) mới nhất, không lọc thời gian bằng SQL để tránh lỗi lệch múi giờ server
$sql_check = "SELECT * FROM flash_sales WHERE status = 1 ORDER BY id DESC LIMIT 1";
$res_check = mysqli_query($conn, $sql_check);
$config = mysqli_fetch_assoc($res_check);

// Nếu không có chiến dịch nào được bật từ Admin -> Ẩn popup
if (!$config) {
    return;
}

// 3. Thực hiện kiểm tra thời gian thực chính xác bằng PHP 
$now = time();
$start = intval($config['start_time']);
$end = intval($config['end_time']);

// Nếu thời gian hiện tại nằm ngoài khung giờ đã set bên Admin -> Ẩn popup
if ($now < $start || $now > $end) {
    return; 
}

// Tính số giây đếm ngược chuẩn xác đến lúc kết thúc sự kiện
$countdown_seconds = $end - $now;
$voucher_code = $config['voucher_code'];
$banner_url = "img/banner/flash_sale_hot.jpg"; 
?>

<div id="flash-sale-sticky" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #fff; border: 2px solid #e74c3c; border-radius: 12px; padding: 12px; width: 220px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); font-family: Arial, sans-serif; cursor: pointer; display: none;" onclick="triggerFlashSaleAction()">
    <div style="background: #e74c3c; color: #fff; text-align: center; font-weight: bold; font-size: 12px; padding: 4px; border-radius: 6px; margin-bottom: 8px; letter-spacing: 0.5px;">
        🔥 FLASH SALE ĐANG DIỄN RA
    </div>
    
    <div style="text-align: center; margin-bottom: 8px; font-size: 13px; color: #333; font-weight: bold; background: #fff9f8; border: 1px dashed #e74c3c; padding: 5px; border-radius: 6px;">
        Nhập mã: <span style="color: #e74c3c; font-size: 14px; font-weight: 900;"><?php echo htmlspecialchars($voucher_code); ?></span>
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-size: 12px; color: #666; font-weight: bold;">Kết thúc sau:</span>
        <span id="flash-countdown" style="font-size: 16px; color: #e74c3c; font-weight: bold; font-family: monospace;">00:00</span>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const REMAINING_SECONDS = <?php echo $countdown_seconds; ?>; 
    let now = Math.floor(Date.now() / 1000);
    let flashExpiry = now + REMAINING_SECONDS;

    const timerBox = document.getElementById('flash-countdown');
    const stickyWidget = document.getElementById('flash-sale-sticky');
    
    stickyWidget.style.display = 'block';

    const interval = setInterval(function() {
        let currentNow = Math.floor(Date.now() / 1000);
        let remaining = flashExpiry - currentNow;

        if (remaining <= 0) {
            clearInterval(interval);
            timerBox.innerText = "HẾT HẠN!";
            stickyWidget.style.border = '2px solid #7f8c8d';
            stickyWidget.querySelector('div').style.background = '#7f8c8d';
            setTimeout(() => { stickyWidget.style.display = 'none'; }, 3000);
        } else {
            let minutes = Math.floor(remaining / 60);
            let seconds = remaining % 60;
            timerBox.innerText = 
                (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                (seconds < 10 ? "0" + seconds : seconds);
        }
    }, 1000);
});

async function triggerFlashSaleAction() {
    try {
        let resCampaign = await fetch('php/ajax_flash_sale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=apply_combo'
        });
        
        let data = await resCampaign.json();
        
        if (!data.success) {
            alert(data.error || "Có lỗi xảy ra!");
            return;
        }

        let addSuccessCount = 0;
        for (let item of data.products) {
            let fd = new FormData();
            fd.append('product_id', item.product_id);
            fd.append('size_selected', item.size);
            fd.append('quantity', 1);

            let resAdd = await fetch('php/add_to_cart_detailed.php', { method: 'POST', body: fd });
            let resultText = await resAdd.text();
            if (resultText.trim() === 'success') {
                addSuccessCount++;
            }
        }

        if (addSuccessCount > 0) {
            alert(`🎉 Đã tự động gom thành công ${addSuccessCount} sản phẩm vào giỏ hàng!\n👉 Hãy nhớ nhập mã giảm giá hiển thị trên bong bóng để được ưu đãi nhé!`);
            window.location.href = 'cart.php';
        } else {
            alert("Không thể gom hàng tự động, sản phẩm có thể đã hết!");
        }

    } catch (err) {
        console.error("Lỗi:", err);
        alert("Vui lòng đăng nhập trước khi săn deal!");
    }
}
</script>