CustomView_BaseController_Js(
  "Products_CheckWarranty4_Js",
  {},
  {
    registerEvents: function () {
      this._super();
      this.registerEventFormInit();
    },
    registerEventFormInit: function () {
      jQuery(function ($) {
        $("#btnCheck").click(function () {
          var serial = $('input[name="serial"]').val();
          app.helper.showProgress();
          $("#result").hide();
          var params = {
            module: "Products",
            action: "CheckWarrantyAjax",
            serial: serial,
          };
          app.request.post({ data: params }).then(function (error, data) {
            console.log(data);
            app.helper.hideProgress();
            // Handle errors
            if (error) {
              var errorMsg = app.vtranslate("JS_CHECK_WARRANTY_ERROR_MSG");
              app.helper.showErrorNotification({ message: errorMsg });
              return;
            }

            if (data.matched_product == null) {
              // var noProductMsg = app.vtranslate(
              //   "JS_CHECK_WARRANTY_NO_PRODUCT_MATCH_ERROR_MSG"
              // );
              app.helper.showErrorNotification({
                message: "Không tìm thấy sản phẩm",
              });
              return;
            }

            // Show result
            var productInfo = data.matched_product;
            var warrantyStatusClass =
              productInfo.warranty_status == "valid"
                ? "label-success"
                : "label-danger";
            if (productInfo.warranty_status == "ended") {
              app.helper.showErrorNotification({
                message: "Sản phẩm không còn thời gian bảo hành !",
              });
            }
            $("#productName").text(productInfo.productname);
            $("#serialNo").text(productInfo.serialno);
            $("#warrantyStartDate").text(productInfo.start_date);
            $("#warrantyEndDate").text(productInfo.expiry_date);
            $("#warrantyStatus")
              .text(productInfo.warranty_status_label)
              .removeClass("label-success label-danger")
              .addClass(warrantyStatusClass);

            $("#result").show();
          });

          return false;
        });
        $("#btnDeclare").click(function () {
          var declareProductModal = $("#declareProductModal").clone(true, true);

          var callBackFunction = function (data) {
            data.find("#declareProductModal").removeClass("hide");
            var form = data.find(".declareProductForm");
            var productName = form.find('[name="product_name"]');
            var serialNo = form.find('[name="serial_no"]');
            var warrantyStartDate = form.find('[name="warranty_start_date"]');
            var warrantyEndDate = form.find('[name="warranty_end_date"]');
            var website = form.find('[name="website"]');

            function generateRandomSerial() {
              return Math.floor(1000000000 + Math.random() * 9000000000);
            }

            serialNo.val(generateRandomSerial());
            var controller = Vtiger_Edit_Js.getInstance();
            controller.registerBasicEvents(form);
            vtUtils.applyFieldElementsView(form);
            vtUtils.initDatePickerFields(form);
            // Form validation
            var params = {
              submitHandler: function (form) {
                var form = $(form);
                var params = form.serializeFormData();
                params["module"] = "Products";
                params["action"] = "DeclareAjax";

                app.request.post({ data: params }).then(function (error, data) {
                  app.helper.hideProgress();
                  if (data && !data.success) {
                    if (data.type === "duplicate_serial") {
                      app.helper.showErrorNotification({
                        message:
                          "Số serial đã tồn tại trong hệ thống. Vui lòng thử lại.",
                      });
                    } else {
                      app.helper.showErrorNotification({
                        message:
                          "An unexpected error occurred. Please try again later.",
                      });
                    }
                    return;
                  }
                  app.helper.hideModal();
                  var message = app.vtranslate("Thêm sản phẩm thành công");
                  app.helper.showSuccessNotification({ message: message });
                });
              },
            };
            form.vtValidate(params);
          };

          var modalParams = {
            cb: callBackFunction,
          };
          app.helper.showModal(declareProductModal, modalParams);

          return false;
        });
        $("#btnDelete").click(function () {
          var serialNo = $('input[name="serial"]').val();
          app.helper.showProgress();
          if (serialNo == "") {
            app.helper.showErrorNotification({
              message: "Vui lòng nhập số serial",
            });
            return false;
          } else {
            var params = {
              module: "Products",
              action: "DeleteBySerialAjax",
              serial: serialNo,
            };
            app.request.post({ data: params }).then(function (error, data) {
              console.log(data);
              app.helper.hideProgress();

              if (error) {
                var errorMsg = app.vtranslate("JS_CHECK_WARRANTY_ERROR_MSG");
                app.helper.showErrorNotification({ message: errorMsg });
                return;
              }
              if (data.success) {
                app.helper.showSuccessNotification({
                  message: "Xóa sản phầm thành công !",
                });
                // $('input[name="serial"]').val("");
              } else {
                app.helper.showErrorNotification({
                  message: "Không thể xóa sản phẩm !",
                });
              }
            });
          }
          return false;
        });
        $("#btnUpdate").click(function () {
          var declareProductModal = $("#declareProductModal").clone(true, true);
          var serialNo = $('input[name="serial"]').val();
          var params = {
            module: "Products",
            action: "GetBySerialAjax",
            serial: serialNo,
          };
          app.request.post({ data: params }).then(function (error, data) {
            if (error) {
              console.error("Error:", error); // Hiển thị lỗi nếu có
            } else if (data) {
              // Set giá trị vào các trường tương ứng nếu dữ liệu tồn tại
              if (data.productname) {
                $('input[name="product_name"]').val(data.productname);
              }
              if (data.serialno) {
                $('input[name="serial_no"]').val(data.serialno);
              }
              if (data.start_date) {
                $('input[name="warranty_start_date"]').val(data.start_date);
              }
              if (data.expiry_date) {
                $('input[name="warranty_end_date"]').val(data.expiry_date);
              }
              if (data.website) {
                $('input[name="website"]').val(data.website);
              }

              console.log("Updated fields with data:", data);
            } else {
              console.log("No data found for the given serial number.");
            }

            app.helper.hideProgress();
          });
          var declareProductModal = $("#declareProductModal").clone(true, true);

          var callBackFunction = function (data) {
            data.find("#declareProductModal").removeClass("hide");
            var form = data.find(".declareProductForm");
            var productName = form.find('[name="product_name"]');
            var serialNo = form.find('[name="serial_no"]');
            var warrantyStartDate = form.find('[name="warranty_start_date"]');
            var warrantyEndDate = form.find('[name="warranty_end_date"]');
            var website = form.find('[name="website"]');
            var controller = Vtiger_Edit_Js.getInstance();
            controller.registerBasicEvents(form);
            vtUtils.applyFieldElementsView(form);
            vtUtils.initDatePickerFields(form);
            // Form validation
            var params = {
              submitHandler: function (form) {
                var form = $(form);
                var params = form.serializeFormData();
                params["module"] = "Products";
                params["action"] = "DeclareAjax";

                app.request.post({ data: params }).then(function (error, data) {
                  app.helper.hideProgress();
                  if (data && !data.success) {
                    if (data.type === "duplicate_serial") {
                      app.helper.showErrorNotification({
                        message:
                          "Số serial đã tồn tại trong hệ thống. Vui lòng thử lại.",
                      });
                    } else {
                      app.helper.showErrorNotification({
                        message:
                          "An unexpected error occurred. Please try again later.",
                      });
                    }
                    return;
                  }
                  app.helper.hideModal();
                  var message = app.vtranslate("Thêm sản phẩm thành công");
                  app.helper.showSuccessNotification({ message: message });
                });
              },
            };
            form.vtValidate(params);
          };

          var modalParams = {
            cb: callBackFunction,
          };
          app.helper.showModal(declareProductModal, modalParams);
          return false;
        });
      });
    },
  }
);
