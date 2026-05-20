<?php
session_start();
include 'php/datk.php';

$res = mysqli_query($conn, "SELECT * FROM services ORDER BY id DESC");
$services_data = [];

while ($row = mysqli_fetch_assoc($res)) {
    if (empty($row['image'])) {
        $row['image'] = "https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=800";
    }
    $services_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dịch vụ | Golden Feast</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="css/dungchung.css">
    <link rel="stylesheet" href="css/booking.css">
    <link rel="stylesheet" href="css/services.css">

    <!-- Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <?php include 'chat_bubble.php'; ?>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
        <div class="logo">Golden<span>Feast</span></div>
        <ul class="nav-links">
            <li><a href="index.php">Trang Chủ</a></li>
            <li><a href="services.php">Dịch Vụ</a></li>
            <li><a href="booking.php">Phòng Tiệc</a></li>
            <li><a href="evaluate.php">Đánh giá</a></li>
            <li><a href="about.html">Giới Thiệu</a></li>
            <li><a href="evaluate.php">Đánh giá</a></li>
        </ul>
        <div class="nav-btns" id="navBtns">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    <span class="welcome-msg">Chào,</span>
                    <span class="user-name-display"><?php echo $_SESSION['fullname']; ?></span>
                </div>
                <a href="php/logout.php" class="btn-login" style="margin-left:10px">Thoát</a>
            <?php else: ?>
                <a href="login.html" class="btn-login">Đăng nhập</a>
            <?php endif; ?>
            <a href="booking.php" class="btn-main">Đặt Tiệc Ngay</a>
        </div>
    </nav>
<?php include 'flash_sale_popup.php'; ?>

<!-- ===== PAGE ===== -->
<div class="booking-page">

    <!-- ===== SIDEBAR (GIỐNG BOOKING) ===== -->
    <aside class="filter-sidebar">
        <h3>Lọc dịch vụ</h3>

        <div class="filter-group">
            <label>Tên dịch vụ</label>
            <input type="text" id="searchService" placeholder="Nhập tên dịch vụ">
        </div>

        <div class="filter-group">
            <label>Loại dịch vụ</label>
            <select id="filterServiceType">
                <option value="all">Tất cả</option>
                <option value="Trang trí">Trang trí</option>
                <option value="Thực đơn">Thực đơn</option>
                <option value="Âm thanh">Âm thanh & Ánh sáng</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Giá tối đa: <b id="priceValue">20</b> triệu</label>
            <input type="range" id="filterServicePrice" min="0.5" max="20" step="0.5" value="20">
        </div>

        <button id="resetServiceFilter" class="btn-reset">Làm mới</button>
    </aside>

    <!-- ===== CONTENT ===== -->
    <section class="booking-content">
        <div id="serviceContainer" class="service-grid"></div>
    </section>

</div>

<!-- DATA + JS -->
<script>
    const allServices = <?php echo json_encode($services_data); ?>;
</script>
<script src="js/dungchung.js"></script>
<script src="js/services_display.js"></script>

</body>
</html>
    