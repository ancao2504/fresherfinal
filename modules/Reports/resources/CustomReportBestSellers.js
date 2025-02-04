document.addEventListener("DOMContentLoaded", function () {
  const reportButton = document.getElementById("btnReport");
  const startDateInput = document.getElementById("start_date");
  const endDateInput = document.getElementById("end_date");

  reportButton.addEventListener("click", function (event) {
    event.preventDefault(); // Ngăn form submit ngay lập tức

    const startDate = startDateInput.value.trim();
    const endDate = endDateInput.value.trim();

    // Kiểm tra các trường hợp nhập ngày
    if (!startDate && !endDate) {
      alert(
        "Vui lòng nhập ít nhất 1 trong 2 ngày: Ngày bắt đầu hoặc Ngày kết thúc."
      );
      return;
    }

    if (startDate && endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);

      if (end < start) {
        alert("Ngày kết thúc phải sau Ngày bắt đầu.");
        return;
      }
    }

    // Nếu chỉ nhập Ngày bắt đầu
    if (startDate && !endDate) {
      const start = new Date(startDate);
      const now = new Date();

      if (start > now) {
        alert("Ngày bắt đầu không thể lớn hơn ngày hiện tại.");
        return;
      }
    }

    // Nếu các điều kiện hợp lệ, submit form
    event.target.closest("form").submit();
  });
});
