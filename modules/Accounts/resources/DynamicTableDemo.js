CustomView_BaseController_Js("Accounts_DynamicTableDemo_Js", {
  registerEvents: function () {
    this._super();
    this.registerEventFormInit();
  },
  registerEventFormInit: function () {
    // Init form
    jQuery(function ($) {
      // Init dynamic table
      $("#tblDemo").dynamicTable({
        delAction: "hide",
        preAddCallback: function () {
          // Handle logic before adding
          console.log("Adding new row!");

          if ($("#tblDemo").find("tbody").find("tr:visible").length == 10) {
            alert("Max is 10 rows!");
            return false;
          }
        },
        postAddCallback: function (insertedRow) {
          // Handle logic after adding
          console.log("Inserted row:", insertedRow);
        },
        preDelCallback: function (selectedRow) {
          // Handle logic before deleting
          console.log("Selected row:", selectedRow);
          // Kiểm tra nếu là dòng cuối cùng
          if ($("#tblDemo").find("tbody").find("tr:visible").length == 1) {
            // Clear tất cả input trong dòng
            $(selectedRow).find('input[type="text"]').val("");
            // Không cho phép xóa dòng
            return false;
          }

          if ($("#tblDemo").find("tbody").find("tr:visible").length == 5) {
            alert("At least 5 rows are required!");
            return false;
          }
        },
        postDelCallback: function () {
          // Handle logic after deleting
          console.log("Row deleted!");
        },
      });
      $("#tblDemo .btnAddRow").trigger("click");
    });
  },
});
