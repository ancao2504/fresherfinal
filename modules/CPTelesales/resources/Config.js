/*
	File: Config.js
	Author: Vu Mai
	Date: 2022-08-12
	Purpose: Telesales config UI handle 
*/

CustomView_BaseController_Js(
  "CPTelesales_Config_Js",
  {},
  {
    // This will contain the customer field multi select element
    columnFieldList: false,
    getColumnFieldList: function () {
      if (this.columnFieldList == false) {
        this.columnFieldList = jQuery("#column-field-list");
      }

      return this.columnFieldList;
    },
    registerEvents: function () {
      this._super();
      this.registerEventFormInit();
      this.makeColumnListSortable();
    },
    registerEventFormInit: function () {
      alert("Hello");
      let self = this;
      let form = this.getForm();
      let purpose = self.getCampaignPurpose(form);

      // Load customer status by purpose
      self.loadCustomerStatusList(form, purpose);
      form
        .find('select[name="campaign_purpose"]')
        .attr("data-current-value", purpose);

      self.registerCustomerStatusSortableEvent();

      // Added click event for icon active to reload page
      form
        .find("#modules-menu")
        .find("ul li.active")
        .on("click", function () {
          location.reload();
        });

      // Handle change campaign purpose event
      form.find('select[name="campaign_purpose"]').on("change", function () {
        let dropdown = $(this);
        self.showChangePurposeConfirmationBox(dropdown, form);
      });

      // Handle button add customer status
      form.find(".btn-add-status").on("click", function () {
        self.showCustomerStatusModal(this);
      });

      // Handle button next click
      form.find(".next-step").on("click", function () {
        let step = self.getStep(form);
        let purpose = self.getCampaignPurpose(form);

        self.nextStep(self, this, step, form, purpose);
      });

      // Handle button prev click
      form.find(".prev-step").on("click", function () {
        let step = self.getStep(form);
        self.prevStep(this, step, form);
      });

      // Do saving config
      form.vtValidate({
        submitHandler: function () {
          let config = form.serializeObject();

          if (
            form
              .find('select[name="call_screen[customer_list_columns][]"]')
              .val() == null
          ) {
            form.find('.nav-tabs a[href="#general-config"]').click();
            form
              .find('select[name="call_screen[customer_list_columns][]"]')
              .valid();
            return false;
          }

          if (config["customer_status_is_new"] == null) {
            form.find('.nav-tabs a[href="#customer-status-config"]').click();

            app.helper.showErrorNotification({
              message: app.vtranslate(
                "JS_TELESALES_CAMPAIGN_CONFIG_NOT_SET_NEW_STATUS_ERROR_MSG"
              ),
            });
            return false;
          }

          if (config["customer_status_is_success"] == null) {
            form.find('.nav-tabs a[href="#customer-status-config"]').click();

            app.helper.showErrorNotification({
              message: app.vtranslate(
                "JS_TELESALES_CAMPAIGN_CONFIG_NOT_SET_SUCCESS_STATUS_ERROR_MSG"
              ),
            });
            return false;
          }

          if (config["customer_status_is_failed"] == null) {
            form.find('.nav-tabs a[href="#customer-status-config"]').click();

            app.helper.showErrorNotification({
              message: app.vtranslate(
                "JS_TELESALES_CAMPAIGN_CONFIG_NOT_SET_FAILED_STATUS_ERROR_MSG"
              ),
            });
            return false;
          }

          config.call_screen.customer_list_columns = self.getSelectedColumns();

          config.customer_status_is_failed_array = new Array();
          form
            .find(
              '#customer-status-list input[name="customer_status_is_failed"]'
            )
            .each(function () {
              if ($(this).is(":checked")) {
                config.customer_status_is_failed_array.push($(this).val());
              }
            });

          delete config.customer_status_is_failed;

          config.call_result_to_status_mapping = {};
          form
            .find(
              '#call-result-to-status-mapping-list select[name="customer_status"]'
            )
            .each(function () {
              config.call_result_to_status_mapping[
                $(this).attr("data-call-result")
              ] = $(this).val();
            });

          // Check if the purpose has an active campaign. if yes then show popup
          self.checkPurposeHasActiveCampaign(config);
          return;
        },
      });
    },

    getForm: function () {
      return $("form#config");
    },

    getStep: function (form) {
      return form.find(".breadcrumb").attr("data-step");
    },

    makeColumnListSortable: function () {
      var select2Element = jQuery("#s2id_column-field-list");
      var chozenChoiceElement = select2Element.find("ul.select2-choices");

      chozenChoiceElement.sortable({
        containment: chozenChoiceElement,
        start: function () {},
        update: function () {},
      });
    },

    getSelectedColumns: function () {
      var columnListSelectElement = this.getColumnFieldList();
      var select2Element = jQuery("#s2id_column-field-list");
      var selectedValuesByOrder = new Array();
      var selectedOptions = columnListSelectElement.find("option:selected");
      var orderedSelect2Options = select2Element
        .find("li.select2-search-choice")
        .find("div");

      orderedSelect2Options.each(function (index, element) {
        var chosenOption = jQuery(element);
        var choiceElement = chosenOption.closest(".select2-search-choice");
        var choiceValue = choiceElement.data("select2Data").id;

        selectedOptions.each(function (optionIndex, domOption) {
          var option = jQuery(domOption);

          if (option.val() == choiceValue) {
            selectedValuesByOrder.push(option.val());
            return false;
          }
        });
      });

      return selectedValuesByOrder;
    },

    getCampaignPurpose: function (form) {
      return form.find('select[name="campaign_purpose"]').val();
    },

    showChangePurposeConfirmationBox: function (dropdown, form) {
      let self = this;
      let nextCampaignPurposeValue = dropdown.val();
      let currentCampaignPurposeValue = dropdown.attr("data-current-value");
      let replaceParams = {
        old_purpose: app.vtranslate(currentCampaignPurposeValue),
        new_purpose: app.vtranslate(nextCampaignPurposeValue),
      };

      app.helper
        .showConfirmationBox({
          message: app.vtranslate(
            "JS_TELESALES_CAMPAIGN_CONFIG_CHANGE_CAMPAIGN_PURPOSE_CONFIRM_MSG",
            replaceParams
          ),
        })
        .then(
          function (e) {
            form.find("#customer-status-config .step").removeClass("active");
            form.find("#customer-status-config .step1").addClass("active");
            form.find(".breadcrumb").attr("data-step", 1);
            $("#call_result_to_status_mapping").html("");
            dropdown.attr("data-current-value", nextCampaignPurposeValue);

            // Load customer list by next purpose
            self.loadCustomerStatusList(form, nextCampaignPurposeValue);
          },
          function (error, err) {
            dropdown.val(currentCampaignPurposeValue);
            $(".campaign-purpose .select2-chosen").text(
              app.vtranslate(currentCampaignPurposeValue)
            );
          }
        );
    },

    // load customer status table
    loadCustomerStatusList: function (form, purpose) {
      var params = {
        module: "CPTelesales",
        view: "ConfigAjax",
        mode: "getCustomerStatusList",
        purpose: purpose,
      };

      app.helper.showProgress();
      app.request.post({ data: params }).then((err, data) => {
        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return false;
        }

        form.find("#customer-status-list").html("");
        form.find("#customer-status-list").html(data);

        app.helper.hideProgress();
      });
    },

    // Init custom status sorttable and handle save sequence event
    registerCustomerStatusSortableEvent: function () {
      var thisInstance = this;
      var tbody = jQuery("tbody", jQuery("#customer-status-table"));

      tbody.sortable({
        helper: function (e, ui) {
          ui.children().each(function (index, element) {
            element = jQuery(element);
            element.width(element.width());
          });

          return ui;
        },

        containment: tbody,
        revert: true,

        update: function (e, ui) {
          thisInstance.saveSequence();
        },
      });
    },

    saveSequence: function () {
      app.helper.showProgress();
      let self = this;
      let form = this.getForm();
      let purpose = self.getCampaignPurpose(form);
      var customerStatusKeyArray = [];
      var customerStatusKey = jQuery("#customer-status-table tbody").find(
        ".customer-status-item"
      );

      jQuery.each(customerStatusKey, function (i, element) {
        customerStatusKeyArray.push(jQuery(element).attr("data-current-value"));
      });

      let params = {
        module: "CPTelesales",
        action: "SaveConfig",
        mode: "saveCustomerStatusSequence",
        purpose: purpose,
        customerStatusKeyArray: customerStatusKeyArray,
      };

      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err });
          return false;
        }
      });
    },

    showCustomerStatusModal: function (targetBtn) {
      let self = this;
      let form = this.getForm();
      let purpose = self.getCampaignPurpose(form);

      targetBtn = $(targetBtn);
      app.helper.showProgress();

      let params = {
        module: "CPTelesales",
        view: "ConfigAjax",
        mode: "getCustomerStatusModal",
        purpose: purpose,
      };

      // User click edit button
      if (targetBtn.hasClass("btn-edit-status")) {
        params["current_value"] = targetBtn
          .closest(".customer-status-item")
          .attr("data-current-value");
        params["edit"] = true; // To display the right modal title
      }

      // Call ajax to get modal content
      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        // Display modal
        app.helper.showModal(res, {
          preShowCb: function (modal) {
            const modalForm = modal.find("form#customer-status");
            modalForm.find('[name="color"]').customColorPicker();
            self.makeValueNonUnicode(modalForm);

            modalForm.vtValidate({
              submitHandler: function () {
                data = modalForm.serializeFormData();
                let valueInput = modalForm.find('input[name="value"]');
                let currentValue = targetBtn
                  .closest(".customer-status-item")
                  .attr("data-current-value");

                var showValidationParams = {
                  position: {
                    my: "bottom left",
                    at: "top left",
                    container: modalForm,
                  },
                };

                if (targetBtn.hasClass("btn-edit-status")) {
                  if (self.checkExistStatusValue(modalForm, currentValue)) {
                    var errorMessage = app.vtranslate(
                      "JS_DUPLICATE_ENTRIES_FOUND_FOR_THE_VALUE"
                    );
                    vtUtils.showValidationMessage(
                      valueInput,
                      errorMessage,
                      showValidationParams
                    );
                    return false;
                  }

                  rowIndex = targetBtn.closest(".customer-status-item");
                  self.saveCustomerStatus(data, purpose, rowIndex);
                } else {
                  if (self.checkExistStatusValue(modalForm)) {
                    var errorMessage = app.vtranslate(
                      "JS_DUPLICATE_ENTRIES_FOUND_FOR_THE_VALUE"
                    );
                    vtUtils.showValidationMessage(
                      valueInput,
                      errorMessage,
                      showValidationParams
                    );
                    return false;
                  }

                  self.saveCustomerStatus(data, purpose);
                }

                modalForm.find(".cancelLink").trigger("click");
                return false;
              },
            });
          },
        });
      });
    },

    makeValueNonUnicode: function (form) {
      // Make item value non-unicode
      form
        .find('input[name="value"], input[name="label_display_en"]')
        .on("keyup", function (e) {
          var value = jQuery(e.currentTarget).val().trim();

          if (value.isUnicode()) {
            jQuery(this).val(value.unUnicode());
          }
        });
    },

    checkExistStatusValue: function (form, currentValue = null) {
      var value = jQuery('input[name="value"]').val().trim();
      var statusValueList = JSON.parse(
        form.find('input[name="status_array"]').val()
      );

      if (currentValue && value == currentValue) {
        return false;
      }

      var statusValueArr = new Array();

      jQuery.each(statusValueList, function (i, e) {
        statusValueArr.push(jQuery.trim(i));
      });

      if (jQuery.inArray(value, statusValueArr) != -1) {
        return true;
      } else {
        return false;
      }
    },

    saveCustomerStatus: function (data, purpose, rowIndex = null) {
      let form = this.getForm();
      let edit = rowIndex !== null ? true : false;
      app.helper.showProgress();

      let params = {
        module: "CPTelesales",
        action: "SaveConfig",
        mode: "saveCustomerStatus",
        purpose: purpose,
        data: data,
        edit: edit,
      };

      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        if (edit) {
          var contrast = app.helper.getColorContrast(res.color);
          var textColor = contrast === "dark" ? "white" : "black";

          // Update current key, text and style for updated row
          rowIndex.attr("data-current-value", data["value"]);
          rowIndex.find(".picklist-color").text(res.labelDisplay);
          rowIndex.find(".picklist-color").css({
            "background-color": res["color"],
            color: textColor,
          });
          rowIndex.find("input").val(data["value"]);

          // Update option on dropdown in call result to status mapping table if exsist
          let mapping = $("#call_result_to_status_mapping");

          if (mapping.find("table").length) {
            optionUpdate = mapping.find(
              `select[name="customer_status"] option[value="${data["current_value"]}"]`
            );

            optionUpdate.each(function (e) {
              $(this).val(data["value"]);
              $(this).text(res.labelDisplay);
            });
          }
        } else {
          // Insert new row
          let newRow = form
            .find("#customer-status-table tfoot#template")
            .find("tr")
            .clone(true, true);
          var contrast = app.helper.getColorContrast(data["color"]);
          var textColor = contrast === "dark" ? "white" : "black";

          newRow.attr("data-current-value", data["value"]);
          newRow.find(".picklist-color").text(res.labelDisplay);
          newRow.find(".picklist-color").css({
            "background-color": data["color"],
            color: textColor,
          });
          newRow.find("input").val(data["value"]);

          form
            .find("#customer-status-table #customer-status-list")
            .append(newRow);

          // Insert new option to dropdown in call result to status mapping table if exsist
          let mapping = $("#call_result_to_status_mapping");
          let newOption = `<option value="${data["value"]}">${res.labelDisplay}</option>`;

          if (mapping.find("table").length) {
            mapping.find('select[name="customer_status"]').append(newOption);
          }
        }
      });
    },

    showDeleteCutomerStatusModal: function (targetBtn) {
      let self = this;
      let form = this.getForm();
      let purpose = self.getCampaignPurpose(form);

      targetBtn = $(targetBtn);

      let params = {
        module: "CPTelesales",
        view: "ConfigAjax",
        mode: "getCustomerStatusModal",
        purpose: purpose,
        type: "delete",
      };

      params["current_value"] = targetBtn
        .closest(".customer-status-item")
        .attr("data-current-value");

      rowIndex = targetBtn.closest(".customer-status-item");

      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        // Display modal
        app.helper.showModal(res, {
          preShowCb: function (modal) {
            const modalForm = modal.find("form#customer-status");
            let currentValue = targetBtn
              .closest(".customer-status-item")
              .attr("data-current-value");
            modalForm
              .find('select[name="swap_status"]')
              .find(`option[value="${currentValue}"]`)
              .remove();

            modalForm.vtValidate({
              submitHandler: function () {
                data = modalForm.serializeFormData();
                self.deleteCutomerStatus(
                  currentValue,
                  data["swap_status"],
                  rowIndex,
                  purpose
                );
                modalForm.find(".cancelLink").trigger("click");
                return false;
              },
            });
          },
        });
      });
    },

    deleteCutomerStatus: function (
      selectedStatus,
      swapStatus,
      rowIndex,
      purpose
    ) {
      app.helper.showProgress();

      let params = {
        module: "CPTelesales",
        action: "SaveConfig",
        mode: "deleteCustomerStatus",
        selectedStatus: selectedStatus,
        swapStatus: swapStatus,
        purpose: purpose,
      };

      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        rowIndex.remove();

        // remove option on dropdown in call result to status mapping table if exsist
        let mapping = $("#call_result_to_status_mapping");

        if (mapping.find("table").length) {
          select = mapping.find('select[name="customer_status"]');

          select.each(function () {
            if ($(this).val() == selectedStatus) {
              $(this).val(swapStatus).trigger("change");
            }

            $(this).find(`option[value="${selectedStatus}"]`).remove();
          });

          select.select2();
        }
      });
    },

    // Handle button next step click
    nextStep: function (self, targetBtn, step, form, purpose) {
      let customerStatusTable = $("#customer-status-table");
      let mapping = $("#call_result_to_status_mapping");

      if (!customerStatusTable.find("tbody tr").length) {
        app.helper.showErrorNotification({
          message: app.vtranslate(
            "JS_TELESALES_CAMPAIGN_CONFIG_CUSTOMER_STATUS_IS_EMPTY_ERROR_MSG"
          ),
        });
        return false;
      }

      step++;
      form.find(".breadcrumb").attr("data-step", step);
      form.find("#customer-status-config .step").removeClass("active");
      form.find("#customer-status-config .step" + step).addClass("active");

      if (!mapping.find("table").length) {
        self.getCallResultToStatusMappingList(purpose, mapping);
      }
    },

    getCallResultToStatusMappingList: function (purpose, mapping) {
      app.helper.showProgress();

      let params = {
        module: "CPTelesales",
        view: "ConfigAjax",
        mode: "getCallResultToStatusMappingList",
        purpose: purpose,
      };

      app.request.post({ data: params }).then((err, data) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        mapping.html(data);
        mapping.find(".campaign-purpose-in-mapping-table").select2();
      });
    },

    // Handle button prev step click
    prevStep: function (targetBtn, step, form) {
      step--;
      form.find(".breadcrumb").attr("data-step", step);
      form.find("#customer-status-config .step").removeClass("active");
      form.find("#customer-status-config .step" + step).addClass("active");
    },

    // Check if the purpose has an active campaign. if yes then show popup
    checkPurposeHasActiveCampaign: function (config) {
      let self = this;
      let params = {
        module: "CPTelesales",
        action: "SaveConfig",
        mode: "checkPurposeHasActiveCampaign",
        purpose: config.campaign_purpose,
      };

      app.request.post({ data: params }).then((err, res) => {
        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        if (res.result == false) {
          self.saveConfig(config);
          return;
        }

        self.showCustomerStatusUpdateOptionModal(config);
      });
    },

    showCustomerStatusUpdateOptionModal: function (config) {
      let self = this;
      let params = {
        module: "CPTelesales",
        view: "ConfigAjax",
        mode: "getCustomerStatusUpdateOptionModal",
        purpose: config.campaign_purpose,
      };

      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        // Display modal
        app.helper.showModal(res, {
          preShowCb: function (modal) {
            const modalForm = modal.find('form[name="update_option_form"]');

            modalForm.vtValidate({
              submitHandler: function () {
                data = modalForm.serializeFormData();
                self.saveConfig(config, data.update_option);
                modalForm.find(".cancelLink").trigger("click");
                return false;
              },
            });
          },
        });
      });
    },

    // save all config
    saveConfig: function (config, customerStatusUpdateOption = null) {
      app.helper.showProgress();

      let params = {
        module: "CPTelesales",
        action: "SaveConfig",
        mode: "saveConfig",
        config: config,
        customer_status_update_option: customerStatusUpdateOption,
      };

      app.request.post({ data: params }).then((err, res) => {
        app.helper.hideProgress();

        if (err) {
          app.helper.showErrorNotification({ message: err.message });
          return;
        }

        app.helper.showSuccessNotification({
          message: app.vtranslate("JS_SAVE_SETTINGS_SUCCESS_MSG"),
        });
      });
    },
  }
);
