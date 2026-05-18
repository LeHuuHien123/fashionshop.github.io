const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

// Hiệu ứng trượt qua lại giữa Đăng nhập và Đăng ký (Giữ lại)
registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

// ĐÃ XÓA ĐOẠN e.preventDefault() ĐỂ FORM CÓ THỂ GỬI DỮ LIỆU SANG PHP