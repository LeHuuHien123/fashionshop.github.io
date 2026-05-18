/**
 * TOÀN BỘ CODE ADMIN.JS - ĐÃ TỐI ƯU VÀ KHÔNG LẶP
 */
function showToast(message) {
    const toast = document.createElement('div');
    toast.style = `
        position: fixed; top: 20px; right: 20px; background: #2ecc71;
        color: white; padding: 15px 25px; border-radius: 8px;
        z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        animation: slideIn 0.5s ease-out;
    `;
    toast.innerText = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = "fadeOut 0.5s ease-in forwards";
        setTimeout(() => toast.remove(), 500);
    }, 2000);
}
// Biến biểu đồ toàn cục để tránh lỗi ghi đè
let myBarChart, myPieChart, myLineChart;

document.addEventListener('DOMContentLoaded', () => {
    // 1. Khởi tạo dữ liệu Dashboard
    loadDashboardData();


    // 3. Xử lý Form Người dùng (Thêm & Sửa)
    // Đảm bảo đoạn này nằm trong document.addEventListener('DOMContentLoaded', ...)
const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'save'); 

            // Trong phần userForm.onsubmit
            fetch('./php/manage_users.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("✅ Cập nhật thông tin khách hàng thành công!");
                    
                    // Cập nhật dòng tương ứng trong bảng User
                    const userRow = document.getElementById(`user-row-${data.id}`);
                    if (userRow) {
                        userRow.cells[1].innerText = data.fullname;
                        userRow.cells[2].innerText = data.email;
                        userRow.cells[3].innerText = data.phone;
                    }
                    closeUserModal();
                }
            });
        };
    }
});

// --- PHẦN 1: THỐNG KÊ & BIỂU ĐỒ (DASHBOARD) ---
if (typeof myBarChart !== 'undefined' && myBarChart) myBarChart.destroy();
if (typeof myPieChart !== 'undefined' && myPieChart) myPieChart.destroy();
if (typeof myLineChart !== 'undefined' && myLineChart) myLineChart.destroy();
async function loadDashboardData(selectedDate = '') {
    try {
        const url = selectedDate ? `php/get_stats.php?date=${selectedDate}` : 'php/get_stats.php';
        const response = await fetch(url);
        const data = await response.json();

        // Cập nhật số liệu Header
        const revEl = document.getElementById('stat-revenue');
        const soldEl = document.getElementById('stat-sold');
        if(revEl) revEl.innerText = new Intl.NumberFormat('vi-VN').format(data.stats.revenue || 0) + 'đ';
        if(soldEl) soldEl.innerText = data.stats.sold || 0;

        renderCharts(data.bar, data.pie, data.line);
    } catch (error) {
        console.error("Lỗi lấy dữ liệu dashboard:", error);
    }
}

