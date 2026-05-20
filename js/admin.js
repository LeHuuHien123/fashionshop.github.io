/**
 * TOÀN BỘ CODE ADMIN.JS - ĐÃ THAY ALERT/CONFIRM THÀNH MODAL XÁC NHẬN GÓC DƯỚI PHẢI
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

/**
 * HÀM TẠO MODAL XÁC NHẬN GÓC DƯỚI PHẢI MÀN HÌNH (THAY THẾ CONFIRM)
 */
function hunoConfirm(message) {
    return new Promise((resolve) => {
        // Tạo container cho modal nếu chưa có
        let confirmBox = document.createElement('div');
        confirmBox.style = `
            position: fixed; bottom: 20px; right: -350px; width: 320px;
            background: #fff; border-left: 5px solid #ff4757; padding: 15px 20px;
            border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            z-index: 10000; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: Arial, sans-serif;
        `;
        
        confirmBox.innerHTML = `
            <div style="margin-bottom: 12px; font-size: 14px; color: #2c3e50; font-weight: 500; line-height: 1.4;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #ff4757; margin-right: 8px;"></i> ${message}
            </div>
            <div style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                <button id="huno-confirm-cancel" style="background: #f1f2f6; color: #57606f; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">Hủy</button>
                <button id="huno-confirm-ok" style="background: #ff4757; color: white; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">Xóa luôn</button>
            </div>
        `;
        
        document.body.appendChild(confirmBox);
        
        // Hiệu ứng trượt vào góc phải màn hình
        setTimeout(() => { confirmBox.style.right = '20px'; }, 50);
        
        // Hàm đóng box confirm đi kèm hiệu ứng trượt mất
        const closeConfirmBox = () => {
            confirmBox.style.right = '-350px';
            setTimeout(() => { confirmBox.remove(); }, 400);
        };
        
        // Lắng nghe sự kiện click nút bấm
        confirmBox.querySelector('#huno-confirm-ok').addEventListener('click', () => {
            closeConfirmBox();
            resolve(true); // Trả về đồng ý xóa
        });
        
        confirmBox.querySelector('#huno-confirm-cancel').addEventListener('click', () => {
            closeConfirmBox();
            resolve(false); // Trả về hủy bỏ
        });
    });
}

// 1. Hàm tự động gọi AJAX để lấy danh sách Logs mới về đổ vào Table
function refreshSystemLogs() {
    const logsBody = document.getElementById('system-logs-body');
    if (!logsBody) return; 

    fetch('php/fetch_logs.php') 
        .then(response => {
            if (!response.ok) {
                throw new Error('Mạng hoặc đường dẫn file fetch_logs.php có vấn đề');
            }
            return response.text();
        })
        .then(htmlData => {
            logsBody.innerHTML = htmlData; 
        })
        .catch(error => console.error('Lỗi khi cập nhật nhật ký hệ thống:', error));
}

// 2. Thiết lập vòng lặp chạy ngầm mỗi 3 giây (3000ms)
document.addEventListener('DOMContentLoaded', () => {
    refreshSystemLogs(); 
    setInterval(refreshSystemLogs, 3000); 
});

// Biến biểu đồ toàn cục để tránh lỗi ghi đè
let myBarChart, myPieChart, myLineChart;

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();

    // 3. Xử lý Form Người dùng (Thêm & Sửa) Real-time
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.onsubmit = function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'save'); 

            fetch('./php/manager_users.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("✅ Cập nhật thông tin khách hàng thành công!");
                    
                    const userRow = document.getElementById(`user-row-${data.id}`);
                    if (userRow) {
                        userRow.cells[1].innerText = data.fullname;
                        userRow.cells[2].innerText = data.email;
                        if(userRow.cells[3]) userRow.cells[3].innerText = data.phone || '';
                        
                        const editBtn = userRow.querySelector('.btn-edit') || userRow.querySelector('button[onclick^="editUser"]');
                        if (editBtn) {
                            editBtn.setAttribute('onclick', `editUser(${JSON.stringify({id: data.id, fullname: data.fullname, email: data.email, role: data.role || ''})})`);
                        }
                    } else {
                        const userTableBody = document.querySelector('#users table tbody');
                        if (userTableBody) {
                            const newRowHTML = `
                                <tr data-user-id="${data.id}" id="user-row-${data.id}">
                                    <td>#${data.id}</td>
                                    <td class="u-name">${data.fullname}</td>
                                    <td class="u-email">${data.email}</td>
                                    <td><span class="badge badge-user">${(data.role || 'user').toUpperCase()}</span></td>
                                    <td>
                                        <button class="btn-edit" title="Sửa" onclick='editUser(${JSON.stringify({id: data.id, fullname: data.fullname, email: data.email, role: data.role || 'user'})})'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn-danger" style="background: #ff4757; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-left: 5px;" onclick="deleteUser(${data.id})">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            `;
                            userTableBody.insertAdjacentHTML('afterbegin', newRowHTML);
                        }
                    }
                    closeUserModal();
                } else {
                    alert("Lỗi: " + (data.message || "Không thể lưu dữ liệu"));
                }
            })
            .catch(err => console.error("Lỗi fetch user:", err));
        };
    }
});

