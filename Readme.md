Dưới đây là file `README.md` được viết mới lại toàn bộ, thiết kế chuẩn chỉ, chuyên nghiệp và đầy đủ cấu trúc dành riêng cho dự án hệ thống quản trị website thời trang **HUNO** của bạn:

---

# 🛍️ HUNO FASHION - Hệ Thống Quản Trị Website Bán Hàng & IoT Smart Parking

HUNO Fashion là một nền tảng Web Full-stack phục vụ việc vận hành kinh doanh quần áo trực tuyến, tích hợp trang Dashboard quản trị thông minh dành cho Admin/Staff và hệ thống bãi đỗ xe thông minh ứng dụng công nghệ IoT.

---

## 📌 Các Tính Năng Cốt Lõi

### 1. 📊 Trang Quản Trị Tổng Quan (Dashboard)

* **Thống kê Real-time:** Theo dõi tổng doanh thu, số lượng đơn hàng, sản phẩm đã bán và số lượng khách hàng.
* **Biểu đồ động:** Trực quan hóa dữ liệu kinh doanh bằng biểu đồ trực quan (Chart.js).

### 2. 🛒 Quản Lý Đơn Hàng (Orders Management)

* **Gom nhóm đơn thông minh:** Tự động gộp các mặt hàng của cùng một khách đặt chung một thời điểm thành một nhóm đơn lớn.
* **Mã hóa đơn tự động:** Sinh mã đơn theo cấu trúc đồng bộ thời gian thực: `DH-ngaythangnam-giophut-ID`.
* **In hóa đơn (Print Invoice):** Tích hợp tính năng xuất hóa đơn mini khổ **K80 (80mm auto)** chuyên nghiệp cho các dòng máy in nhiệt, tự động xóa tiêu đề và URL chân trang của trình duyệt.

### 3. 📦 Quản Lý Sản Phẩm & Khuyến Mãi

* **Kho hàng:** Quản lý danh mục quần áo, phân loại kích cỡ (Size), số lượng tồn kho và cập nhật giá bán.
* **Voucher:** Hệ thống tạo và quản lý mã giảm giá áp dụng trực tiếp vào đơn hàng của khách.

### 4. 🛡️ Nhật Ký Hoạt Động & Phân Quyền Bảo Mật

* **Phân quyền nghiêm ngặt:** * `Admin`: Toàn quyền quản trị hệ thống.
* `Staff`: Chỉ được phép quản lý đơn hàng, sản phẩm, không có quyền truy cập vùng nhạy cảm.


* **Nhật ký hệ thống:** Lưu vết toàn bộ lịch sử thao tác của nhân viên.
* **Bộ lọc nâng cao:** Hỗ trợ lọc nhật ký hoạt động chính xác theo từng ngày và **chỉ duy nhất Admin** mới có quyền hạn xem tab này.

### 5. 🚗 Tích Hợp IoT Smart Parking (Bãi Xe Thông Minh)

* Kết nối phần cứng thông qua vi điều khiển **ESP32** và nền tảng **Adafruit IO MQTT**.
* Giám sát số lượng chỗ trống trong bãi, tự động ghi nhận xe ra/vào hệ thống hỗ trợ khách hàng và nhân viên đến shop.

---

## 💻 Công Nghệ Sử Dụng

* **Back-end:** PHP (XAMPP), kiến trúc kết nối Database thuần qua `mysqli`.
* **Front-end:** HTML5, CSS3 (Responsive Grid/Flexbox), JavaScript (ES6, AJAX kết nối bất đồng bộ).
* **Thư viện bên thứ 3:** Chart.js (Vẽ biểu đồ), FontAwesome 6 (Hệ thống icon).
* **Hardware & IoT:** ESP32, Cảm biến siêu âm/Loadcell, Giao thức MQTT.

---

## 📂 Cấu Trúc Thư Mục Dự Án (Các File Chính)

```text
wbqa/
├── css/
│   ├── admin.css          # Giao diện trang quản trị HUNO
│   └── dungchung.css      # Cấu hình phong cách dùng chung toàn trang
├── js/
│   └── admin.js           # Xử lý các logic AJAX, bộ lọc ngày và các Modal xác nhận
├── php/
│   ├── datk.php           # File cấu hình kết nối Cơ sở dữ liệu MySQL
│   ├── functions.php      # Chứa hàm hệ thống (Ghi log hoạt động, helper...)
│   ├── export_excel.php   # Xuất báo cáo dữ liệu ra file Excel
│   └── print_invoice.php  # Trang xử lý định dạng và in hóa đơn khổ K80
├── img/
│   └── logo/              # Nơi lưu trữ bộ nhận diện thương hiệu HUNO
├── admin.php              # Bảng điều khiển trung tâm (Dashboard chính)
└── index.php              # Giao diện phục vụ khách hàng mua sắm

```

---

## 🚀 Hướng Dẫn Cài Đặt Trên Localhost

### 1. Chuẩn bị môi trường

* Cài đặt phần mềm **XAMPP** (Hỗ trợ PHP 7.4 hoặc PHP 8.x).
* Kích hoạt hai dịch vụ **Apache** và **MySQL** trên XAMPP Control Panel.

### 2. Cài đặt Source Code

1. Tải thư mục dự án về và di chuyển vào đường dẫn: `C:\xampp\htdocs\wbqa\`
2. Truy cập vào đường dẫn `http://localhost/phpmyadmin/`.
3. Tạo một cơ sở dữ liệu mới (Ví dụ tên: `wbqa`).
4. Import file cơ sở dữ liệu `.sql` đi kèm của dự án vào database vừa tạo.

### 3. Cấu hình kết nối

* Mở file `php/datk.php` và chỉnh sửa các thông số kết nối sao cho trùng khớp với tài khoản MySQL local của bạn:

```php
$conn = mysqli_connect("localhost", "root", "", "tên_database_của_bạn");

```

### 4. Khởi chạy dự án

* Mở trình duyệt web bất kỳ và truy cập theo đường dẫn: `http://localhost/wbqa/admin.php` để vào ngay giao diện quản trị hệ thống.

---

## 📝 Lưu Ý Vận Hành Hệ Thống In Hóa Đơn

Để hóa đơn in ra được đẹp mắt, tối ưu chuẩn khổ giấy giống như các biên lai siêu thị:

1. Khi hộp thoại **Print Preview (Xem trước bản in)** của trình duyệt hiện lên.
2. Tìm đến mục **Cài đặt khác (More settings)** ở cột bên phải.
3. Bỏ tích chọn ở ô **Tiêu đề và chân trang (Headers and footers)** để trang giấy sạch 100% đường link URL của trình duyệt.

---

Thực hiện bởi **Lê Hữu Hiền** - Hệ thống vận hành nội bộ thương hiệu thời trang **HUNO**.