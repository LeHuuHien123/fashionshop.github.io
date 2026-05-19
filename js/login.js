const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

// Hiệu ứng trượt qua lại giữa Đăng nhập và Đăng ký
if (registerBtn && loginBtn) {
    registerBtn.addEventListener('click', () => {
        container.classList.add("active");
        clearAllErrors();
    });

    loginBtn.addEventListener('click', () => {
        container.classList.remove("active");
        clearAllErrors();
    });
}

// Hàm dọn sạch các chữ thông báo lỗi cũ khi người dùng thao tác nhập lại
function clearAllErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.innerText = '';
        el.style.display = 'none';
    });
}

// ==========================================
// AJAX XỬ LÝ ĐĂNG NHẬP
// ==========================================
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); // CHẶN LOAD LẠI TRANG TUYỆT ĐỐI
        clearAllErrors();

        const formData = new FormData(this);

        // Gọi trực tiếp đến file login.php trong thư mục php
        fetch('php/login.php?action=login', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Không thể kết nối đến máy chủ");
            return res.json();
        })
        .then(data => {
            console.log("Dữ liệu đăng nhập nhận được:", data); // Kiểm tra log phản hồi từ PHP
            
            if (data.success === true || data.success) {
                // Đúng thông tin -> chuyển hướng trang mượt mà
                window.location.href = data.redirect;
            } else {
                // Sai thông tin -> tìm đúng ID và hiển thị chữ đỏ lên
                if (data.field === 'email') {
                    const emailError = document.getElementById('login-email-error');
                    if (emailError) {
                        emailError.innerText = data.message;
                        emailError.style.display = 'block';
                    } else {
                        alert(data.message); // Bẫy dự phòng nếu không tìm thấy thẻ ID lỗi
                    }
                } else if (data.field === 'password') {
                    const passError = document.getElementById('login-pass-error');
                    if (passError) {
                        passError.innerText = data.message;
                        passError.style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(err => {
            console.error("Lỗi đăng nhập:", err);
            alert("❌ Hệ thống gặp sự cố phản hồi dữ liệu!");
        });
    });
}

// ==========================================
// AJAX XỬ LÝ ĐĂNG KÝ
// ==========================================
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault(); // CHẶN LOAD LẠI TRANG TUYỆT ĐỐI
        clearAllErrors();

        const formData = new FormData(this);

        fetch('php/login.php?action=register', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Không thể kết nối đến máy chủ");
            return res.json();
        })
        .then(data => {
            console.log("Dữ liệu đăng ký nhận được:", data);
            
            if (data.success === true || data.success) {
                alert("🎉 Đăng ký tài khoản thành công!");
                if (container) container.classList.remove("active"); // Quay về màn hình đăng nhập
                this.reset();
            } else {
                if (data.field === 'email') {
                    const regEmailError = document.getElementById('register-email-error');
                    if (regEmailError) {
                        regEmailError.innerText = data.message;
                        regEmailError.style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(err => {
            console.error("Lỗi đăng ký:", err);
            alert("❌ Hệ thống đăng ký gặp sự cố!");
        });
    });
}