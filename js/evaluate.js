document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const comment = this.querySelector('textarea').value;
    const rating = this.querySelector('input[name="star"]:checked');

    if (!rating) {
        alert("Vui lòng chọn số sao đánh giá!");
        return;
    }

    if (comment.trim() === "") {
        alert("Vui lòng nhập nội dung đánh giá!");
        return;
    }

    alert("Cảm ơn bạn đã gửi đánh giá! Phản hồi của bạn đang được duyệt.");
    this.reset();
});