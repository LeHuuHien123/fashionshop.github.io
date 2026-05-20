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