function renderCharts(barData, pieData, lineData) {
    const ctxBar = document.getElementById('barChart')?.getContext('2d');
    const ctxPie = document.getElementById('pieChart')?.getContext('2d');
    const ctxLine = document.getElementById('hunoRevenueChart')?.getContext('2d');
    
    if (!ctxBar || !ctxPie) return;

    // Xóa các biểu đồ cũ để tránh lỗi hover đè dữ liệu
    if (myBarChart) myBarChart.destroy();
    if (myPieChart) myPieChart.destroy();
    if (myLineChart) myLineChart.destroy();

    // 1. Biểu đồ cột (ĐÃ XÓA chữ 'let' ở đầu dòng)
    myBarChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: barData.labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: barData.data,
                backgroundColor: '#e67e22',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true
        }
    });

    myPieChart = new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: pieData.labels,
            datasets: [{
                data: pieData.data,
                backgroundColor: ['#e67e22', '#3498db', '#2ecc71', '#f1c40f', '#9b59b6']
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // 3. Biểu đồ đường xu hướng 7 ngày
    if (ctxLine && lineData) {
        const revenueGradient = ctxLine.createLinearGradient(0, 0, 0, 300);
        revenueGradient.addColorStop(0, 'rgba(141, 110, 99, 0.4)');
        revenueGradient.addColorStop(1, 'rgba(141, 110, 99, 0.01)');

        myLineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: lineData.labels,
                datasets: [{
                    label: 'Doanh thu thực tế (đ)',
                    data: lineData.data,
                    borderColor: '#8d6e63',
                    backgroundColor: revenueGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.38,
                    pointBackgroundColor: '#3e2723',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 7,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f5eee9' },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}

function updateCharts() {
    const date = document.getElementById('filterDate').value;
    loadDashboardData(date);
}

// --- PHẦN 2: QUẢN LÝ SẢN PHẨM ---

function openProductModal() {
    const form = document.getElementById('productForm');
    form.reset();
    document.getElementById('prodId').value = "";
    document.getElementById('modalTitle').innerText = "Thêm Sản Phẩm Mới";
    document.getElementById('productModal').style.display = 'block';
}

let currentImageList = []; // Lưu danh sách ảnh hiện tại của sản phẩm

function editProduct(prod) {
    document.getElementById('modalTitle').innerText = "Chỉnh Sửa Sản Phẩm";
    document.getElementById('prodId').value = prod.id;
    document.getElementById('prodName').value = prod.product_name;
    document.getElementById('prodCategory').value = prod.category;
    document.getElementById('prodPrice').value = prod.price;
    document.getElementById('prodSize').value = prod.size;
    document.getElementById('prodDesc').value = prod.description;
    
    // Reset các input liên quan đến ảnh
    document.getElementById('deletedImages').value = "";
    document.getElementById('prodImages').value = "";
    
    // Xử lý hiển thị ảnh cũ
    const container = document.getElementById('imagePreviewContainer');
    container.innerHTML = "";
    
    if (prod.image) {
        currentImageList = prod.image.split(','); // Giả sử ảnh lưu dạng "img1,img2"
        currentImageList.forEach((imgUrl, index) => {
            if(imgUrl.trim() !== "") {
                renderImagePreview(imgUrl, index, true);
            }
        });
    }

    document.getElementById('productModal').style.display = 'block';
}
// Hàm render từng khung ảnh có dấu X
function renderImagePreview(url, index, isOld) {
    const container = document.getElementById('imagePreviewContainer');
    const div = document.createElement('div');
    div.className = 'preview-item';
    div.innerHTML = `
        <img src="${url}">
        <button type="button" class="btn-remove-img" onclick="removeImage(this, '${url}', ${isOld})">×</button>
    `;
    container.appendChild(div);
}

// Hàm xử lý khi nhấn dấu X
function removeImage(btn, url, isOld) {
    if (isOld) {
        // Nếu là ảnh cũ, lưu vào danh sách chờ xóa trên server
        let deletedInput = document.getElementById('deletedImages');
        let deletedArr = deletedInput.value ? deletedInput.value.split(',') : [];
        deletedArr.push(url);
        deletedInput.value = deletedArr.join(',');
    }
    // Xóa khung hiển thị
    btn.parentElement.remove();
}

// Xem trước ảnh mới ngay khi chọn từ máy tính
function previewNewImages(input) {
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                renderImagePreview(e.target.result, null, false);
            }
            reader.readAsDataURL(file);
        });
    }
}
// Xử lý nộp form không reload
const productForm = document.getElementById('productForm');
if (productForm) {
    productForm.onsubmit = function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'save');

        fetch('php/manage_products.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast("✅ Lưu thành công!");
                
                // Cập nhật dòng tương ứng trong bảng
                const row = document.querySelector(`tr[data-id="${data.product.id}"]`);
                if (row) {
                    row.cells[1].innerHTML = `<img src="${data.product.image}" style="width:50px; height:60px; object-fit:cover; border-radius:4px;">`;
                    row.cells[2].innerText = data.product.name;
                    row.cells[3].innerText = data.product.category;
                    row.cells[4].innerText = data.product.price;
                } else {
                    // Nếu thêm mới thì reload để hiện lên đầu
                    location.reload();
                }
                closeProductModal();
            } else {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(err => console.error("Lỗi fetch:", err));
    };
}
// --- SỬA PHẦN SUBMIT ĐỂ CẬP NHẬT KHÔNG RELOAD ---