// --- THỐNG KÊ & BIỂU ĐỒ (DASHBOARD) ---
async function loadDashboardData(selectedDate = '') {
    try {
        const url = selectedDate ? `php/get_stats.php?date=${selectedDate}` : 'php/get_stats.php';
        const response = await fetch(url);
        const data = await response.json();

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

    if (myBarChart) myBarChart.destroy();
    if (myPieChart) myPieChart.destroy();
    if (myLineChart) myLineChart.destroy();

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
        options: { responsive: true }
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

// --- QUẢN LÝ SẢN PHẨM ---

function openProductModal() {
    const form = document.getElementById('productForm');
    form.reset();
    document.getElementById('prodId').value = "";
    document.getElementById('modalTitle').innerText = "Thêm Sản Phẩm Mới";
    document.getElementById('productModal').style.display = 'block';
}

let currentImageList = []; 

function editProduct(prod) {
    document.getElementById('modalTitle').innerText = "Chỉnh Sửa Sản Phẩm";
    document.getElementById('prodId').value = prod.id;
    document.getElementById('prodName').value = prod.product_name || prod.name || '';
    document.getElementById('prodCategory').value = prod.category;
    
    let cleanPrice = (prod.price || '0').toString().replace(/[^\d]/g, '');
    document.getElementById('prodPrice').value = cleanPrice;
    
    document.getElementById('prodSize').value = prod.size || '';
    document.getElementById('prodDesc').value = prod.description || '';
    
    document.getElementById('deletedImages').value = "";
    document.getElementById('prodImages').value = "";
    
    const container = document.getElementById('imagePreviewContainer');
    container.innerHTML = "";
    
    if (prod.image) {
        currentImageList = prod.image.split(','); 
        currentImageList.forEach((imgUrl, index) => {
            if(imgUrl.trim() !== "") {
                let fullSrc = imgUrl;
                if (imgUrl.indexOf('img/') !== 0 && imgUrl.indexOf('http') !== 0) {
                    fullSrc = 'img/' + imgUrl;
                }
                renderImagePreview(fullSrc, index, true);
            }
        });
    }

    document.getElementById('productModal').style.display = 'block';
}

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

function removeImage(btn, url, isOld) {
    if (isOld) {
        let deletedInput = document.getElementById('deletedImages');
        let deletedArr = deletedInput.value ? deletedInput.value.split(',') : [];
        let cleanUrl = url.replace('img/', '');
        deletedArr.push(cleanUrl);
        deletedInput.value = deletedArr.join(',');
    }
    btn.parentElement.remove();
}

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
                showToast("✅ Lưu sản phẩm thành công!");
                let firstImg = data.product.image.split(',')[0];

                const row = document.querySelector(`tr[data-id="${data.product.id}"]`);
                if (row) {
                    row.cells[0].innerHTML = `<img src="${firstImg}" style="width:40px; height:50px; object-fit:cover; border-radius:4px;">`;
                    row.cells[1].innerText = data.product.name;
                    row.cells[2].innerText = data.product.category;
                    row.cells[3].innerText = data.product.size;
                    row.cells[4].innerText = data.product.price;
                    row.cells[5].innerText = data.product.stock;
                    
                    const dbProductObj = {
                        id: data.product.id,
                        product_name: data.product.name,
                        category: data.product.category,
                        size: data.product.size,
                        price: data.product.price,
                        stock: data.product.stock,
                        image: data.product.image,
                        description: document.getElementById('prodDesc').value
                    };
                    const editBtn = row.querySelector('.btn-edit');
                    if (editBtn) {
                        editBtn.setAttribute('onclick', `editProduct(${JSON.stringify(dbProductObj).replace(/"/g, '&quot;')})`);
                    }
                } else {
                    const productTableBody = document.querySelector('#products table tbody');
                    if (productTableBody) {
                        const dbProductObj = {
                            id: data.product.id,
                            product_name: data.product.name,
                            category: data.product.category,
                            size: data.product.size,
                            price: data.product.price,
                            stock: data.product.stock,
                            image: data.product.image,
                            description: document.getElementById('prodDesc').value
                        };
                        const jsonStr = JSON.stringify(dbProductObj).replace(/"/g, '&quot;');

                        const newRowHTML = `
                            <tr data-id="${data.product.id}">
                                <td><img src="${firstImg}" style="width:40px; height:50px; object-fit:cover; border-radius:4px;"></td>
                                <td class="p-name">${data.product.name}</td>
                                <td class="p-cat">${data.product.category}</td>
                                <td class="p-size" style="font-size:11px;">${data.product.size}</td>
                                <td class="p-price">${data.product.price}</td>
                                <td class="p-stock">${data.product.stock}</td>
                                <td>
                                    <button class="btn-edit" onclick="editProduct(${jsonStr})"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn-delete" onclick="deleteProduct(${data.product.id})"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                        productTableBody.insertAdjacentHTML('afterbegin', newRowHTML);
                    }
                }
                closeProductModal();
            } else {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(err => console.error("Lỗi fetch sản phẩm:", err));
    };
}

// XÓA SẢN PHẨM REAL-TIME MODAL MỚI
async function deleteProduct(id) {
    const confirmDelete = await hunoConfirm("⚠️ Xác nhận xóa sản phẩm này? Thao tác không thể hoàn tác.");
    if (!confirmDelete) return;

    const fd = new FormData();
    fd.append('id', id);
    fd.append('action', 'delete');

    fetch('php/manage_products.php', { method: 'POST', body: fd })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) row.remove();
            showToast("🗑️ Đã xóa sản phẩm thành công!");
        } else { alert("Lỗi: " + data); }
    });
}

// --- QUẢN LÝ NGƯỜI DÙNG ---

function openUserModal() {
    const form = document.getElementById('userForm');
    form.reset();
    document.getElementById('userId').value = "";
    document.getElementById('userPass').required = true; 
    document.getElementById('userPass').placeholder = "Nhập mật khẩu mới";
    document.getElementById('userModal').style.display = 'block';
}

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

// XÓA TÀI KHOẢN KHÁCH HÀNG REAL-TIME MODAL MỚ
window.deleteUser = async function(id) {
    const confirmDelete = await hunoConfirm("Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản khách hàng này?");
    if (!confirmDelete) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('php/manager_users.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast("✅ Đã xóa tài khoản thành viên thành công!");
            const userRow = document.getElementById(`user-row-${id}`);
            if (userRow) userRow.remove(); 
            else {
                const fallbackRow = document.querySelector(`tr[data-user-id='${id}']`);
                if(fallbackRow) fallbackRow.remove();
            }
        } else {
            alert("❌ Lỗi: " + (data.message || "Không thể xóa tài khoản này"));
        }
    })
    .catch(err => alert("❌ Lỗi hệ thống không thể kết nối dữ liệu."));
}

// --- QUẢN LÝ ĐƠN HÀNG ---

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

// XÓA ĐƠN HÀNG REAL-TIME MODAL MỚI
window.deleteOrderGroup = async function(listIds) {
    const confirmDelete = await hunoConfirm("⚠️ Xác nhận xóa vĩnh viễn nhóm đơn hàng này?");
    if (!confirmDelete) return;

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
function closeCouponModal() { document.getElementById('couponModal').style.display = "none"; }

window.onclick = function(event) {
    let couponModal = document.getElementById('couponModal');
    let prodModal = document.getElementById('productModal');
    let userModal = document.getElementById('userModal');
    
    if (event.target == couponModal) closeCouponModal();
    if (event.target == prodModal) closeProductModal();
    if (event.target == userModal) closeUserModal();
}

// --- QUẢN LÝ MÃ GIẢM GIÁ (COUPON) REAL-TIME ---

function openCouponModal() {
    document.getElementById('couponModalTitle').innerText = "Thêm Mã Giảm Giá Mới";
    document.getElementById('couponId').value = "";
    document.getElementById('couponForm').reset();
    document.getElementById('couponLimit').value = "100"; 
    document.getElementById('couponModal').style.display = 'block';
}

function editCoupon(coupon) {
    document.getElementById('couponModalTitle').innerText = "Chỉnh Sửa Mã Giảm Giá";
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('couponCode').value = coupon.code;
    document.getElementById('couponDiscount').value = coupon.discount_value;
    document.getElementById('couponMinOrder').value = coupon.min_order;
    document.getElementById('couponLimit').value = coupon.usage_limit ? coupon.usage_limit : "100";
    document.getElementById('couponExpiry').value = coupon.expiry_date;
    document.getElementById('couponStatus').value = coupon.status;
    document.getElementById('couponModal').style.display = 'block';
}

document.getElementById('couponForm').addEventListener('submit', function(e) {
    e.preventDefault(); 
    const formData = new FormData(this);
    formData.append('action', 'save');
    
    fetch('php/ajax_coupon.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast("✅ " + data.message);
            
            const coupon = data.coupon;
            const formattedDiscount = new Intl.NumberFormat('vi-VN').format(coupon.discount_value) + 'đ';
            const formattedMinOrder = new Intl.NumberFormat('vi-VN').format(coupon.min_order) + 'đ';
            
            let displayDate = coupon.expiry_date;
            if (coupon.expiry_date) {
                const parts = coupon.expiry_date.split('-');
                if(parts.length === 3) displayDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            const badgeClass = coupon.status === 'active' ? 'badge-success' : 'badge-danger';
            const badgeText = coupon.status === 'active' ? 'Hoạt động' : 'Tạm dừng';

            const existingRow = document.querySelector(`#coupons table tbody tr[data-id='${coupon.id}']`);
            
            if (existingRow) {
                existingRow.cells[0].innerText = coupon.code;
                existingRow.cells[1].innerText = formattedDiscount;
                existingRow.cells[2].innerText = formattedMinOrder;
                existingRow.cells[3].innerText = coupon.usage_limit;
                existingRow.cells[4].innerText = displayDate;
                existingRow.cells[5].innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
                
                const editBtn = existingRow.querySelector('.btn-edit');
                if (editBtn) {
                    editBtn.setAttribute('onclick', `editCoupon(${JSON.stringify(coupon).replace(/"/g, '&quot;')})`);
                }
            } else {
                const couponTableBody = document.querySelector('#coupons table tbody');
                if (couponTableBody) {
                    const jsonStr = JSON.stringify(coupon).replace(/"/g, '&quot;');
                    const newRowHTML = `
                        <tr data-id="${coupon.id}">
                            <td>${coupon.code}</td>
                            <td>${formattedDiscount}</td>
                            <td>${formattedMinOrder}</td>
                            <td>${coupon.usage_limit}</td>
                            <td>${displayDate}</td>
                            <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                            <td>
                                <button class="btn-edit" onclick="editCoupon(${jsonStr})"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-delete" onclick="deleteCoupon(${coupon.id})"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    couponTableBody.insertAdjacentHTML('afterbegin', newRowHTML);
                }
            }
            closeCouponModal();
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => console.error("Lỗi hệ thống lưu coupon:", err));
});

// XÓA COUPON REAL-TIME MODAL MỚI
async function deleteCoupon(id) {
    const confirmDelete = await hunoConfirm("Bạn có chắc chắn muốn xóa hoàn toàn mã giảm giá này không?");
    if (!confirmDelete) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('php/ajax_coupon.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast("🗑️ Đã xóa mã giảm giá thành công!");
            const targetRow = document.querySelector(`#coupons table tbody tr[data-id='${id}']`);
            if (targetRow) targetRow.remove();
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => console.error("Lỗi hệ thống xóa coupon:", err));
}

/**
 * HÀM TÌM KIẾM TỨC THÌ TRÊN BẢNG
 */
function filterTable(tabId, inputId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.querySelector(`#${tabId} table`);
    if(!table) return;
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
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
    const filterDate = document.getElementById('filterOrderDate').value; 
    const table = document.querySelector('#orders table');
    if(!table) return;
    const tr = table.getElementsByTagName("tr");

    if (!filterDate) {
        for (let i = 1; i < tr.length; i++) {
            if (tr[i].id && tr[i].id.startsWith('details-')) {
                tr[i].style.display = "none";
            } else {
                tr[i].style.display = "";
            }
        }
        return;
    }

    const [year, month, day] = filterDate.split('-');
    const formattedFilter = `${day}/${month}/${year}`;

    for (let i = 1; i < tr.length; i++) {
        if (tr[i].id && tr[i].id.startsWith('details-')) {
            tr[i].style.display = "none";
            continue;
        }

        const dateCell = tr[i].getElementsByTagName("td")[1]; 
        if (dateCell) {
            const dateText = dateCell.textContent || dateCell.innerText;
            if (dateText.includes(formattedFilter)) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}