jQuery(function ($) {
  var serialExists = false;

  $('input[name="serial_no"]').blur(function () {
    var serialNo = $(this).val().trim();

    if (serialNo) {
      var params = {
        module: "Products",
        action: "CheckSerialAjax",
        serial: serialNo,
      };
      app.request.get({ data: params }).then(function (error, data) {
        if (error) {
          return;
        }
        console.log(data);
        if (data.check) {
          alert("Số serial đã trùng !");
          $('input[name="serial_no"]').val("");
          $('input[name="serial_no"]').focus();
          serialExists = true;
          return false;
        } else {
          serialExists = false;
        }
      });
    }
  });
  $("form#EditView")
    .find(".saveButton")
    .click(function () {
      var productNameInput = $('input[name="productname"]');
      var productName = productNameInput.val().trim();
      var serial = $('input[name="serial_no"]').val();
      var nameType = $('select[name="productcategory"]').val();
      // Kiểm tra nếu productName rỗng
      if (!productName) {
        productNameInput.val(nameType + " - " + serial);
        // alert("Tên sản phẩm trống, đã được đặt thành 'ASSSS'.");
      }
    });
});