function deleteProduct(id) {
    if (!confirm("⚠️ Xác nhận xóa sản phẩm này?")) return;
    const fd = new FormData();
    fd.append('id', id);
    fd.append('action', 'delete');

    fetch('php/manage_products.php', { method: 'POST', body: fd })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) row.remove();
            alert("Đã xóa sản phẩm!");
        } else { alert("Lỗi: " + data); }
    });
}

// --- PHẦN 3: QUẢN LÝ NGƯỜI DÙNG ---

function openUserModal() {
    const form = document.getElementById('userForm');
    form.reset();
    document.getElementById('userId').value = "";
    document.getElementById('userPass').required = true; 
    document.getElementById('userPass').placeholder = "Nhập mật khẩu mới";
    document.getElementById('userModal').style.display = 'block';
}

// Hàm mở Modal chỉnh sửa
function editUser(user) {
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.fullname || '';
    document.getElementById('userEmail').value = user.email;
    
    const roleSelect = document.getElementById('userRole');
    if (roleSelect) roleSelect.value = user.role;

    const passInput = document.getElementById('userPass');
    if (passInput) {
        passInput.value = ''; 
        passInput.required = false; 
        passInput.placeholder = "Bỏ trống nếu không đổi";
    }
    document.getElementById('userModal').style.display = 'block';
}

// Hàm xóa người dùng
function deleteUser(id) {
    if (!confirm("⚠️ Bạn có chắc chắn muốn xóa vĩnh viễn?")) return;

    const fd = new FormData();
    fd.append('id', id);
    fd.append('action', 'delete');

    fetch('./php/manager_users.php', { method: 'POST', body: fd })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            const row = document.querySelector(`tr[data-user-id='${id}']`);
            if (row) row.remove();
            alert("✅ Đã xóa!");
        } else {
            alert("❌ Lỗi: " + data);
        }
    })
    .catch(err => alert("❌ Lỗi kết nối server (404 Not Found)"));
}
// --- PHẦN 4: QUẢN LÝ ĐƠN HÀNG ---

function updateGroupStatus(listIds, newStatus, selectElement) {
    if(!confirm('Xác nhận thay đổi trạng thái?')) return;
    fetch('php/update_order_status.php', {  
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ids=${listIds}&status=${newStatus}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Cập nhật thành công!');
            selectElement.style.background = (newStatus === 'delivered') ? '#d4edda' : '#fff';
        } else { alert('Lỗi: ' + data.message); }
    });
}

function deleteOrderGroup(listIds) {
    if (!confirm('⚠️ Xác nhận xóa vĩnh viễn nhóm đơn hàng này?')) return;
    const params = new URLSearchParams();
    params.append('ids', listIds);

    fetch('php/delete_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Đã xóa thành công!');
            location.reload(); 
        } else { alert('Lỗi: ' + data.message); }
    })
    .catch(err => alert('Lỗi kết nối: ' + err.message));
}

// --- ĐÓNG MODALS ---
function closeProductModal() { document.getElementById('productModal').style.display = 'none'; }
function closeUserModal() { document.getElementById('userModal').style.display = 'none'; }
// Modal quản lý mã giảm giá
function openCouponModal() {
    document.getElementById('couponModalTitle').innerText = "Thêm Mã Giảm Giá Mới";
    document.getElementById('couponId').value = "";
    document.getElementById('couponForm').reset();
    
    // Đặt số lượng mặc định khi thêm mới là 100
    document.getElementById('couponLimit').value = "100"; 
    
    document.getElementById('couponModal').style.display = 'block';
}

function closeCouponModal() {
    document.getElementById('couponModal').style.display = "none";
}

