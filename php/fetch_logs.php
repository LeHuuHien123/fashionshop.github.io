<?php
session_start();
include 'datk.php'; // Đảm bảo tệp kết nối cơ sở dữ liệu của bạn nằm cùng cấp hoặc đúng đường dẫn

// Bảo mật: Chỉ cho phép tài khoản Admin hoặc Staff đã đăng nhập truy cập lấy dữ liệu
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    exit('<tr><td colspan="5" style="text-align:center; padding:20px; color:#e74c3c;">Bạn không có quyền truy cập dữ liệu nhật ký!</td></tr>');
}

// Câu lệnh truy vấn SQL lấy danh sách logs kèm thông tin chi tiết người thực hiện
$log_sql = "SELECT l.*, u.fullname, u.role 
            FROM activity_logs l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC 
            LIMIT 100";

$log_result = mysqli_query($conn, $log_sql);

if ($log_result && mysqli_num_rows($log_result) > 0) {
    while ($log = mysqli_fetch_assoc($log_result)) {
        
        // Phân loại màu sắc Badge dựa theo Vai trò (Role)
        $role_badge = $log['role'] === 'admin' 
            ? '<span style="background: #fde8e8; color: #e53e3e; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">Admin</span>' 
            : '<span style="background: #e1f5fe; color: #0288d1; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">Staff</span>';
        
        // Phân biệt màu sắc văn bản dựa vào hành động (Thêm/Xóa)
        $action_color = '#2c3e50';
        if (mb_strpos($log['action'], 'Xóa') !== false) {
            $action_color = '#e53e3e';
        } elseif (mb_strpos($log['action'], 'Thêm') !== false) {
            $action_color = '#38a169';
        }
        ?>
        <tr style="border-bottom: 1px solid #edf2f7; font-size: 13.5px;">
            <td style="padding: 12px; color: #4a5568;">
                <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
            </td>
            <td style="padding: 12px; font-weight: 600; color: #2d3748;">
                <?php echo htmlspecialchars($log['fullname']); ?>
            </td>
            <td style="padding: 12px;"><?php echo $role_badge; ?></td>
            <td style="padding: 12px; font-weight: 600; color: <?php echo $action_color; ?>;">
                <?php echo htmlspecialchars($log['action']); ?>
            </td>
            <td style="padding: 12px; color: #4a5568; max-width: 400px; word-wrap: break-word;">
                <?php echo htmlspecialchars($log['target']); ?>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding: 30px; color: #a0aec0;'>Chưa có hoạt động nào được ghi lại!</td></tr>";
}
?>