CustomView_BaseController_Js(
  "Accounts_ModelEntity_Js",
  {},
  {
    selectedRecordId: null, // Biến lưu ID của record được chọn
    registerEvents: function () {
      this._super();
      this.registerEventFormInit();
    },
    registerEventFormInit: function () {
      // Initialize form
      jQuery(function ($) {
        $(document).on("change", ".recordCheckbox", function () {
          $(".recordCheckbox").not(this).prop("checked", false);
          if ($(this).is(":checked")) {
            selectedRecord = $(this).data("id");
          } else {
            selectedRecord = null;
          }
          console.log(selectedRecord);
        });
        $("#btnDelete").click(function () {
          var accountid = selectedRecord;
          // alert(accountid);
          console.log(accountid);
          app.helper.showProgress();

          var params = {
            module: "Accounts",
            action: "DeleteAjax",
            accountid: accountid,
          };
          // Submit form via AJAX
          app.request.post({ data: params }).then(function (error, data) {
            console.log("Data:");

            app.helper.hideProgress();
            // Handle errors
            if (error) {
              var errorMsg = app.vtranslate("JS_CHECK_WARRANTY_ERROR_MSG");
              app.helper.showErrorNotification({ message: error });
              return;
            }
            console.log(data);
            if (data.check) {
              var success = "Xóa thành công !";
              app.helper.showSuccessNotification({ message: success });
              window.location.reload();
            } else {
              app.helper.showErrorNotification({
                message: "Xóa thất bại !",
              });
            }
          });
          return false;
        });
      });
    },
  }
);
