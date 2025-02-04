jQuery(function ($) {
  // Handle custom logic when the save button is clicked
  $("form#QuickCreate")
    .find("button[name='saveButton']")
    .click(function () {
      // !confirm("Hello from QuickCreate.js");
      var accountType = $('select[name="accounttype"]');
      var employees = $('input[name="employees"]');
      var annualRevenue = $('input[name="annual_revenue"]');
      //   alert(accountType.val().trim());
      //   alert(employees.val().trim());
      //   alert(annualRevenue.val().trim());
      if (accountType.val().trim() == "Competitor") {
        var employeesValue = employees.val().trim();
        var annualRevenueValue = annualRevenue.val().trim();

        // Show confirm message when employees or annual revenue is empty
        if (
          employeesValue === "" ||
          employeesValue === "0" ||
          annualRevenueValue === ""
        ) {
          // If user cancel saving to update the empty fields
          // then we will focus on the empty field and postpone the submit event;
          if (
            !confirm(
              app.vtranslate(
                "Cả 2 trường Số lượng nhân viên và Doanh thu hàng năm đều là những thông tin quan trọng của Công ty Đối thủ. Bạn có chăc schawsn muốn bỏ trống 2 trường này ? "
              )
            )
          ) {
            if (employeesValue == "" || employeesValue == 0) {
              employees.focus();
              return false;
            }

            if (annualRevenueValue == "") annualRevenue.focus();
            return false;
          }
        }
      }
    });
});
