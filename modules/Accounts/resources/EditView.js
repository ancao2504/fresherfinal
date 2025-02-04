/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2018-11-29
    Purpose: to handle logic on the UI
*/

jQuery(function ($) {
  // Init auto complete address
  GoogleMaps.initAutocomplete($(':input[name="bill_street"]'), {
    city: $(':input[name="bill_city"]'),
    state: $(':input[name="bill_state"]'),
    zip: $(':input[name="bill_zip"]'),
    country: $(':input[name="bill_country"]'),
  });

  GoogleMaps.initAutocomplete($(':input[name="ship_street"]'), {
    city: $(':input[name="ship_city"]'),
    state: $(':input[name="ship_state"]'),
    zip: $(':input[name="ship_zip"]'),
    country: $(':input[name="ship_country"]'),
  });

  jQuery('form[name="edit"] input[name="accountname"').attr(
    "data-rule-maxlength",
    150
  );
});
jQuery(function ($) {
  $("form#EditView")
    .find(".saveButton")
    .click(function () {
      var accountType = $('select[name="accounttype"]');
      var employees = $('input[name="employees"]');
      var annualRevenue = $('input[name="annual_revenue"]');
      if (accountType.val().trim() == "Competitor") {
        var employeesValue = employees.val().trim();
        var annualRevenueValue = annualRevenue.val().trim();

        if (
          employeesValue === "" ||
          employeesValue === "0" ||
          annualRevenueValue === ""
        ) {
          if (
            !confirm(
              app.vtranslate(
                "Cả 2 trường Số lượng nhân viên và Doanh thu hàng năm đều là những thông tin quan trong của Công ty Đối thủ. Bạn có chắc chắn muốn bỏ trống 2 trường này ?"
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