// Khi click ra ngoài vùng modal thì tự đóng
window.onclick = function(event) {
    let couponModal = document.getElementById('couponModal');
    let prodModal = document.getElementById('productModal');
    let userModal = document.getElementById('userModal');
    
    if (event.target == couponModal) closeCouponModal();
    if (event.target == prodModal) closeProductModal();
    if (event.target == userModal) closeUserModal();
}
// Hàm mở modal để sửa coupon cũ
function editCoupon(coupon) {
    document.getElementById('couponModalTitle').innerText = "Chỉnh Sửa Mã Giảm Giá";
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('couponCode').value = coupon.code;
    document.getElementById('couponDiscount').value = coupon.discount_value;
    document.getElementById('couponMinOrder').value = coupon.min_order;
    
    // Đổ dữ liệu số lượng giới hạn cũ vào ô input
    document.getElementById('couponLimit').value = coupon.usage_limit ? coupon.usage_limit : "100";
    
    document.getElementById('couponExpiry').value = coupon.expiry_date;
    document.getElementById('couponStatus').value = coupon.status;
    
    document.getElementById('couponModal').style.display = 'block';
}

// Lắng nghe sự kiện gửi dữ liệu từ Form (Thêm hoặc Sửa) bằng AJAX
document.getElementById('couponForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Chặn tải lại trang mặc định
    
    const formData = new FormData(this);
    
    fetch('php/ajax_coupon.php?action=save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload(); // Tải lại trang để cập nhật bảng dữ liệu
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => alert("Lỗi kết nối hệ thống!"));
});

// Hàm Xóa Coupon bằng AJAX
function deleteCoupon(id) {
    if(confirm("Bạn có chắc chắn muốn xóa mã giảm giá này không?")) {
        fetch(`php/ajax_coupon.php?action=delete&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                // Tìm dòng tr chứa data-id để xóa trực tiếp trên giao diện mà không cần reload trang
                document.querySelector(`tr[data-id='${id}']`).remove();
            } else {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(err => alert("Lỗi kết nối hệ thống!"));
    }
}
/**
 * HÀM TÌM KIẾM TỨC THÌ TRÊN BẢNG
 * @param {string} tabId - ID của section (products, users, orders)
 * @param {string} inputId - ID của ô input tìm kiếm
 */
function filterTable(tabId, inputId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.querySelector(`#${tabId} table`);
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        // Bỏ qua các dòng chi tiết đơn hàng (id bắt đầu bằng details-)
        if (tr[i].id && tr[i].id.startsWith('details-')) continue;

        let found = false;
        const tds = tr[i].getElementsByTagName("td");
        
        for (let j = 0; j < tds.length; j++) {
            const txtValue = tds[j].textContent || tds[j].innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        tr[i].style.display = found ? "" : "none";
        
        // Nếu dòng chính bị ẩn, dòng chi tiết tương ứng (nếu đang mở) cũng phải ẩn
        if (!found && tabId === 'orders') {
            const nextRow = tr[i].nextElementSibling;
            if (nextRow && nextRow.id && nextRow.id.startsWith('details-')) {
                nextRow.style.display = "none";
            }
        }
    }
}

/**
 * HÀM LỌC ĐƠN HÀNG THEO NGÀY
 */
function filterOrdersByDate() {
    const filterDate = document.getElementById('filterOrderDate').value; // Định dạng YYYY-MM-DD
    const table = document.querySelector('#orders table');
    const tr = table.getElementsByTagName("tr");

    if (!filterDate) {
        // Nếu không chọn ngày, hiển thị lại tất cả các dòng chính
        for (let i = 1; i < tr.length; i++) {
            if (tr[i].id && tr[i].id.startsWith('details-')) {
                tr[i].style.display = "none";
            } else {
                tr[i].style.display = "";
            }
        }
        return;
    }

    // Chuyển YYYY-MM-DD từ input sang DD/MM/YYYY để khớp với hiển thị trong bảng của bạn
    const [year, month, day] = filterDate.split('-');
    const formattedFilter = `${day}/${month}/${year}`;

    for (let i = 1; i < tr.length; i++) {
        // Luôn ẩn các dòng chi tiết khi đang lọc
        if (tr[i].id && tr[i].id.startsWith('details-')) {
            tr[i].style.display = "none";
            continue;
        }

        const dateCell = tr[i].getElementsByTagName("td")[1]; // Cột "Ngày đặt" là cột thứ 2 (index 1)
        if (dateCell) {
            const dateText = dateCell.textContent || dateCell.innerText;
            // So sánh phần ngày (trước khoảng trắng của giờ)
            if (dateText.includes(formattedFilter)) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}