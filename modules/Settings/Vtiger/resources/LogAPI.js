/*
	File: Settings_Vtiger_LogAPI_Js.js
	Author: Tung Nguyen
	Date: 2022.06.23
*/

CustomView_BaseController_Js(
  "Settings_Vtiger_LogAPI_Js",
  {},
  {
    registerEvents: function () {
      this._super();
      this.registerEventFormInit();
    },

    registerEventFormInit: function () {
      alert("Hello");
      vtUtils.initDatePickerFields($("#form-log-api"));

      $(".json-display").each(function () {
        try {
          new JsonEditor("#" + $(this).attr("id"), JSON.parse($(this).text()), {
            editable: false,
          });
        } catch (ex) {}
      });

      $(".main-row").click(function () {
        let rowChil = $("table.log-api").find(`[id='${$(this).data("id")}']`);

        if (rowChil.hasClass("collapse")) {
          rowChil.removeClass("collapse");
        } else {
          rowChil.addClass("collapse");
        }
      });

      $("#searchable-field-help-text").customPopover({
        placement: "left",
        size: "sm",
        title: "Searchable Fields",
        trigger: "click",
        container: "body",
      });

      $('[name="logger"]')
        .on("change", function () {
          let logger = $(this).val();
          let searchableFieldHelpText = $(
            "#searchable-field-help-text .custom-popover-content"
          );

          searchableFieldHelpText.find("li").removeClass("hide");
          searchableFieldHelpText
            .find("div.searchable-field-logger")
            .addClass("hide");

          searchableFieldHelpText
            .find(`div[id='${logger}']`)
            .removeClass("hide");
        })
        .trigger("change");

      $('[name="api"]')
        .on("change", function () {
          let API = $(this).val();
          let searchableFieldHelpText = $(
            "#searchable-field-help-text .custom-popover-content"
          );

          if (!API) {
            searchableFieldHelpText.find("li").removeClass("hide");

            return null;
          }

          searchableFieldHelpText.find("li").addClass("hide");
          searchableFieldHelpText.find(`li[id='${API}']`).removeClass("hide");
        })
        .trigger("change");
    },
  }
);
