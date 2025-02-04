// jQuery(function ($) {
//   $("#addStepButton").click(function () {
//     var addStepModal = $("#addStepPipelineModal").clone(true, true);
//     // const modalForm = modal.find("form#addStepPipelineModalForm");

//     // self.makeValueNonUnicode(modalForm);
//     var callBackFunction = function (data) {
//       data.find("#addStepPipelineModal").removeClass("hide");
//       var form = data.find(".addStepPipelineModal");
//       // var productName = form.find('[name="product_name"]');
//       // var serialNo = form.find('[name="serial_no"]');
//       // var warrantyStartDate = form.find('[name="warranty_start_date"]');
//       // var warrantyEndDate = form.find('[name="warranty_end_date"]');
//       // var website = form.find('[name="website"]');
//       form.find('[name="color"]').customColorPicker();
//       var controller = Vtiger_Edit_Js.getInstance();
//       controller.registerBasicEvents(form);
//       vtUtils.applyFieldElementsView(form);
//       vtUtils.initDatePickerFields(form);
//       form.find(".select2").select2();
//       // Bind all drodowns
//       form.find('[name="leadsource"]').select2();
//       // Bind a specific dropdown
//       // Form validation
//       var params = {
//         submitHandler: function (form) {
//           var form = $(form);
//           var params = form.serializeFormData();
//           params["module"] = "Products";
//           params["action"] = "DeclareAjax";
//           app.request.post({ data: params }).then(function (error, data) {
//             app.helper.hideProgress();
//             // if (data && !data.success) {
//             //   if (data.type === "duplicate_serial") {
//             //     app.helper.showErrorNotification({
//             //       message:
//             //         "Số serial đã tồn tại trong hệ thống. Vui lòng thử lại.",
//             //     });
//             //   } else {
//             //     app.helper.showErrorNotification({
//             //       message:
//             //         "An unexpected error occurred. Please try again later.",
//             //     });
//             //   }
//             //   return;
//             // }
//             app.helper.hideModal();
//             // var message = app.vtranslate("Thêm sản phẩm thành công");
//             // app.helper.showSuccessNotification({ message: message });
//           });
//         },
//       };
//       form.vtValidate(params);
//     };

//     var modalParams = {
//       cb: callBackFunction,
//     };
//     app.helper.showModal(addStepModal, modalParams);

//     return false;
//   });
//   $("#addNewStepModal").click(function () {
//     var addStepModal = $("#addStepPipelineNewModal").clone(true, true);
//     // const modalForm = modal.find("form#addStepPipelineModalForm");

//     // self.makeValueNonUnicode(modalForm);
//     var callBackFunction = function (data) {
//       data.find("#addStepPipelineNewModal").removeClass("hide");
//       var form = data.find(".addStepPipelineNewModal");
//       form.find('[name="color"]').customColorPicker();
//       var controller = Vtiger_Edit_Js.getInstance();
//       controller.registerBasicEvents(form);
//       vtUtils.applyFieldElementsView(form);
//       vtUtils.initDatePickerFields(form);
//       // Form validation
//       var params = {
//         submitHandler: function (form) {
//           var form = $(form);
//           var params = form.serializeFormData();
//           params["module"] = "Products";
//           params["action"] = "DeclareAjax";
//           app.request.post({ data: params }).then(function (error, data) {
//             app.helper.hideProgress();
//             // if (data && !data.success) {
//             //   if (data.type === "duplicate_serial") {
//             //     app.helper.showErrorNotification({
//             //       message:
//             //         "Số serial đã tồn tại trong hệ thống. Vui lòng thử lại.",
//             //     });
//             //   } else {
//             //     app.helper.showErrorNotification({
//             //       message:
//             //         "An unexpected error occurred. Please try again later.",
//             //     });
//             //   }
//             //   return;
//             // }
//             app.helper.hideModal();
//             // var message = app.vtranslate("Thêm sản phẩm thành công");
//             // app.helper.showSuccessNotification({ message: message });
//           });
//         },
//       };
//       form.vtValidate(params);
//     };

//     var modalParams = {
//       cb: callBackFunction,
//     };
//     app.helper.showModal(addStepModal, modalParams);

//     return false;
//   });
// });
jQuery(function ($) {
  // Xử lý click addStepButton
  $("#addStepButton").click(function () {
    var addStepModal = $("#addStepPipelineModal").clone(true, true);

    var callBackFunction = function (data) {
      data.find("#addStepPipelineModal").removeClass("hide");
      var form = data.find(".addStepPipelineModal");

      // Khởi tạo color picker
      form.find('[name="color"]').customColorPicker();

      // Khởi tạo các controls cơ bản
      var controller = Vtiger_Edit_Js.getInstance();
      controller.registerBasicEvents(form);
      vtUtils.applyFieldElementsView(form);
      vtUtils.initDatePickerFields(form);

      // Khởi tạo select2
      form.find(".select2").select2();
      form.find('[name="leadsource"]').select2();

      // Form validation
      var params = {
        submitHandler: function (form) {
          var form = $(form);
          var params = form.serializeFormData();
          params["module"] = "Products";
          params["action"] = "DeclareAjax";

          app.request.post({ data: params }).then(function (error, data) {
            app.helper.hideProgress();
            app.helper.hideModal();
          });
        },
      };
      form.vtValidate(params);
    };

    app.helper.showModal(addStepModal, {
      cb: callBackFunction,
    });

    return false;
  });

  // Xử lý click addNewStepModal
  $(document).on("click", "#addNewStepModal", function () {
    // Ẩn modal hiện tại
    app.helper.hideModal();

    // Hiển thị modal mới sau 300ms
    setTimeout(function () {
      var addStepModal = $("#addStepPipelineNewModal").clone(true, true);

      var callBackFunction = function (data) {
        data.find("#addStepPipelineNewModal").removeClass("hide");
        var form = data.find(".addStepPipelineNewModal");

        // Khởi tạo color picker
        form.find('[name="color"]').customColorPicker();

        // Khởi tạo các controls cơ bản
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
              app.helper.hideModal();
            });
          },
        };
        form.vtValidate(params);
      };

      app.helper.showModal(addStepModal, {
        cb: callBackFunction,
      });
    }, 300);

    return false;
  });
});
