/*
	File: DashletGuideConfig.js
	Author: Hieu Nguyen
	Date: 2022-03-10
	Purpose: handle logic on the UI for Dashlet Guide Config
*/

CustomView_BaseController_Js('Settings_Vtiger_DashletGuideConfig_Js', {}, {
	registerEvents: function() {
		this._super();
		this.registerEventFormInit();
	},
	registerEventFormInit: function() {
		var form = $('form#config');
		var widgetIdInput = form.find('[name="widget_id"]');
		var guideContentInput = form.find('[name="guide_content"]');

		// Load existing guide content of the selected module
		widgetIdInput.on('change', function () {
			if ($(this).val() == '') return;
			app.helper.showProgress();

			var params = {
				module: 'Vtiger',
				parent: 'Settings',
				action: 'DashletGuideConfigAjax',
				mode: 'getConfig',
				widget_id: $(this).val()
			}

			app.request.post({ data: params })
			.then((err, res) => {
				app.helper.hideProgress();
				var guideContent = '';

				if (res && res.guide_content) {
					guideContent = res.guide_content;
				}

				CKEDITOR.instances.guide_content.setData(guideContent);
				guideContentInput.trigger('change');
			});            
		});

		// Init CKEDITOR
		var ckeConfig = {
			height: '300px',
		};

		var ckeInstance = new Vtiger_CkEditor_Js();
		ckeInstance.loadCkEditor(guideContentInput, ckeConfig);

		// Handle submit form
		form.vtValidate({
			submitHandler : function() {
				var guideContentHtml = CKEDITOR.instances.guide_content.getData();
				
				if ($(guideContentHtml).text().trim() == '') {
					app.helper.showErrorNotification({ message: app.vtranslate('JS_DASHLET_GUIDE_CONFIG_GUIDE_CONTENT_EMPTY_VALIDATE_MSG') });
					return;
				}

				app.helper.showProgress();

				var params = {
					module: 'Vtiger',
					parent: 'Settings',
					action: 'DashletGuideConfigAjax',
					mode: 'saveConfig',
					widget_id: widgetIdInput.val(),
					guide_content: guideContentHtml
				}

				app.request.post({ data: params })
				.then((err, res) => {
					app.helper.hideProgress();

					if (err) {
						app.helper.showErrorNotification({ message: app.vtranslate('JS_DASHLET_GUIDE_CONFIG_SAVE_ERROR_MSG') });
						return;
					}

					app.helper.showSuccessNotification({ message: app.vtranslate('JS_DASHLET_GUIDE_CONFIG_SAVE_SUCCESS_MSG') });
					return;
				});
			}
		});
	}
});