CustomView_BaseController_Js(
  "Settings_Vtiger_ListPipeline_Js",
  {},
  {
    registerEvents: function () {
      this._super();
      this.registerEventFormInit();
    },

    registerEventFormInit: function () {
      let self = this;
      let form = this.getForm();
      self.loadPipeline(form);
      // alert("Please register your event here");
    },
    loadPipeline: function (form) {
      var params = {
        parent: "Settings",
        module: "Vtiger",
        view: "ListPipelineAjax",
      };
      app.helper.showProgress();
      app.request.post({ data: params }).then((err, data) => {
        // console.log(err);
        // console.log("Dữ liệu");
        // console.log(data);
        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return false;
        }
        form.find("#pipeline-list").html("");
        form.find("#pipeline-list").html(data);
        app.helper.hideProgress();
      });
    },
    deletePipeline: function (id) {
      alert("Xóa pipeline có id: " + id);
    },
    getForm: function () {
      return $("form#pipeline");
    },
  }
);
