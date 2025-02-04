// window.onload = function () {
//   // Handle click event for the "Check" button
//   jQuery(function ($) {
//     // Handle click event for the "Check" button
//     $("#btnCheck").click(function () {
//       var serial = $('input[name="serial"]').val();

//       app.helper.showProgress();
//       alert(serial);
//       var params = {
//         module: "Products",
//         view: "CheckWarrantyAjax",
//         serial: serial,
//       };
//       // Submit form via AJAX
//       app.request.post({ data: params }).then(function (error, data) {
//         app.helper.hideProgress();

//         if (error) {
//           var errorMsg = app.vtranslate("JS_CHECK_WARRANTY_ERROR_MSG");
//           app.helper.showErrorNotification({ message: errorMsg });
//           return;
//         }

//         // Show result
//         $("#result").html(data);
//       });

//       return false; // Prevent submit button from reloading the page
//     });
//     // Handle click event for button declare product
//     $("#btnDeclare").click(function () {
//       var declareProductModal = $("#declareProductModal").clone(true, true);

//       var callBackFunction = function (data) {
//         data.find("#declareProductModal").removeClass("hide");
//         var form = data.find(".declareProductForm");
//         var productName = form.find('[name="product_name"]');
//         var serialNo = form.find('[name="serial_no"]');
//         var warrantyStartDate = form.find('[name="warranty_start_date"]');
//         var warrantyEndDate = form.find('[name="warranty_end_date"]');
//         var website = form.find('[name="website"]');
//         // Tạo số serial ngẫu nhiên
//         function generateRandomSerial() {
//           return Math.floor(1000000000 + Math.random() * 9000000000); // 10 chữ số ngẫu nhiên
//         }

//         // Đặt giá trị ngẫu nhiên vào trường serial_no
//         serialNo.val(generateRandomSerial());
//         var controller = Vtiger_Edit_Js.getInstance();
//         controller.registerBasicEvents(form);
//         vtUtils.applyFieldElementsView(form);
//         vtUtils.initDatePickerFields(form);
//         // Form validation
//         var params = {
//           submitHandler: function (form) {
//             var form = $(form);
//             var params = form.serializeFormData();
//             params["module"] = "Products";
//             params["action"] = "DeclareAjax";

//             // Submit form
//             app.request.post({ data: params }).then(function (error, data) {
//               app.helper.hideProgress();
//               if (data && !data.success) {
//                 // Xử lý các lỗi cụ thể
//                 if (data.type === "duplicate_serial") {
//                   app.helper.showErrorNotification({
//                     message:
//                       "Số serial đã tồn tại trong hệ thống. Vui lòng thử lại.",
//                   });
//                 } else {
//                   app.helper.showErrorNotification({
//                     message:
//                       "An unexpected error occurred. Please try again later.",
//                   });
//                 }
//                 return;
//               }
//               app.helper.hideModal();
//               var message = app.vtranslate("Thêm sản phẩm thành công");
//               app.helper.showSuccessNotification({ message: message });
//             });
//           },
//         };
//         form.vtValidate(params);
//       };

//       var modalParams = {
//         cb: callBackFunction,
//       };
//       app.helper.showModal(declareProductModal, modalParams);

//       return false;
//     });
//   });
//   // Rest of your existing code...
// };
