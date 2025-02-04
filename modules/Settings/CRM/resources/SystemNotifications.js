ustomView_BaseController_Js(
  "Settings_CRM_SystemNotifications_Js",
  {},
  {
    registerEvents: function () {
      this._super();
      this.registerEventFormInit();
    },
    registerEventFormInit: function () {
      let form = $('form[name="settings"]');

      form.on("submit", (e) => {
        e.preventDefault();

        let target = $(e.target);

        let formData = target.serializeFormData();
        171;
        target.find('input[type="checkbox"]').each(function (e) {
          let fieldName = $(this).attr("name");
          formData[fieldName] = $(this).is(":checked") ? 1 : 0;
        });
        let params = {
          module: "CRM",
          parent: "Settings",
          action: "SaveSystemNotificationsConfig",
          config: formData,
        };
        app.helper.showProgress();
        app.request.post({ data: params }).then((err, res) => {
          app.helper.hideProgress();
          if (err) {
            app.helper.showErrorNotification({
              message: app.vtranslate(
                "JS_NOTIFICATIONS_SAVE_ERROR_MSG",
                "CPNotifications"
              ),
            });
            return;
          }
          app.helper.showSuccessNotification({
            message: app.vtranslate(
              "JS_NOTIFICATIONS_SAVE_SUCCESS_MSG",
              "CPNotifications"
            ),
          });
          return;
        });
      });
    },
  }
);
