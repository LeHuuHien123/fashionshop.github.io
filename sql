CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL, -- Hành động (Ví dụ: 'Cập nhật đơn hàng', 'Xóa sản phẩm')
  `target` TEXT NOT NULL,         -- Nội dung chi tiết hành động
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,           -- Cột này sẽ lưu ID từ bảng orders của bạn
  `product_id` INT NOT NULL,
  `size` VARCHAR(10) NOT NULL,
  `quantity` INT NOT NULL,
  `price` INT NOT NULL                -- Giá gốc lúc mua (bằng cột total_price / quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- 1. Bảng lưu thông tin chung của sự kiện Flash Sale
CREATE TABLE IF NOT EXISTS `flash_sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `start_time` INT NOT NULL,              -- Lưu thời gian bắt đầu dạng TIMESTAMP UNIX
  `end_time` INT NOT NULL,                -- Lưu thời gian kết thúc dạng TIMESTAMP UNIX
  `voucher_code` VARCHAR(50) NOT NULL,    -- Mã voucher áp dụng
  `status` TINYINT DEFAULT 1,             -- 1: Hoạt động, 0: Khóa
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Bảng liên kết các sản phẩm thuộc Bộ sưu tập Flash Sale đó (Mối quan hệ 1-nhiều)
CREATE TABLE IF NOT EXISTS `flash_sale_products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `flash_sale_id` INT NOT NULL,           -- Khóa phụ nối tới bảng flash_sales
  `product_id` INT NOT NULL,              -- ID sản phẩm được tích chọn checkbox
  FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;