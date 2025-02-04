/*
	GenerateWebhookUrl.js
	Author: Hieu Nguyen
	Date: 2021-03-03
	Purpose: to handle logic on the UI of feature Generate Webhook URL
*/

CustomView_BaseController_Js(
  "Settings_Vtiger_GenerateWebhookUrl_Js",
  {},
  {
    registerEvents: function () {
      this._super();
      this.form = $('form[name="config"]');
      app.helper.showSuccessNotification({
        message: "Xin chào",
      });

      this.registerEventFormInit();
    },
    showResult: function (webhookUrls) {
      let urls = "";

      webhookUrls.forEach((url) => {
        urlParts = url.split("|");
        urls += `<div class="row">
					<div class="col-md-2 fieldLabel">${urlParts[0]}</div>
					<div class="col-md-10 highlight">${urlParts[1]}</div>
				</div>`;
      });

      this.form.find("#result").html(urls);
    },
    clearResult: function () {
      this.form.find("#result").html("");
    },
    registerEventFormInit: function () {
      let thisInstance = this;
      let integrationTypeInput = thisInstance.form.find(
        '[name="integration_type"]'
      );
      let vendorInput = thisInstance.form.find('[name="vendor"]');

      // Load vendors based on selected integration type
      integrationTypeInput.on("change", function () {
        let selectedType = $(this).val();
        let options = "";

        if (selectedType != "") {
          let vendors = _INTEGRATION_MAPPING[selectedType];

          if (vendors.groups) {
            $.each(vendors.groups, (groupKey, groupData) => {
              options +=
                '<optgroup value="' +
                groupKey +
                '" label="' +
                groupData.label +
                '">';

              $.each(groupData.options, (key, value) => {
                options += '<option value="' + key + '">' + value + "</option>";
              });

              options += "</optgroup>";
            });
          } else {
            $.each(vendors, (key, value) => {
              options += '<option value="' + key + '">' + value + "</option>";
            });
          }
        }

        vendorInput.find('option[value!=""]').remove();
        $(vendorInput).append(options);
        vendorInput.select2("destroy").select2();
        thisInstance.clearResult();
      });

      // Clear result when the vendor input is changed
      vendorInput.on("change", function () {
        thisInstance.clearResult();
      });

      // Handle button generate
      thisInstance.form.find("button#generate").on("click", function () {
        let selectedType = integrationTypeInput.val();
        let selectedVendor = vendorInput.val();

        if (!selectedType || !selectedVendor) {
          let message = $(this).data("validateMsg");
          app.helper.showErrorNotification({ message: message });
          return;
        }

        let urlMapping = _WEBHOOK_MAPPING[selectedType];
        let siteUrl = thisInstance.form.find("#site_url").text().trim();
        let secretKey = thisInstance.form.find("#secret_key").text().trim();
        let webhookUrls = [];

        switch (selectedType) {
          case "call_center":
            callEventUrl = `${siteUrl}/webhook.php?name=${selectedVendor}Connector&secret_key=${secretKey}`;
            inboundRougingUrl = callEventUrl + "&action=GetRouting";
            webhookUrls.push(urlMapping["call_event_url"] + "|" + callEventUrl);
            webhookUrls.push(
              urlMapping["inbound_routing_url"] + "|" + inboundRougingUrl
            );
            break;
          case "sms":
            callbackUrl = `${siteUrl}/webhook.php?name=SMSCallback&secret_key=${secretKey}&provider=${selectedVendor}`;
            webhookUrls.push(urlMapping["callback_url"] + "|" + callbackUrl);
            break;
          case "ott":
            let selectedChannel = vendorInput
              .find('option[value="' + selectedVendor + '"]')
              .closest("optgroup")
              .attr("value");
            callbackUrl = `${siteUrl}/webhook.php?name=OTTCallback&secret_key=${secretKey}&channel=${selectedChannel}&provider=${selectedVendor}`;
            webhookUrls.push(urlMapping["callback_url"] + "|" + callbackUrl);
            break;
          case "social":
            webhookUrl = `${siteUrl}/webhook.php?name=${selectedVendor}Connector&secret_key=${secretKey}`;
            oauthCallbackUrl = webhookUrl + "&state=OauthCallback";
            webhookUrls.push(
              urlMapping["oauth_callback_url"] + "|" + oauthCallbackUrl
            );
            webhookUrls.push(urlMapping["webhook_url"] + "|" + webhookUrl);
            break;
          case "chatbot":
            iframeUrl = `${siteUrl}/entrypoint.php?name=${selectedVendor}Iframe&secret_key=${secretKey}`;
            webhookUrl = `${siteUrl}/webhook.php?name=${selectedVendor}Connector&secret_key=${secretKey}`;
            webhookUrls.push(urlMapping["iframe_url"] + "|" + iframeUrl);
            webhookUrls.push(urlMapping["webhook_url"] + "|" + webhookUrl);
            break;
          case "mkt_automation":
            oauthCallbackUrl = `${siteUrl}/index.php?module=Vtiger&parent=Settings&view=ConnectMautic`;
            webhookUrl = `${siteUrl}/webhook.php?name=${selectedVendor}Connector&secret_key=${secretKey}`;
            webhookUrls.push(
              urlMapping["oauth_callback_url"] + "|" + oauthCallbackUrl
            );
            webhookUrls.push(urlMapping["webhook_url"] + "|" + webhookUrl);
            break;
          default:
            webhookUrl = `${siteUrl}/webhook.php?name=${selectedVendor}Connector&secret_key=${secretKey}`;
            webhookUrls.push(urlMapping["webhook_url"] + "|" + webhookUrl);
            break;
        }

        thisInstance.showResult(webhookUrls);
      });
    },
  }
);
