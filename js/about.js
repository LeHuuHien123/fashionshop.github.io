// Hiệu ứng đếm số (giả lập thành tựu)
function animateNumber(id, start, end, duration) {
    let obj = document.getElementById(id);
    let range = end - start;
    let minTimer = 50;
    let step = Math.abs(Math.floor(duration / range));
    step = Math.max(step, minTimer);
    let startTimestamp = null;
    const stepFn = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * range + start);
        if (progress < 1) {
            window.requestAnimationFrame(stepFn);
        }
    };
    window.requestAnimationFrame(stepFn);
}

// Bạn có thể thêm các thẻ <span> với ID và gọi hàm này khi người dùng cuộn tới.